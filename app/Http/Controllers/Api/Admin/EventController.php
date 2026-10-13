<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetEventRequest;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\Result;
use App\Models\ScanLog;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    use LogsActivity;

    public function index()
    {
        $events = Event::orderBy('event_date', 'desc')->get();

        return response()->json([
            'message' => 'Daftar semua event.',
            'data' => EventResource::collection($events),
        ]);
    }

    public function store(StoreEventRequest $request)
    {
        $event = Event::create($request->validated());
        $this->logActivity('create_event', 'events', $event->id, "Membuat event: {$event->name}");

        return response()->json([
            'message' => 'Event berhasil dibuat.',
            'data' => new EventResource($event),
        ], 201);
    }

    public function show(string $id)
    {
        $event = Event::find($id);

        if (! $event) {
            return response()->json([
                'message' => 'Event tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Detail event.',
            'data' => new EventResource($event),
        ]);
    }

    public function update(UpdateEventRequest $request, string $id)
    {
        $event = Event::find($id);

        if (! $event) {
            return response()->json([
                'message' => 'Event tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        $event->update($request->validated());
        $this->logActivity('update_event', 'events', $event->id, "Memperbarui event: {$event->name}");

        return response()->json([
            'message' => 'Event berhasil diperbarui.',
            'data' => new EventResource($event->fresh()),
        ]);
    }

    public function destroy(string $id)
    {
        $event = Event::find($id);

        if (! $event) {
            return response()->json([
                'message' => 'Event tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        // Cegah hapus event yang sudah ada registrasi
        if ($event->registrations()->exists()) {
            return response()->json([
                'message' => 'Event tidak dapat dihapus karena sudah memiliki peserta terdaftar.',
                'data' => null,
            ], 422);
        }

        $this->logActivity('delete_event', 'events', $event->id, "Menghapus event: {$event->name}");
        $event->delete();

        return response()->json([
            'message' => 'Event berhasil dihapus.',
            'data' => null,
        ]);
    }

    public function toggleActive(string $id)
    {
        $event = Event::find($id);

        if (! $event) {
            return response()->json([
                'message' => 'Event tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        $event->update(['is_active' => ! $event->is_active]);
        $this->logActivity('toggle_event', 'events', $event->id, "Toggle event: {$event->name}");

        return response()->json([
            'message' => 'Status event berhasil diubah.',
            'data' => new EventResource($event->fresh()),
        ]);
    }

    public function reset(ResetEventRequest $request, string $id)
    {
        $event = Event::find($id);

        if (! $event) {
            return response()->json([
                'message' => 'Event tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        $result = DB::transaction(function () use ($event) {
            // 1. Hapus scan_logs terkait event ini (via participant yang registrasi di event)
            $registrationIds = $event->registrations()->pluck('id')->toArray();

            $scanLogsDeleted = ScanLog::whereHas('participant', function ($q) use ($event) {
                $q->whereHas('registrations', function ($sub) use ($event) {
                    $sub->where('event_id', $event->id);
                });
            })->delete();

            // 2. Hapus results terkait event
            $resultsDeleted = Result::where('event_id', $event->id)->delete();

            // 3. Reset start_time event
            $event->update(['start_time' => null]);

            return [
                'scan_logs_deleted' => $scanLogsDeleted,
                'results_deleted' => $resultsDeleted,
                'event_id' => $event->id,
            ];
        });

        $this->logActivity(
            'reset_event',
            'events',
            $event->id,
            "Reset acara: {$event->name} (scan_logs: {$result['scan_logs_deleted']}, results: {$result['results_deleted']})"
        );

        return response()->json([
            'message' => 'Acara berhasil direset. Data scan & hasil telah dihapus.',
            'data' => $result,
        ]);
    }
}
