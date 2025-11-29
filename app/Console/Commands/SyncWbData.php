<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WbApiService;
use App\Repositories\SalesRepository;
use App\Repositories\OrdersRepository;
use App\Repositories\StocksRepository;
use App\Repositories\IncomesRepository;
use App\Traits\ResolvesDates;

class SyncWbData extends Command
{
    use ResolvesDates;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wb:fetch {table?} {--from=} {--to=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected array $tables = [
        'sales'   => SalesRepository::class,
        'orders'  => OrdersRepository::class,
        'stocks'  => StocksRepository::class,
        'incomes' => IncomesRepository::class,
    ];

    public function handle()
    {
        $tableArg = $this->argument('table');
        [$dateFrom, $dateTo] = $this->resolveDates($tableArg, $this);

        if ($tableArg) {
            if (!isset($this->tables[$tableArg])) {
                $this->error("Таблица '$tableArg' не найдена");
                return;
            }
            $this->tables = [$tableArg => $this->tables[$tableArg]];
        }

        $service = new WbApiService();

        foreach ($this->tables as $name => $repoClass) {
            $this->info("=== Загрузка таблицы: $name ===");
            $repo = new $repoClass();

            // Получаем генератор по таблице
            switch ($name) {
                case 'sales':
                    $generator = $service->getSales($dateFrom, $dateTo);
                    break;
                case 'orders':
                    $generator = $service->getOrders($dateFrom, $dateTo);
                    break;
                case 'stocks':
                    $generator = $service->getStocks($dateFrom, $dateTo);
                    break;
                case 'incomes':
                    $generator = $service->getIncomes($dateFrom, $dateTo);
                    break;
                default:
                    $this->error("Неизвестная таблица: $name");
                    continue 2;
            }

            // Сохраняем данные по страницам
            foreach ($generator as $pageData) {
                $repo->save($pageData);
            }

            $this->info("=== Таблица $name загружена ===");
        }

        $this->info("Все данные загружены!");
        //
    }
}
