<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WbApiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $limit = 500;
    protected float $delay = 0.5;


    public function __construct()
    {
        $this->apiKey = config('app.wb_api_key');
        $this->baseUrl = config('app.wb_api_host');
    }

    /**
     * Универсальный метод для GET с пагинацией
     */
    public function getPaginated(string $endpoint, array $params = []): \Generator
    {
        $page = 1;

        do {
            $query = array_merge($params, [
                'page' => $page,
                'limit' => $this->limit,
                'key' => $this->apiKey
            ]);

            $response = Http::get("{$this->baseUrl}/{$endpoint}", $query);


            $data = $response->json()['data'] ?? [];
            if (empty($data)) {
                echo ("Таблица $endpoint закончена, всего страниц: " . ($page - 1));
                break;
            }
            yield $data;
            $page++;
            usleep($this->delay * 1_000_000);
        } while (true);
    }


    public function getSales(string $from, string $to): \Generator
    {
        return $this->getPaginated('sales', [
            'dateFrom' => $from,
            'dateTo' => $to
        ]);
    }

    public function getOrders(string $from, string $to): \Generator
    {
        return $this->getPaginated('orders', [
            'dateFrom' => $from,
            'dateTo' => $to
        ]);
    }

    public function getStocks(string $from, string $to): \Generator
    {
        return $this->getPaginated('stocks', [
            'dateFrom' => $from,
            'dateTo' => $to
        ]);
    }

    public function getIncomes(string $from, string $to): \Generator
    {
        return $this->getPaginated('incomes', [
            'dateFrom' => $from,
            'dateTo' => $to
        ]);
    }
}
