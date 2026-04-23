<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SettingsUpdateRequest;
use App\Services\SettingsService;

class SettingController extends Controller
{
    public function __construct(
        private readonly SettingsService $settingsService
    ) {}

    public function index()
    {
        return response()->json([
            'update_interval' => $this->settingsService->getWidgetInterval(),
        ]);
    }

    public function update(SettingsUpdateRequest $request)
    {
        $validated = $request->validated();

        if (isset($validated['update_interval'])) {
            $this->settingsService->setWidgetInterval($validated['update_interval']);
        }

        return response()->json([
            'message' => 'Настройки обновлены',
            'update_interval' => $this->settingsService->getWidgetInterval(),
        ]);
    }
}