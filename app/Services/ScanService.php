<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Registration;
use App\Models\Result;
use App\Models\ScanLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScanService
{
    public function startEvent(int $eventId, int $userId, ?string $ip, ?string $agent): array
    {
        return DB::transaction(function () use ($eventId) {
            $event = Event::findOrFail($eventId);

            $event->update(['start_time' => now()]);

            // Update start_time di semua results yang belum punya start_time
            Result::where('event_id', $eventId)
                ->whereNull('start_time')
                ->update(['start_time' => now()]);

            return [
                'event_id' => $event->id,
                'start_time' => $event->start_time,
            ];
        });
    }

    public function scan(string $barcode, int $userId, ?string $ip, ?string $agent): array
    {
        return DB::transaction(function () use ($barcode, $userId, $ip, $agent) {
            $registration = Registration::with(['participant', 'category', 'event'])
                ->where('barcode', $barcode)
                ->first();

            if (! $registration) {
                ScanLog::create([
                    'participant_id' => null,
                    'barcode' => $barcode,
                    'scan_status' => 'invalid',
                    'scanned_by' => $userId,
                    'ip_address' => $ip,
                    'user_agent' => $agent,
                    'scanned_at' => now(),
                ]);

                return [
                    'status' => 'invalid',
                    'message' => 'Barcode tidak ditemukan.',
                    'data' => null,
                ];
            }

            $participant = $registration->participant;
            $event = $registration->event;

            if (! $event || ! $event->start_time) {
                ScanLog::create([
                    'participant_id' => $participant->id,
                    'barcode' => $barcode,
                    'scan_status' => 'invalid',
                    'scanned_by' => $userId,
                    'ip_address' => $ip,
                    'user_agent' => $agent,
                    'scanned_at' => now(),
                ]);

                return [
                    'status' => 'invalid',
                    'message' => 'Acara belum dimulai.',
                    'data' => null,
                ];
            }

            $existing = Result::where('registration_id', $registration->id)->first();

            if ($existing && $existing->finish_time) {
                ScanLog::create([
                    'participant_id' => $participant->id,
                    'barcode' => $barcode,
                    'scan_status' => 'duplicate',
                    'scanned_by' => $userId,
                    'ip_address' => $ip,
                    'user_agent' => $agent,
                    'scanned_at' => now(),
                ]);

                return [
                    'status' => 'duplicate',
                    'message' => 'Peserta sudah pernah discan.',
                    'data' => [
                        'registration_number' => $registration->registration_number,
                        'full_name' => $participant->full_name,
                        'finish_time' => $existing->finish_time,
                        'duration' => $existing->duration,
                    ],
                ];
            }

            $finishTime = now();
            $startTime = Carbon::parse($event->start_time);
            $duration = $finishTime->getTimestamp() - $startTime->getTimestamp();

            if ($existing) {
                $existing->update([
                    'finish_time' => $finishTime,
                    'duration' => $duration,
                    'scan_status' => 'valid',
                    'scanned_by' => $userId,
                ]);
                $result = $existing;
            } else {
                $result = Result::create([
                    'registration_id' => $registration->id,
                    'participant_id' => $participant->id,
                    'event_id' => $event->id,
                    'start_time' => $startTime,
                    'finish_time' => $finishTime,
                    'duration' => $duration,
                    'scan_status' => 'valid',
                    'scanned_by' => $userId,
                ]);
            }

            ScanLog::create([
                'participant_id' => $participant->id,
                'barcode' => $barcode,
                'scan_status' => 'valid',
                'scanned_by' => $userId,
                'ip_address' => $ip,
                'user_agent' => $agent,
                'scanned_at' => now(),
            ]);

            return [
                'status' => 'valid',
                'message' => 'Scan berhasil.',
                'data' => [
                    'registration_number' => $registration->registration_number,
                    'full_name' => $participant->full_name,
                    'category' => $registration->category->name ?? null,
                    'start_time' => $startTime,
                    'finish_time' => $finishTime,
                    'duration' => $duration,
                ],
            ];
        });
    }
}
