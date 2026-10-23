<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    /**
     * Ambil semua setting config (definisi).
     */
    public function getConfig(): array
    {
        return config('app_settings', []);
    }

    /**
     * Ambil semua setting dari database, grouped by group.
     * Setiap setting digabung dengan config (label, type, default).
     */
    public function getAllGrouped(): array
    {
        $config = $this->getConfig();
        $values = Setting::pluck('value', 'key')->toArray();

        $grouped = [];

        foreach ($config as $item) {
            $group = $item['group'];
            $value = array_key_exists($item['key'], $values) ? $values[$item['key']] : $item['default'];

            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }

            $grouped[$group][] = [
                'key'     => $item['key'],
                'label'   => $item['label'],
                'type'    => $item['type'],
                'value'   => $value,
                'default' => $item['default'],
            ];
        }

        return $grouped;
    }

    /**
     * Ambil semua setting sebagai flat array (key => value).
     */
    public function getAllFlat(): array
    {
        $config = $this->getConfig();
        $values = Setting::pluck('value', 'key')->toArray();

        $result = [];

        foreach ($config as $item) {
            $result[$item['key']] = array_key_exists($item['key'], $values) ? $values[$item['key']] : $item['default'];
        }

        return $result;
    }

    /**
     * Simpan banyak setting sekaligus.
     */
    public function updateMany(array $data): void
    {
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }

    /**
     * Ambil satu setting.
     */
    public function get(string $key, $default = null)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}