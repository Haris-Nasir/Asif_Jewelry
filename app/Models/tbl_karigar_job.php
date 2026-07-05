<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tbl_karigar_job extends Model
{
    protected $table = 'tbl_karigar_jobs';
    protected $primaryKey = 'karigar_job_id';

    protected $casts = [
        'job_date' => 'datetime',
        'return_date' => 'datetime',
        'issued_weight_grams' => 'decimal:3',
        'returned_weight_grams' => 'decimal:3',
        'wastage_grams' => 'decimal:3',
        'mazduri_cost' => 'decimal:2',
    ];

    public function karigar()
    {
        return $this->belongsTo(tbl_karigar::class, 'karigar_id', 'karigar_id');
    }

    public function quality()
    {
        return $this->belongsTo(tbl_sell_quality::class, 'sell_quality_id', 'sell_quality_id')
            ->with('category:sell_quality_category_id,sell_category_name,metal_type');
    }
}
