<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class CbrService
{
    const CBR_URL = 'http://www.cbr.ru/scripts/XML_daily.asp';

    public function fetchRates(): array
    {
        $response = Http::get(self::CBR_URL);
        if (!$response->successful()) {
            throw new \Exception('Не удалось получить данные от ЦБ');
        }

        $xml = new SimpleXMLElement($response->body());
        $rates = [];
        $date = (string) $xml['Date']; // формат: DD.MM.YYYY

        foreach ($xml->Valute as $valute) {
            $rates[] = [
                'char_code' => (string) $valute->CharCode,
                'name'      => (string) $valute->Name,
                'nominal'   => (int) $valute->Nominal,
                'value'     => (float) str_replace(',', '.', $valute->Value),
            ];
        }

        return [
            'date'  => \DateTime::createFromFormat('d.m.Y', $date)->format('Y-m-d'),
            'rates' => $rates,
        ];
    }
}