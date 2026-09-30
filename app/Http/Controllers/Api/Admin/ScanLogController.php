<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScanLogResource;
use App\Models\ScanLog;
use Illuminate\Http\Request;

class ScanLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ScanLog::with(['participant', 'scannedBy']);

        if ($request->filled('scan_status')) {
            $query->where('scan_status', $request->scan_status);
        }

        if ($request->filled('barcode')) {
            $query->where('barcode', 'like', '%' . $request->barcode . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scanned_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scanned_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('scanned_at', 'desc')->paginate(50);

        return response()->json([
            'message' => 'Daftar scan logs.',
            'data'    => ScanLogResource::collection($logs),
            'meta'    => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'per_page'     => $logs->perPage(),
                'total'        => $logs->total(),
            ],
        ]);
    }

    public function show(string $id)
    {
        $log = ScanLog::with(['participant', 'scannedBy'])->find($id);

        if (!$log) {
            return response()->json([
                'message' => 'Scan log tidak ditemukan.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Detail scan log.',
            'data'    => new ScanLogResource($log),
        ]);
    }
}