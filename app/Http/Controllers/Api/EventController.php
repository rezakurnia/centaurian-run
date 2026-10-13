<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;

class EventController extends Controller
{
    public function active()
    {
        $event = Event::where('is_active', true)->first();

        if (! $event) {
            return response()->json([
                'message' => 'Belum ada acara aktif.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Informasi acara aktif.',
            'data' => new EventResource($event),
        ]);
    }
}
