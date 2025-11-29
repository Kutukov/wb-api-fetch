<?php

namespace App\Repositories;

use App\Models\Sales;

class SalesRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Sales());
    }
}
