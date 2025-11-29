<?php

namespace App\Repositories;

use App\Models\Stocks;

class StocksRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Stocks());
    }
}
