<?php

namespace App\Http\Controllers\Api\Panitia;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScanBarcodeRequest;
use App\Models\Event;
use App\Services\ScanService;
use Illuminate\Http\Request;
use App\Traits\LogsActivity;

class ScanController extends Controller
{
    use LogsActivity;
    public function start(Request $request, ScanService $scanService)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
        ]);

        $result = $scanService->startEvent(
            $request->event_id,
            $request->user()->id,
            $request->ip(),
            $request->userAgent()
        );

        $this->logActivity(
    'start_event',
    'events',
    $request->event_id,
    "Memulai acara event ID: {$request->event_id}"
);

        return response()->json([
            'message' => 'Acara telah dimulai. Waktu start tercatat.',
            'data'    => $result,
        ]);
    }

    public function scan(ScanBarcodeRequest $request, ScanService $scanService)
    {
        $result = $scanService->scan(
            $request->barcode,
            $request->user()->id,
            $request->ip(),
            $request->userAgent()
        );

        $httpCode = match ($result['status']) {
            'valid'     => 200,
            'duplicate' => 409,
            'invalid'   => 404,
            default     => 400,
        };

        return response()->json([
            'message' => $result['message'],
            'data'    => $result['data'],
        ], $httpCode);
    }
}