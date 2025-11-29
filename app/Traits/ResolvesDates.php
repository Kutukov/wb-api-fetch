<?php

namespace App\Traits;

use Illuminate\Console\Command;

trait ResolvesDates
{
    /**
     * Возвращает диапазон дат для таблицы.
     * Для stocks всегда сегодня, для остальных — из опций или по умолчанию вчера-сегодня.
     */
    private function resolveDates(string $table, Command $command): array
    {
        if ($table === 'stocks') {
            $today = now()->format('Y-m-d');
            return [$today, $today];
        }

        return [
            $command->option('from') ?? now()->subDay()->format('Y-m-d'),
            $command->option('to') ?? now()->format('Y-m-d'),
        ];
    }
}
