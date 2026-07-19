<?php
// DESCRIPTION
//     This module generates a Manages Pre-Generated Invoices where user have to enter info. related to Invoioce 
//     and add that information to Database
// NOTES
//     Version         : 1.0
//     Date            : 01/10/2021
//     Author          : Uddhav Savani

//     Initial Release : v1.0: Initial Release

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class tbl_invoice_mst extends Model
{
    use HasFactory;

    protected $primaryKey = "invoice_mst_id";

    protected $casts = [
        'invoice_date' => 'datetime',
    ];

    public static function hasChallanOrInvoiceWithInGivenInvoceNoAndFinancialYear($invoiceNo, $financialYear){
        $isChallanExists = tbl_challan_mst::where('challan_no',$invoiceNo)
        ->whereDate('challan_date', ">=", $financialYear['fromDate'])
        ->whereDate('challan_date', "<=", $financialYear['toDate'])
        ->where('challan_mst_status', true)
        ->exists();

        return $isChallanExists;
        
    }

    public function challanMst(){
        return $this->hasOne('App\Models\tbl_challan_mst', 'challan_mst_id', 'invoice_mst_id')->with('quality:sell_quality_id,quality_name,sell_quality_category_id');
    }

    public function challanMstForInvoice(){
        return $this->hasOne('App\Models\tbl_challan_mst', 'challan_mst_id', 'invoice_mst_id')->with(['quality:sell_quality_id,quality_name,sell_quality_category_id', 'customer_relation:customer_id,customer_company_name,customer_contact_no,customer_gst_no,customer_address,customer_gst_code','broker:broker_id,broker_name']);
    }
    
    public function challanMstForInvoiceFromChallan(){
        return $this->hasOne('App\Models\tbl_challan_mst', 'challan_mst_id', 'invoice_mst_id')->with(['quality:sell_quality_id,quality_name,sell_quality_category_id', 'customer:customer_id,customer_company_name,customer_contact_no,customer_gst_no', 'broker:broker_id,broker_name,broker_contact_no', "challan_details:challan_mst_id,qty"]);
    }

    public function bank(){
        return $this->hasOne('App\Models\tbl_bank_details', 'bank_details_id', 'bank_details_id');
    }

    public function details()
    {
        return $this->hasMany(tbl_invoice_detail::class, 'invoice_mst_id', 'invoice_mst_id')
            ->where('invoice_detail_status', true)
            ->with([
                'quality:sell_quality_id,quality_name,sell_quality_category_id',
                'category:sell_quality_category_id,sell_category_name',
            ]);
    }
}
