<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use App\Traits\LogsActivity;

class SettingController extends Controller
{
    use LogsActivity;

    public function index()
    {
        $settings = Setting::orderBy('key')->get();

        return response()->json([
            'message' => 'Daftar settings.',
            'data'    => SettingResource::collection($settings),
        ]);
    }

    public function store(StoreSettingRequest $request)
    {
        $setting = Setting::create([
            'key'   => $request->key,
            'value' => $request->value,
        ]);

        $this->logActivity(
            'create_setting',
            'settings',
            $setting->id,
            "Membuat setting: {$setting->key}"
        );

        return response()->json([
            'message' => 'Setting berhasil dibuat.',
            'data'    => new SettingResource($setting),
        ], 201);
    }

    public function show(string $key)
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'message' => 'Setting tidak ditemukan.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Detail setting.',
            'data'    => new SettingResource($setting),
        ]);
    }

    public function update(UpdateSettingRequest $request, string $key)
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'message' => 'Setting tidak ditemukan.',
                'data'    => null,
            ], 404);
        }

        $setting->update([
            'value' => $request->value,
        ]);

        $this->logActivity(
            'update_setting',
            'settings',
            $setting->id,
            "Memperbarui setting: {$setting->key}"
        );

        return response()->json([
            'message' => 'Setting berhasil diperbarui.',
            'data'    => new SettingResource($setting->fresh()),
        ]);
    }

    public function destroy(string $key)
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'message' => 'Setting tidak ditemukan.',
                'data'    => null,
            ], 404);
        }

        $this->logActivity(
            'delete_setting',
            'settings',
            $setting->id,
            "Menghapus setting: {$setting->key}"
        );

        $setting->delete();

        return response()->json([
            'message' => 'Setting berhasil dihapus.',
            'data'    => null,
        ]);
    }
}