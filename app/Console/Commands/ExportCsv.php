<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:csv {file} {--table=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $table = $this->option('table') ?? pathinfo($filePath, PATHINFO_FILENAME);

        if (!file_exists($filePath)) {
            $this->error("Файл не найден: $filePath");
            return;
        }

        $rows = array_map('str_getcsv', file($filePath));
        if (empty($rows)) {
            $this->error("CSV файл пустой: $filePath");
            return;
        }

        $header = array_shift($rows); // заголовки колонок
        $batchSize = 500;
        $batch = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $data = [];

                foreach ($header as $i => $col) {
                    $value = $row[$i] ?? null;
                    $value = trim($value);

                    // пустые значения → null
                    if ($value === '') {
                        $value = null;
                    }

                    $data[$col] = $value;
                }

                $batch[] = $data;

                if (count($batch) >= $batchSize) {
                    DB::table($table)->insert($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                DB::table($table)->insert($batch);
            }

            DB::commit();
            $this->info("Импорт завершён в таблицу $table, строк: " . count($rows));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Ошибка импорта: " . $e->getMessage());
        }
    }
}
