<?php

namespace App\Repositories;

use App\Models\Incomes;

class IncomesRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Incomes());
    }
}
