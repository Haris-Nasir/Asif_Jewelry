<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tbl_invoice_detail extends Model
{
    protected $table = 'tbl_invoice_details';
    protected $primaryKey = 'invoice_detail_id';

    protected $fillable = [
        'invoice_mst_id',
        'sell_quality_id',
        'sell_category_id',
        'qty',
        'qty_unit',
        'weight_grams',
        'rate',
        'base_amount',
        'gst_percentage',
        'gst_amount',
        'sold_amount',
        'cost_amount',
        'profit_amount',
        'invoice_detail_status',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'weight_grams' => 'decimal:3',
        'rate' => 'decimal:2',
        'base_amount' => 'decimal:2',
        'gst_percentage' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'sold_amount' => 'decimal:2',
        'cost_amount' => 'decimal:2',
        'profit_amount' => 'decimal:2',
        'invoice_detail_status' => 'boolean',
    ];

    public function invoice()
    {
        return $this->belongsTo(tbl_invoice_mst::class, 'invoice_mst_id', 'invoice_mst_id');
    }

    public function quality()
    {
        return $this->belongsTo(tbl_sell_quality::class, 'sell_quality_id', 'sell_quality_id');
    }

    public function category()
    {
        return $this->belongsTo(tbl_sell_quality_category::class, 'sell_category_id', 'sell_quality_category_id');
    }
}
