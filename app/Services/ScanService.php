<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\Result;
use App\Models\ScanLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScanService
{
    /**
     * Mulai acara (start serentak).
     */
    public function startEvent(int $eventId, int $userId, ?string $ip, ?string $agent): array
    {
        return DB::transaction(function () use ($eventId) {
            $event = Event::findOrFail($eventId);

            if ($event->start_time) {
                throw new \Exception('Acara sudah dimulai sebelumnya.');
            }

            $event->update(['start_time' => now()]);

            // Update start_time di semua results yang belum punya start_time
            Result::where('event_id', $eventId)
                ->whereNull('start_time')
                ->update(['start_time' => now()]);

            return [
                'event_id'   => $event->id,
                'start_time' => $event->start_time,
            ];
        });
    }

    /**
     * Akhiri acara (finish).
     */
    public function finishEvent(int $eventId, int $userId): array
    {
        return DB::transaction(function () use ($eventId) {
            $event = Event::findOrFail($eventId);

            if (!$event->start_time) {
                throw new \Exception('Acara belum dimulai.');
            }

            if ($event->finish_time) {
                throw new \Exception('Acara sudah diakhiri sebelumnya.');
            }

            $event->update(['finish_time' => now()]);

            return [
                'event_id'    => $event->id,
                'finish_time' => $event->finish_time,
            ];
        });
    }

    /**
     * Scan barcode peserta.
     */
    public function scan(string $barcode, int $userId, ?string $ip, ?string $agent): array
    {
        return DB::transaction(function () use ($barcode, $userId, $ip, $agent) {
            $registration = Registration::with(['participant', 'category', 'event'])
                ->where('barcode', $barcode)
                ->first();

            // 1. Barcode tidak ditemukan
            if (!$registration) {
                ScanLog::create([
                    'participant_id' => null,
                    'barcode'        => $barcode,
                    'scan_status'    => 'invalid',
                    'scanned_by'     => $userId,
                    'ip_address'     => $ip,
                    'user_agent'     => $agent,
                    'scanned_at'     => now(),
                ]);

                return [
                    'status'  => 'invalid',
                    'message' => 'Barcode tidak ditemukan.',
                    'data'    => null,
                ];
            }

            $participant = $registration->participant;
            $event       = $registration->event;

            // 1b. Pendaftaran dibatalkan atau belum lunas: barcode tidak sah
            if ($registration->registration_status === 'cancelled'
                || ! in_array($registration->payment_status, ['paid', 'free'], true)) {
                ScanLog::create([
                    'participant_id' => $registration->participant->id,
                    'barcode'        => $barcode,
                    'scan_status'    => 'invalid',
                    'scanned_by'     => $userId,
                    'ip_address'     => $ip,
                    'user_agent'     => $agent,
                    'scanned_at'     => now(),
                ]);

                return [
                    'status'  => 'invalid',
                    'message' => 'Pendaftaran dibatalkan atau belum lunas.',
                    'data'    => null,
                ];
            }

            // 2. Acara belum dimulai
            if (!$event || !$event->start_time) {
                ScanLog::create([
                    'participant_id' => $participant->id,
                    'barcode'        => $barcode,
                    'scan_status'    => 'invalid',
                    'scanned_by'     => $userId,
                    'ip_address'     => $ip,
                    'user_agent'     => $agent,
                    'scanned_at'     => now(),
                ]);

                return [
                    'status'  => 'invalid',
                    'message' => 'Acara belum dimulai.',
                    'data'    => null,
                ];
            }

            // 3. Acara sudah selesai
            if ($event->finish_time) {
                ScanLog::create([
                    'participant_id' => $participant->id,
                    'barcode'        => $barcode,
                    'scan_status'    => 'invalid',
                    'scanned_by'     => $userId,
                    'ip_address'     => $ip,
                    'user_agent'     => $agent,
                    'scanned_at'     => now(),
                ]);

                return [
                    'status'  => 'invalid',
                    'message' => 'Acara sudah selesai. Scan tidak dapat dilakukan.',
                    'data'    => null,
                ];
            }

            // 4. Cek duplikat (sudah pernah discan)
            $existing = Result::where('registration_id', $registration->id)->first();

            if ($existing && $existing->finish_time) {
                ScanLog::create([
                    'participant_id' => $participant->id,
                    'barcode'        => $barcode,
                    'scan_status'    => 'duplicate',
                    'scanned_by'     => $userId,
                    'ip_address'     => $ip,
                    'user_agent'     => $agent,
                    'scanned_at'     => now(),
                ]);

                return [
                    'status'  => 'duplicate',
                    'message' => 'Peserta sudah pernah discan.',
                    'data'    => [
                        'registration_number' => $registration->registration_number,
                        'full_name'           => $participant->full_name,
                        'finish_time'         => $existing->finish_time,
                        'duration'            => $existing->duration,
                    ],
                ];
            }

            // 5. Hitung durasi
            $finishTime = now();
            $startTime  = Carbon::parse($event->start_time);
            $duration   = $finishTime->getTimestamp() - $startTime->getTimestamp();

            // 6. Simpan / update Result
            if ($existing) {
                $existing->update([
                    'finish_time' => $finishTime,
                    'duration'    => $duration,
                    'scan_status' => 'valid',
                    'scanned_by'  => $userId,
                ]);
                $result = $existing;
            } else {
                $result = Result::create([
                    'registration_id' => $registration->id,
                    'participant_id'  => $participant->id,
                    'event_id'        => $event->id,
                    'start_time'      => $startTime,
                    'finish_time'     => $finishTime,
                    'duration'        => $duration,
                    'scan_status'     => 'valid',
                    'scanned_by'      => $userId,
                ]);
            }

            // 7. Catat scan log
            ScanLog::create([
                'participant_id' => $participant->id,
                'barcode'        => $barcode,
                'scan_status'    => 'valid',
                'scanned_by'     => $userId,
                'ip_address'     => $ip,
                'user_agent'     => $agent,
                'scanned_at'     => now(),
            ]);

            // 8. Return sukses
            return [
                'status'  => 'valid',
                'message' => 'Scan berhasil.',
                'data'    => [
                    'registration_number' => $registration->registration_number,
                    'full_name'           => $participant->full_name,
                    'category'            => $registration->category->name ?? null,
                    'start_time'          => $startTime,
                    'finish_time'         => $finishTime,
                    'duration'            => $duration,
                ],
            ];
        });
    }
}