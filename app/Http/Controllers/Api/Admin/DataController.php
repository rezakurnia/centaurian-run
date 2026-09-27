<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyRegistrationRequest;
use App\Http\Resources\ParticipantResource;
use App\Http\Resources\RegistrationResource;
use App\Models\Category;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataController extends Controller
{
    public function participants(Request $request)
    {
        $query = Participant::with(['registrations.category', 'registrations.package']);

        if ($request->filled('category_id')) {
            $query->whereHas('registrations', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $participants = $query->orderBy('registration_number', 'asc')->get();

        return response()->json([
            'message' => 'Daftar peserta.',
            'data'    => ParticipantResource::collection($participants),
        ]);
    }

    public function registrations(Request $request)
    {
        $query = Registration::with(['participant', 'category', 'package', 'event']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('registration_status')) {
            $query->where('registration_status', $request->registration_status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $registrations = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Daftar pendaftaran.',
            'data'    => RegistrationResource::collection($registrations),
        ]);
    }

    public function showRegistration(string $id)
    {
        $registration = Registration::with(['participant', 'category', 'package', 'event'])
            ->find($id);

        if (!$registration) {
            return response()->json([
                'message' => 'Pendaftaran tidak ditemukan.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Detail pendaftaran.',
            'data'    => new RegistrationResource($registration),
        ]);
    }

    public function verifyRegistration(VerifyRegistrationRequest $request, string $id)
    {
        $registration = Registration::find($id);

        if (!$registration) {
            return response()->json([
                'message' => 'Pendaftaran tidak ditemukan.',
                'data'    => null,
            ], 404);
        }

        $registration->update([
            'registration_status' => $request->registration_status,
            'payment_status'      => $request->payment_status,
            'verified_by'         => $request->user()->id,
            'verified_at'         => now(),
        ]);

        return response()->json([
            'message' => 'Pendaftaran berhasil diverifikasi.',
            'data'    => new RegistrationResource($registration->fresh()),
        ]);
    }

    public function dashboard()
    {
        $totalParticipants = Participant::count();
        $totalRegistrations = Registration::count();

        $byCategory = Category::leftJoin('registrations', 'registrations.category_id', '=', 'categories.id')
            ->leftJoin('participants', 'participants.id', '=', 'registrations.participant_id')
            ->select(
                'categories.id',
                'categories.name',
                'categories.code',
                DB::raw('COUNT(participants.id) AS total')
            )
            ->groupBy('categories.id', 'categories.name', 'categories.code')
            ->orderBy('categories.id')
            ->get();

        $byPayment = Registration::select('payment_status', DB::raw('COUNT(*) AS total'))
            ->groupBy('payment_status')
            ->get();

        $byRegistrationStatus = Registration::select('registration_status', DB::raw('COUNT(*) AS total'))
            ->groupBy('registration_status')
            ->get();

        return response()->json([
            'message' => 'Ringkasan data untuk dashboard admin.',
            'data'    => [
                'total_participants'    => $totalParticipants,
                'total_registrations'   => $totalRegistrations,
                'by_category'           => $byCategory,
                'by_payment_status'     => $byPayment,
                'by_registration_status' => $byRegistrationStatus,
            ],
        ]);
    }
}