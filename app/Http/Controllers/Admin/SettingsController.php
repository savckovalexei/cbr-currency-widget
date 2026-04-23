<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Currencies\UpdateCurrencyFlagsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsUpdateRequest;
use App\Models\Currency;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Cache;


class SettingsController extends Controller
{
    public function __construct(
        private readonly SettingsService $settingsService
    ) {}

    public function index()
    {
        $currencies = Currency::orderBy('char_code')->get();
        $updateInterval = $this->settingsService->getWidgetInterval();

        return view('admin.settings', compact('currencies', 'updateInterval'));
    }

    public function update(
        SettingsUpdateRequest $request,
        UpdateCurrencyFlagsAction $updateFlagsAction
    ) {
        $validated = $request->validated();

        // Обновляем интервал через сервис
        $this->settingsService->setWidgetInterval($validated['update_interval']);

        // Обновляем флаги валют через Action
        $updateFlagsAction->execute(
            $validated['fetch_currencies'] ?? [],
            $validated['widget_currencies'] ?? []
        );

        // Сброс кэша уже произошёл в SettingsService
        // Можно повторно сбросить для надёжности
        Cache::tags(['rates'])->flush();

        return redirect()
            ->route('admin.settings')
            ->with('success', 'Настройки сохранены.');
    }
}