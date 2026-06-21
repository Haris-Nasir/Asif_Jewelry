<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tbl_stock_ledger extends Model
{
    protected $table = 'tbl_stock_ledger';
    protected $primaryKey = 'stock_ledger_id';

    protected $fillable = [
        'metal_type',
        'sell_quality_id',
        'transaction_type',
        'weight_grams',
        'quantity_pieces',
        'rate_per_gram',
        'amount',
        'balance_weight_after',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'weight_grams' => 'decimal:3',
        'rate_per_gram' => 'decimal:2',
        'amount' => 'decimal:2',
        'balance_weight_after' => 'decimal:3',
        'quantity_pieces' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(tbl_sell_quality::class, 'sell_quality_id', 'sell_quality_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
