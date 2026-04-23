<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Rate;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DatabaseSeeder extends Seeder
{
    /**
     * Базовые значения курсов для генерации исторических данных
     */
    private array $baseValues = [
        'USD' => 96.50,
        'EUR' => 104.20,
        'CNY' => 13.45,
        'GBP' => 121.80,
        'JPY' => 64.30,
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Начинаем заполнение базы данных...');

        // 1. Создаём валюты
        $this->seedCurrencies();

        // 2. Создаём настройки
        $this->seedSettings();

        // 3. Генерируем исторические курсы
        $this->seedHistoricalRates();

        // 4. Очищаем кэш
        Cache::tags(['rates'])->flush();
        
        $this->command->info('База данных успешно заполнена!');
    }

    /**
     * Создание валют
     */
    private function seedCurrencies(): void
    {
        $currencies = [
            [
                'char_code' => 'USD',
                'name' => 'Доллар США',
                'nominal' => 1,
                'is_fetch_enabled' => true,
                'is_widget_visible' => true,
            ],
            [
                'char_code' => 'EUR',
                'name' => 'Евро',
                'nominal' => 1,
                'is_fetch_enabled' => true,
                'is_widget_visible' => true,
            ],
            [
                'char_code' => 'CNY',
                'name' => 'Китайский юань',
                'nominal' => 1,
                'is_fetch_enabled' => true,
                'is_widget_visible' => false,
            ],
            [
                'char_code' => 'GBP',
                'name' => 'Фунт стерлингов',
                'nominal' => 1,
                'is_fetch_enabled' => true,
                'is_widget_visible' => false,
            ],
            [
                'char_code' => 'JPY',
                'name' => 'Японская иена',
                'nominal' => 100,
                'is_fetch_enabled' => true,
                'is_widget_visible' => false,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['char_code' => $currency['char_code']],
                $currency
            );
        }

        $this->command->info('Валюты созданы: ' . count($currencies) . ' шт.');
    }

    /**
     * Создание настроек
     */
    private function seedSettings(): void
    {
        Setting::set('widget_update_interval', 30); // 30 секунд для тестирования
        
        $this->command->info('Настройки созданы');
    }

    /**
     * Генерация исторических курсов за последние 30 дней
     */
    private function seedHistoricalRates(): void
    {
        $currencies = Currency::where('is_fetch_enabled', true)->get();
        
        if ($currencies->isEmpty()) {
            $this->command->warn('Нет валют для генерации курсов');
            return;
        }

        $days = 30; // Количество дней истории
        $generatedCount = 0;
        
        $this->command->info("Генерация курсов за {$days} дней...");
        
        // Прогресс-бар для красоты
        $bar = $this->command->getOutput()->createProgressBar($days);
        $bar->start();

        // Генерируем от старых дат к новым (чтобы тренд был реалистичным)
        $previousValues = [];
        foreach ($currencies as $currency) {
            $previousValues[$currency->char_code] = $this->baseValues[$currency->char_code] ?? 80;
        }

        for ($i = $days; $i >= 1; $i--) {
            $date = Carbon::today()->subDays($i);
            
            // Пропускаем выходные (ЦБ не работает)
            if ($date->isWeekend()) {
                $bar->advance();
                continue;
            }

            foreach ($currencies as $currency) {
                $prevValue = $previousValues[$currency->char_code];
                
                // Реалистичное изменение: плавный тренд + небольшой шум
                $trend = mt_rand(-100, 100) / 10000; // Базовый тренд
                $noise = mt_rand(-50, 50) / 10000;   // Случайный шум
                $change = round($trend + $noise, 4);
                
                $value = round($prevValue + $change, 4);
                
                // Курс не должен уходить далеко от базового
                $baseValue = $this->baseValues[$currency->char_code] ?? 80;
                if (abs($value - $baseValue) > 2) {
                    $value = $baseValue + (mt_rand(-100, 100) / 100);
                }
                
                Rate::updateOrCreate(
                    [
                        'currency_id' => $currency->id,
                        'date' => $date->toDateString(),
                    ],
                    [
                        'value' => $value,
                    ]
                );
                
                $previousValues[$currency->char_code] = $value;
                $generatedCount++;
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("Сгенерировано записей курсов: {$generatedCount}");
    }
}