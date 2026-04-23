<?php

namespace App\Actions\Currencies;

use App\Models\Currency;

class UpdateCurrencyFlagsAction
{
    public function execute(array $fetchIds = [], array $widgetIds = []): void
    {
        $allIds = Currency::pluck('id')->toArray();

        foreach ($allIds as $id) {
            Currency::where('id', $id)->update([
                'is_fetch_enabled' => in_array($id, $fetchIds),
                'is_widget_visible' => in_array($id, $widgetIds),
            ]);
        }
    }
}