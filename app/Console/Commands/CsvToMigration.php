<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CsvToMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:csvToMigration {csv} {--table=}';

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
        $csvFile = $this->argument('csv');

        if (!file_exists($csvFile)) {
            $this->error("CSV файл не найден: $csvFile");
            return;
        }

        $tableName = $this->option('table') ?? pathinfo($csvFile, PATHINFO_FILENAME);
        $rows = array_map('str_getcsv', file($csvFile));
        if (empty($rows)) {
            $this->error("CSV файл пустой");
            return;
        }

        $header = array_shift($rows);

        // Определяем типы колонок
        $types = [];
        foreach ($header as $col) {
            $types[$col] = 'string'; // по умолчанию
        }

        foreach ($rows as $row) {
            foreach ($row as $i => $value) {
                $col = $header[$i];
                if ($value === '' || strtolower($value) === 'null') continue; // null оставляем
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) $types[$col] = 'date';
                elseif (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) $types[$col] = 'dateTime';
                elseif (ctype_digit($value)) $types[$col] = 'integer';
                elseif (is_numeric($value)) $types[$col] = 'float';
                else $types[$col] = 'string';
            }
        }

        $migrationName = 'create_' . Str::snake($tableName) . '_table';
        $timestamp = date('Y_m_d_His');
        $filePath = database_path("migrations/{$timestamp}_{$migrationName}.php");

        $content = "<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
";
        $content .= "            \$table->id();\n";
        foreach ($types as $col => $type) {
            $content .= "            \$table->{$type}('{$col}')->nullable();\n";
        }
        $content .= "        });\n";
        $content .= "    }\n\n";
        $content .= "    public function down(): void\n";
        $content .= "    {\n";
        $content .= "        Schema::dropIfExists('{$tableName}');\n";
        $content .= "    }\n";
        $content .= "};\n";

        file_put_contents($filePath, $content);
        $this->info("Миграция создана: $filePath");
    }
}
