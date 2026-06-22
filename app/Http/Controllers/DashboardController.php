<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\tbl_inward_mst;
use App\Models\tbl_invoice_mst;
use App\Models\tbl_credit;
use App\Models\tbl_expense;
use App\Models\tbl_metal_balance;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function dashboardCalculations(Request $req)
    {
        $data = array();

        $financialYear = $this -> getFinancialYear(Carbon::now());
        array_push($data, $financialYear);

        $purchaseCount = tbl_inward_mst::whereBetween("inward_mst_date",[$financialYear['fromDate'],$financialYear['toDate']])->where("inward_mst_status","=","1")->count();
        array_push($data, $purchaseCount);

        $salesBillCount = tbl_invoice_mst::whereBetween("invoice_date",[$financialYear['fromDate'],$financialYear['toDate']])->where("invoice_mst_status","=","1")->count();
        array_push($data, $salesBillCount);

        $creditAmount = tbl_credit::whereBetween("credit_date",[$financialYear['fromDate'],$financialYear['toDate']])->where("credit_status","=","1")->sum('credit_amount');
        array_push($data, $creditAmount);

        $expenseAmount = tbl_expense::whereBetween("expense_date",[$financialYear['fromDate'],$financialYear['toDate']])->where("expense_status","=","1")->sum('expense_amount');
        array_push($data, $expenseAmount);

        $totalProfit = tbl_invoice_mst::whereBetween("invoice_date",[$financialYear['fromDate'],$financialYear['toDate']])->where("invoice_mst_status","=","1")->sum('profit_amount');
        array_push($data, $totalProfit);

        $goldBalance = tbl_metal_balance::find('gold');
        $silverBalance = tbl_metal_balance::find('silver');
        array_push($data, [
            'gold' => $goldBalance ? (float) $goldBalance->total_weight_grams : 0,
            'silver' => $silverBalance ? (float) $silverBalance->total_weight_grams : 0,
        ]);

        return $data;
    }

    public function getFinancialYear($date){

        $splitDate = explode("-",$date);
        $Month = $splitDate[1];
        $Year = $splitDate[0];

        $fromDate = $Year;
        $toDate = $Year;
        if((int)$Month<4){
            $fromDate = (int)$Year - 1;
        }else{
            $toDate = (int)$Year + 1;
        }

        $fromDate = $fromDate."-04-01";
        $toDate = $toDate."-03-31";

        return array(
            "fromDate" => $fromDate,
            "toDate" => $toDate
        );
    }
}
