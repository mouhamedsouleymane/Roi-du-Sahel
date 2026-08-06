<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolSettingController extends Controller
{
    /**
     * Display school settings page.
     */
    public function index(): View
    {
        $settings = SchoolSetting::all()->groupBy('group');

        return view('settings.index', compact('settings'));
    }

    /**
     * Update school settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            $setting = SchoolSetting::where('key', $key)->first();
            if ($setting) {
                $setting->update(['value' => $value]);
            }
        }

        return redirect()->route('settings.index')->with('status', 'Paramètres de l\'établissement mis à jour avec succès.');
    }
}
