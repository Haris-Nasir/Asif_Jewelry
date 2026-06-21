<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tbl_metal_balance extends Model
{
    protected $table = 'tbl_metal_balances';
    protected $primaryKey = 'metal_type';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'metal_type',
        'total_weight_grams',
        'total_pieces',
    ];

    protected $casts = [
        'total_weight_grams' => 'decimal:3',
        'total_pieces' => 'integer',
    ];
}
