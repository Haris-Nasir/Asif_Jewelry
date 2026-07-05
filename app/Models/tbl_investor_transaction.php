<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tbl_investor_transaction extends Model
{
    protected $table = 'tbl_investor_transactions';
    protected $primaryKey = 'investor_transaction_id';

    protected $fillable = [
        'investor_id',
        'lab_job_id',
        'transaction_date',
        'transaction_type',
        'metal_type',
        'weight_grams',
        'rate_per_gram',
        'amount',
        'notes',
        'created_by',
        'transaction_status',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'weight_grams' => 'decimal:3',
        'rate_per_gram' => 'decimal:2',
        'amount' => 'decimal:2',
        'transaction_status' => 'boolean',
    ];

    public function investor()
    {
        return $this->belongsTo(tbl_investor::class, 'investor_id', 'investor_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
