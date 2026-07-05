<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tbl_lab_job_investor extends Model
{
    protected $table = 'tbl_lab_job_investors';
    protected $primaryKey = 'lab_job_investor_id';

    protected $fillable = [
        'lab_job_id',
        'investor_id',
        'investment_basis',
        'share_percentage',
        'profit_share',
    ];

    protected $casts = [
        'investment_basis' => 'decimal:2',
        'share_percentage' => 'decimal:4',
        'profit_share' => 'decimal:2',
    ];

    public function labJob()
    {
        return $this->belongsTo(tbl_lab_job::class, 'lab_job_id', 'lab_job_id');
    }

    public function investor()
    {
        return $this->belongsTo(tbl_investor::class, 'investor_id', 'investor_id');
    }
}
