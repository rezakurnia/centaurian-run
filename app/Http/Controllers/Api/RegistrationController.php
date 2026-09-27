<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegistrationRequest;
use App\Http\Resources\ParticipantResource;
use App\Http\Resources\RegistrationResource;
use App\Mail\RegistrationConfirmationMail;
use App\Models\Participant;
use App\Models\Registration;
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
        BarcodeService $barcodeService
    ) {
        $validated = $request->validated();

        $this->ensureNotRegisteredToEvent($validated['email'], $validated['event_id']);

        [$participant, $registration] = DB::transaction(function () use ($validated, $numberService, $barcodeService) {
            $generated = $numberService->generate($validated['category_id']);

            // Cari atau buat participant berdasarkan email
            $participant = Participant::firstOrCreate(
                ['email' => $validated['email']],
                [
                    'full_name'   => $validated['full_name'],
                    'gender'      => $validated['gender'],
                    'birth_place' => $validated['birth_place'],
                    'birth_date'  => $validated['birth_date'],
                    'motivation'  => $validated['motivation'] ?? null,
                    'phone'       => $validated['phone'],
                ]
            );

            $registration = Registration::create([
                'registration_number' => $generated['registration_number'],
                'sequence_number'     => $generated['sequence_number'],
                'barcode'             => $barcodeService->generate($generated['registration_number']),
                'participant_id'      => $participant->id,
                'event_id'            => $validated['event_id'],
                'category_id'         => $validated['category_id'],
                'package_id'          => $validated['package_id'],
                'registration_status' => 'pending',
                'payment_status'      => 'unpaid',
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