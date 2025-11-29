<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WbApiService;
use App\Traits\ResolvesDates;

class ImportCsv extends Command
{
    use ResolvesDates;
    protected $signature = 'wb:csvImport {table} {--from=} {--to=}';
    protected $description = '';

    public function handle(WbApiService $api)
    {
        $tableArg = $this->argument('table');

        [$dateFrom, $dateTo] = $this->resolveDates($tableArg, $this);

        $path = storage_path("app/raw_data/{$tableArg}.csv");
        $first = true;

        $this->info("Экспорт {$tableArg} в CSV…");

        foreach (
            $api->getPaginated($tableArg, [
                'dateFrom' => $dateFrom,
                'dateTo'   => $dateTo,
            ]) as $chunk
        ) {

            $file = fopen($path, $first ? 'w' : 'a');

            if ($first) {
                fputcsv($file, array_keys($chunk[0]));
                $first = false;
            }

            foreach ($chunk as $row) {
                fputcsv($file, $row);
            }

            fclose($file);

            $this->info("Экспортировано: " . count($chunk));
        }

        $this->info("Файл: $path");
    }
}
