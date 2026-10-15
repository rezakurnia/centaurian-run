<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use App\Services\SettingService;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use LogsActivity;

    protected SettingService $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * Daftar setting grouped by group.
     */
    public function index()
    {
        $grouped = $this->settingService->getAllGrouped();

        return response()->json([
            'message' => 'Daftar settings.',
            'data'    => $grouped,
        ]);
    }

    /**
     * Ambil setting flat (key => value) — publik.
     */
    public function publicIndex()
    {
        $flat = $this->settingService->getAllFlat();

        return response()->json([
            'message' => 'Daftar settings publik.',
            'data'    => $flat,
        ]);
    }

    /**
     * Simpan banyak setting sekaligus (bulk update).
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'settings'   => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        $this->settingService->updateMany($request->settings);

        $this->logActivity(
            'update_settings',
            'settings',
            null,
            'Memperbarui ' . count($request->settings) . ' setting'
        );

        return response()->json([
            'message' => 'Settings berhasil disimpan.',
            'data'    => $this->settingService->getAllGrouped(),
        ]);
    }
}