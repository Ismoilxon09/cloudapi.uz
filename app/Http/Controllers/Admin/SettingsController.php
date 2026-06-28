<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $updates = $request->except('_token');
        $changedKeys = [];

        foreach ($updates as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();
            if (!$setting) continue;

            if ($setting->value != $value) {
                SystemSetting::set($key, $value, auth()->id());
                $changedKeys[] = $key;
            }
        }

        if (!empty($changedKeys)) {
            AdminLog::record('settings_updated', null,
                "Yangilangan: " . implode(', ', $changedKeys),
                ['keys' => $changedKeys]
            );
        }

        return back()->with('success', "Sozlamalar saqlandi");
    }
}