<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Actions\Rates\FetchCbrRatesAction;

class FetchCbrRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-cbr-rates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Загрузить курсы валют с сайта ЦБ';

    /**
     * Execute the console command.
     */
   public function handle(FetchCbrRatesAction $fetchAction): int
    {
        try {
            $result = $fetchAction->execute();

            $this->info(sprintf(
                "Курсы на %s успешно загружены. Обновлено валют: %d",
                $result['date'],
                $result['updated_count']
            ));

            return 0;
        } catch (\Exception $e) {
            $this->error("Ошибка: " . $e->getMessage());
            return 1;
        }
    }
}
