<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository
{
    protected Model $model;
    protected int $chunkSize = 500;
    protected ?string $uniqueKey; // уникальное поле или null

    public function __construct(Model $model, ?string $uniqueKey = null)
    {
        $this->model = $model;
        $this->uniqueKey = $uniqueKey;
    }

    /**
     * Сохраняет данные:
     * - если uniqueKey задан — вставка + обновление
     * - если uniqueKey null — просто вставка
     */
    public function save(array $data)
    {
        if (empty($data)) {
            echo "Нет данных для сохранения." . PHP_EOL;
            return;
        }

        if ($this->uniqueKey) {
            $this->saveWithUpdate($data);
        } else {
            $this->saveAll($data);
        }
    }

    /**
     * Вставка новых и обновление существующих по uniqueKey
     */
    protected function saveWithUpdate(array $data)
    {
        $uniqueValues = array_column($data, $this->uniqueKey);

        $existingValues = $this->model
            ->whereIn($this->uniqueKey, $uniqueValues)
            ->pluck($this->uniqueKey)
            ->toArray();

        $toInsert = [];
        $toUpdate = [];

        foreach ($data as $row) {
            if (in_array($row[$this->uniqueKey], $existingValues)) {
                $toUpdate[] = $row;
            } else {
                $toInsert[] = $row;
            }
        }

        // Вставка
        $insertChunks = array_chunk($toInsert, $this->chunkSize);
        foreach ($insertChunks as $i => $chunk) {
            $this->model->insert($chunk);
            echo "Вставлен пакет " . ($i + 1) . " из " . count($insertChunks) . ", строк: " . count($chunk) . PHP_EOL;
        }

        // Обновление
        $updateChunks = array_chunk($toUpdate, $this->chunkSize);
        foreach ($updateChunks as $i => $chunk) {
            DB::transaction(function () use ($chunk) {
                foreach ($chunk as $row) {
                    $this->model->where($this->uniqueKey, $row[$this->uniqueKey])->update($row);
                }
            });
            echo "Обновлён пакет " . ($i + 1) . " из " . count($updateChunks) . ", строк: " . count($chunk) . PHP_EOL;
        }
    }

    /**
     * Простая пакетная вставка без обновления
     */
    protected function saveAll(array $data)
    {
        $chunks = array_chunk($data, $this->chunkSize);
        foreach ($chunks as $i => $chunk) {
            $this->model->insert($chunk);
            echo "Вставлен пакет " . ($i + 1) . " из " . count($chunks) . ", строк: " . count($chunk) . PHP_EOL;
        }
    }
}
