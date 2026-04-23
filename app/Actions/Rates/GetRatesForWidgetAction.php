<?php

namespace App\Actions\Rates;

use App\Models\Currency;
use App\Models\Rate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GetRatesForWidgetAction
{
  
        public function execute(string $date = null): array
    {
        $date = $date ?? Carbon::today()->toDateString();
        $cacheKey = "rates_widget_{$date}";

        return Cache::tags(['rates'])->remember($cacheKey, 60, function () use ($date) {
            // Получаем только валюты для виджета
            $currencyIds = Currency::where('is_widget_visible', true)->pluck('id');
            
            if ($currencyIds->isEmpty()) {
                return [];
            }

            // Один запрос для получения сегодняшних курсов
            $todayRates = Rate::whereIn('currency_id', $currencyIds)
                ->where('date', $date)
                ->get()
                ->keyBy('currency_id');

            // Определяем для каких валют нет курса на сегодня
            $missingIds = $currencyIds->diff($todayRates->keys());

            // Если есть валюты без сегодняшнего курса — получаем последний доступный
            $latestRates = collect();
            if ($missingIds->isNotEmpty()) {
                $latestRates = Rate::whereIn('currency_id', $missingIds)
                    ->whereIn('id', function ($query) use ($missingIds) {
                        $query->select(DB::raw('MAX(id)'))
                            ->from('rates')
                            ->whereIn('currency_id', $missingIds)
                            ->groupBy('currency_id');
                    })
                    ->get()
                    ->keyBy('currency_id');
            }

            // Запрос для получения предыдущих курсов
            $previousRates = Rate::whereIn('currency_id', $currencyIds)
                ->where('date', '<', $date)
                ->whereIn('id', function ($query) use ($currencyIds, $date) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('rates')
                        ->whereIn('currency_id', $currencyIds)
                        ->where('date', '<', $date)
                        ->groupBy('currency_id');
                })
                ->get()
                ->keyBy('currency_id');

            // Формируем результат
            $currencies = Currency::whereIn('id', $currencyIds)
                ->orderBy('char_code')
                ->get();

            return $currencies->map(function ($currency) use ($todayRates, $latestRates, $previousRates) {
                $todayRate = $todayRates->get($currency->id) ?? $latestRates->get($currency->id);
                $previousRate = $previousRates->get($currency->id);

                $currentValue = $todayRate?->value;
                $prevValue = $previousRate?->value;

                $change = null;
                $trend = null;

                if ($currentValue !== null && $prevValue !== null) {
                    $change = round($currentValue - $prevValue, 4);
                    $trend = $change > 0 ? 'up' : ($change < 0 ? 'down' : null);
                }

                return [
                    'char_code' => $currency->char_code,
                    'name' => $currency->name,
                    'value' => $currentValue !== null ? round($currentValue, 4) : null,
                    'previous' => $prevValue !== null ? round($prevValue, 4) : null,
                    'change' => $change,
                    'trend' => $trend,
                ];
            })->toArray();
        });
    }
}