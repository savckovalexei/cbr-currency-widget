<?php

namespace App\Actions\Rates;

use App\Models\Currency;
use App\Models\Rate;
use App\Services\CbrService;
use Illuminate\Support\Facades\Cache;

class FetchCbrRatesAction
{
    public function __construct(
        private readonly CbrService $cbrService
    ) {}

    public function execute(): array
    {
        $data = $this->cbrService->fetchRates();
        $date = $data['date'];

        $currencies = Currency::where('is_fetch_enabled', true)
            ->get()
            ->keyBy('char_code');

        $updatedCount = 0;

        foreach ($data['rates'] as $rateData) {
            $charCode = $rateData['char_code'];

            if (!isset($currencies[$charCode])) {
                continue;
            }

            $currency = $currencies[$charCode];

            Rate::updateOrCreate(
                ['currency_id' => $currency->id, 'date' => $date],
                ['value' => $rateData['value'] / $rateData['nominal']]
            );

            $currency->update([
                'name' => $rateData['name'],
                'nominal' => $rateData['nominal'],
            ]);

            $updatedCount++;
        }

        Cache::tags(['rates'])->flush();

        return [
            'date' => $date,
            'updated_count' => $updatedCount,
        ];
    }
}