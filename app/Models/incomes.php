<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class incomes extends Model
{
    protected $table = 'incomes';
    protected $fillable = [
        'income_id',
        'number',
        'date',
        'last_change_date',
        'supplier_article',
        'tech_size',
        'barcode',
        'quantity',
        'total_price',
        'odid',
        'nm_id',
        'subject',
        'category',
        'brand',
        'is_cancel',
        'cancel_dt'
    ];
}
