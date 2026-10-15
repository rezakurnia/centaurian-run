<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckRegistrationStatusRequest;
use App\Http\Requests\StoreRegistrationRequest;
use App\Http\Requests\UploadPaymentProofRequest;
use App\Http\Resources\ParticipantResource;
use App\Http\Resources\RegistrationResource;
use App\Mail\RegistrationConfirmationMail;
use App\Models\Participant;
use App\Models\Registration;
use App\Services\SettingService;
use App\Services\BarcodeService;
use App\Services\ParticipantNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class RegistrationController extends Controller
{
    public function store(
    StoreRegistrationRequest $request,
    ParticipantNumberService $numberService,
    BarcodeService $barcodeService,
    SettingService $settingService
) {
    // 1. Cek setting: pendaftaran dibuka?
    $registrationOpen = $settingService->get('registration_open', '1');
    if ($registrationOpen !== '1' && $registrationOpen !== true) {
        throw ValidationException::withMessages([
            'registration' => ['Pendaftaran sedang ditutup.'],
        ]);
    }

    // 2. Cek setting: batas akhir
    $deadline = $settingService->get('registration_deadline', '');
    if (!empty($deadline) && now()->toDateString() > $deadline) {
        throw ValidationException::withMessages([
            'registration' => ['Pendaftaran sudah melewati batas waktu.'],
        ]);
    }

    $validated = $request->validated();

    // 3. Ambil kategori & validasi
    $category = \App\Models\Category::find($validated['category_id']);
    if (!$category) {
        throw ValidationException::withMessages([
            'category_id' => ['Kategori tidak ditemukan.'],
        ]);
    }

    // 4. CEK KUOTA ← INI YANG HILANG SEBELUMNYA
    $terdaftar = Registration::where('category_id', $validated['category_id'])
        ->whereIn('registration_status', ['pending', 'confirmed'])
        ->count();

    if ($category->quota && $terdaftar >= $category->quota) {
        throw ValidationException::withMessages([
            'registration' => ['Kuota pendaftaran untuk kategori ini sudah penuh.'],
        ]);
    }

    // 5. Cek duplikat email per event
    $this->ensureNotRegisteredToEvent($validated['email'], $validated['event_id']);

    // 6. Transaksi
    [$participant, $registration] = DB::transaction(function () use ($validated, $numberService, $barcodeService, $category) {
        $generated = $numberService->generate($validated['category_id']);

        $participant = Participant::firstOrCreate(
            ['email' => $validated['email']],
            [
                'full_name'       => $validated['full_name'],
                'gender'          => $validated['gender'],
                'birth_place'     => $validated['birth_place'],
                'birth_date'      => $validated['birth_date'],
                'motivation'      => $validated['motivation'] ?? null,
                'phone'           => $validated['phone'],
                'nip'             => $validated['nip'] ?? null,
                'nis'             => $validated['nis'] ?? null,
                'unit'            => $validated['unit'] ?? null,
                'class'           => $validated['class'] ?? null,
                'position'        => $validated['position'] ?? null,
                'child_name'      => $validated['child_name'] ?? null,
                'child_class'     => $validated['child_class'] ?? null,
                'graduation_year' => $validated['graduation_year'] ?? null,
            ]
        );

        $packageId = match ($category->code) {
            'E' => 1,        // Gratis
            'S' => 2,        // Paket 100K
            'P', 'A' => 3,   // Paket 150K
            default => 1,
        };

        $package = \App\Models\Package::find($packageId);
        $isFree = $package && $package->price == 0;

        $registration = Registration::create([
            'registration_number' => $generated['registration_number'],
            'sequence_number'     => $generated['sequence_number'],
            'barcode'             => $barcodeService->generate($generated['registration_number']),
            'participant_id'      => $participant->id,
            'event_id'            => $validated['event_id'],
            'category_id'         => $validated['category_id'],
            'package_id'          => $packageId,
            'registration_status' => $isFree ? 'confirmed' : 'pending',
            'payment_status'      => $isFree ? 'free' : 'unpaid',
        ]);

        return [$participant, $registration];
    });

    Mail::to($participant->email)->send(new RegistrationConfirmationMail($registration));
    $participant->update(['email_sent_at' => now()]);

    return response()->json([
        'message' => 'Pendaftaran berhasil. Nomor peserta & barcode telah dikirim ke email Anda.',
        'data'    => [
            'participant'  => new ParticipantResource($participant),
            'registration' => new RegistrationResource($registration),
        ],
    ], 201);
}

    public function uploadPaymentProof(
        UploadPaymentProofRequest $request,
        string $registrationNumber
    ) {
        $registration = Registration::where('registration_number', $registrationNumber)->first();

        if (!$registration) {
            return response()->json([
                'message' => 'Pendaftaran tidak ditemukan.',
                'data'    => null,
            ], 404);
        }

        if ($registration->payment_status === 'free') {
            return response()->json([
                'message' => 'Paket gratis tidak memerlukan bukti pembayaran.',
                'data'    => null,
            ], 422);
        }

        if ($registration->payment_status === 'paid') {
            return response()->json([
                'message' => 'Pembayaran sudah terverifikasi.',
                'data'    => null,
            ], 422);
        }

        $path = $request->file('payment_proof')->store('payment_proofs', 'public');

        $registration->update([
            'payment_proof' => $path,
        ]);

        return response()->json([
            'message' => 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.',
            'data'    => new RegistrationResource($registration->fresh()),
        ]);
    }

    public function checkStatus(CheckRegistrationStatusRequest $request)
    {
        $query = Registration::with(['participant', 'category', 'package', 'event']);

        if ($request->filled('registration_number')) {
            $query->where('registration_number', $request->registration_number);
        } else {
            $query->whereHas('participant', function ($q) use ($request) {
                $q->where('email', $request->email);
            });
        }

        $registrations = $query->orderBy('created_at', 'desc')->get();

        if ($registrations->isEmpty()) {
            return response()->json([
                'message' => 'Pendaftaran tidak ditemukan.',
                'data'    => [],
            ], 404);
        }

        return response()->json([
            'message' => 'Status pendaftaran ditemukan.',
            'data'    => RegistrationResource::collection($registrations),
        ]);
    }

    private function ensureNotRegisteredToEvent(string $email, int $eventId): void
    {
        $participant = Participant::where('email', $email)->first();

        if (!$participant) {
            return;
        }

        $exists = Registration::where('participant_id', $participant->id)
            ->where('event_id', $eventId)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'email' => ['Email ini sudah terdaftar pada event tersebut.'],
            ]);
        }
    }
}