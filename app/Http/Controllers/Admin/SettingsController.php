<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Show the site settings form.
     */
    public function edit(): Response
    {
        return Inertia::render('admin/settings', [
            'settings' => Setting::values(),
            'imageUrls' => collect(Setting::IMAGE_KEYS)
                ->mapWithKeys(fn (string $key): array => [$key => Setting::assetUrl(Setting::values()[$key])])
                ->all(),
        ]);
    }

    /**
     * Update the site settings.
     */
    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $values = $request->textSettings();
        $currentValues = Setting::values();

        foreach (Setting::IMAGE_KEYS as $imageKey) {
            if ($request->hasFile($imageKey)) {
                Setting::deleteUploadedAsset($currentValues[$imageKey]);
                $values[$imageKey] = Setting::storeUploadedAsset($request->file($imageKey), 'site', $imageKey);
            }
        }

        Setting::store($values);

        Inertia::flash('success', 'Site settings saved.');

        return to_route('admin.settings.edit');
    }
}
