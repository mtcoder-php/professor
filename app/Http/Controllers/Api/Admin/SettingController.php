<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Get all settings
     */
    public function index()
    {
        try {
            $settings = Setting::all()->pluck('value', 'key');

            return response()->json([
                'success' => true,
                'data' => $settings
            ]);

        } catch (\Exception $e) {
            \Log::error('Settings index error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'settings' => 'required|array',
                'settings.*.key' => 'required|string',
                'settings.*.value' => 'nullable'
            ]);

            foreach ($request->settings as $setting) {
                Setting::updateOrCreate(
                    ['key' => $setting['key']],
                    ['value' => $setting['value'] ?? '']
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Sozlamalar muvaffaqiyatli saqlandi'
            ]);

        } catch (\Exception $e) {
            \Log::error('Settings update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific setting
     */
    public function show($key)
    {
        try {
            $setting = Setting::where('key', $key)->first();

            return response()->json([
                'success' => true,
                'data' => $setting ? $setting->value : null
            ]);

        } catch (\Exception $e) {
            \Log::error('Setting show error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }
}
