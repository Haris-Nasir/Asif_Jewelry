<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tbl_lab_job extends Model
{
    protected $table = 'tbl_lab_jobs';
    protected $primaryKey = 'lab_job_id';

    protected $fillable = [
        'job_date',
        'investor_id',
        'job_reference',
        'metal_type',
        'weight_grams',
        'base_price',
        'refinery_cost',
        'sold_amount',
        'profit_amount',
        'job_status',
        'notes',
        'created_by',
        'lab_job_status',
    ];

    protected $casts = [
        'job_date' => 'datetime',
        'weight_grams' => 'decimal:3',
        'base_price' => 'decimal:2',
        'refinery_cost' => 'decimal:2',
        'sold_amount' => 'decimal:2',
        'profit_amount' => 'decimal:2',
        'lab_job_status' => 'boolean',
    ];

    public function investor()
    {
        return $this->belongsTo(tbl_investor::class, 'investor_id', 'investor_id');
    }

    public function jobInvestors()
    {
        return $this->hasMany(tbl_lab_job_investor::class, 'lab_job_id', 'lab_job_id');
    }

    public function investors()
    {
        return $this->belongsToMany(
            tbl_investor::class,
            'tbl_lab_job_investors',
            'lab_job_id',
            'investor_id',
            'lab_job_id',
            'investor_id'
        )->withPivot(['investment_basis', 'share_percentage', 'profit_share']);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
