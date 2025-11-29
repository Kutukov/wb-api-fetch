<?php


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/test-api', function () {
    $dateFrom = '2000-01-01';
    $dateTo = '2025-11-27';
    $limit = 1;

    $response = Http::get('http://109.73.206.144:6969/api/orders', [
        'key' => 'E6kUTYrYwZq2tN4QEtyzsbEBk3ie',
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'limit' => $limit,
        'page' => 1
    ]);

    $sales = $response->json();

    dd($sales);
});
