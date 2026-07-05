<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class tbl_inward_mst extends Model
{
    use HasFactory;

    protected $primaryKey = 'inward_mst_id';

    protected $casts = [
        'inward_mst_date' => 'datetime',
    ];

    public function setInwardMstInvoiceNoAttribute($value){
        $value = preg_replace('/\s+/', ' ', $value);
        $this->attributes['inward_mst_invoice_no'] = strtoupper($value);
    }

    public function inward_details(){
        return $this->hasOne('App\Models\tbl_inward_details', 'inward_mst_id', 'inward_mst_id')
            ->with('quality:sell_quality_id,sell_quality_category_id,quality_name');
    }
    
    public function getBroker(){
        return $this->hasOne('App\Models\tbl_broker', "broker_id", "inward_mst_broker_id");
    }

    public function getVendor(){
        return $this->hasOne('App\Models\tbl_vendor', 'vendor_id', 'inward_mst_vendor_id');
    }

    public static function getNextInvoiceNo(string $fromDate, string $toDate): string
    {
        $max = self::where('inward_mst_status', true)
            ->whereDate('inward_mst_date', '>=', $fromDate)
            ->whereDate('inward_mst_date', '<=', $toDate)
            ->pluck('inward_mst_invoice_no')
            ->reduce(function (int $carry, $invoiceNo) {
                $invoiceNo = trim((string) $invoiceNo);

                if ($invoiceNo !== '' && ctype_digit($invoiceNo)) {
                    return max($carry, (int) $invoiceNo);
                }

                return $carry;
            }, 0);

        return (string) ($max + 1);
    }

}
