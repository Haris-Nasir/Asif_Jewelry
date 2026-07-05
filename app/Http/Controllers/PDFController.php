<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\tbl_investor;
use App\Models\tbl_investor_transaction;
use App\Models\tbl_lab_job;
use App\Services\InvestorProfitService;
use App\Models\tbl_invoice_mst;
use App\Models\tbl_customer;
use App\Models\tbl_challan_mst;
use App\Models\tbl_challan_details;
use App\Models\tbl_sell_quality;
use App\Models\tbl_broker;
use App\Models\tbl_bank_details;
use App\Models\tbl_gst_code;
use PDF;

class PDFController extends Controller
{
    public function generateDirectInvoicePDF(Request $req, $invoice_id){
        $data = tbl_invoice_mst::with(['challanMstForInvoice','bank'])
            ->where('invoice_mst_status', true)
            ->where('invoice_mst_id', $invoice_id)
            ->select('invoice_mst_id', 'invoice_date', 'no_of_units', 'rate', 'gst_percentage', 'due_date','bank_details_id')
            ->first();

        if (!$data || !$data->challanMstForInvoice || !$data->bank) {
            abort(404, 'Invoice not found.');
        }

        $gstCodeValue = $data->challanMstForInvoice->customer_relation->customer_gst_code;
        $gstEntry = $gstCodeValue
            ? tbl_gst_code::where('gst_code', (int) $gstCodeValue)->first()
            : null;
        $state = $gstEntry ? $gstEntry->state_name : '';

        $invoice = array(
            "customer_company_name" => $data->challanMstForInvoice->customer_relation->customer_company_name,
            "customer_address" => $data->challanMstForInvoice->customer_relation->customer_address,
            "customer_gst_no" => $data->challanMstForInvoice->customer_relation->customer_gst_no,
            "customer_gst_code"=> $data->challanMstForInvoice->customer_relation->customer_gst_code,
            "broker_name"=> $data->challanMstForInvoice->broker->broker_name,
            "bank_name"=>$data->bank->bank_name,
            "branch_name"=> $data->bank->branch_name,
            "account_no"=> $data->bank->account_no,
            "ifsc_code"=> $data->bank->ifsc_code,
            "invoice_date"=> $data->invoice_date,
            "due_date"=> $data->due_date,
            "invoice_mst_id"=> intval($data->invoice_mst_id),
            "challan_no"=> $data->challanMstForInvoice->challan_no,
            "quality_name"=> $data->challanMstForInvoice->quality->quality_name,
            "total_qty"=> $data->challanMstForInvoice->total_qty,
            "weight_grams"=> $data->weight_grams ?? $data->challanMstForInvoice->weight_grams,
            "qty_unit"=> $data->challanMstForInvoice->qty_unit,
            "state_name"=> $state,
            "rate"=> $data->rate,
            "gst_percentage" => $data->gst_percentage
        );

        $invoice = (object)$invoice;

        $pdf = PDF::loadView('invoicePDF', array("invoice" => $invoice, "piecesCount" => $data->no_of_units));
        return $pdf->stream('Invoice - ' . $invoice_id . '.pdf');
    }

    public function generateChallanPDF(Request $req, $challanId){

        $dataToBeValidate = array(
            "challanid" => $challanId
        );

        $validated = validator($dataToBeValidate,[
            'challanid' => 'numeric'
        ]);

        if($validated->fails()){
            $res = array(
                "status" => -1,
                "message" => "Challan Id Is Not In Valid Format"
            );

            return response()->json($res);
        }
        
        $challan = tbl_challan_mst::with([
            'challan_details:challan_mst_id,challan_details_id,no,qty',
            'customer_relation:customer_id,customer_company_name,customer_address,customer_gst_no,customer_contact_no',
            'broker:broker_id,broker_name',
            'quality:sell_quality_id,quality_name,sell_quality_category_id',
            'quality.category:sell_quality_category_id,sell_category_name,metal_type',
        ])
        ->where('challan_mst_id', $challanId)
        ->where('challan_mst_status', true)
        ->first();

        if(is_null($challan)){
            return response()->json(array(
                "status" => 0,
                "message" => "Challan Not Found"
            ));
        }

        $lineItems = [];
        $sr = 1;
        foreach ($challan->challan_details as $detail) {
            $lineItems[] = [
                'sr' => $sr++,
                'qty' => number_format((float) $detail->qty, 2, '.', ''),
            ];
        }

        $challanData = [
            'challanno' => $challan->challan_no,
            'challandate' => \Carbon\Carbon::parse($challan->getRawOriginal('challan_date'))
                ->timezone(config('app.timezone'))
                ->format('d-m-Y, h:i a'),
            'customer' => $challan->customer_relation,
            'broker' => $challan->broker,
            'category' => optional($challan->quality->category)->sell_category_name ?? '-',
            'item_type' => optional($challan->quality)->quality_name ?? '-',
            'metal_type' => ucfirst(optional($challan->quality->category)->metal_type ?? '-'),
            'weight_grams' => number_format((float) $challan->weight_grams, 3, '.', ''),
            'unit' => $challan->qty_unit,
            'line_items' => $lineItems,
            'total_pieces' => number_format((float) $challan->total_qty, 2, '.', ''),
        ];

        $pdf = PDF::loadView('challanPDF', ['challanData' => $challanData]);
        return $pdf->stream('Sales-Bill-' . $challanData['challanno'] . '.pdf');
    }
    
    public function generateInvoicePDF(Request $req, $invoice_id)
    {
        $data = tbl_invoice_mst::with(['challanMstForInvoice', 'bank'])
            ->where('invoice_mst_status', true)
            ->where('invoice_mst_id', $invoice_id)
            ->select('invoice_mst_id', 'invoice_date', 'rate', 'gst_percentage', 'due_date', 'bank_details_id', 'weight_grams', 'no_of_units')
            ->first();

        if (!$data || !$data->challanMstForInvoice || !$data->bank) {
            abort(404, 'Invoice not found.');
        }

        $challan = $data->challanMstForInvoice;
        $piecesCount = max(1, (int) round((float) $challan->total_qty));

        $gstCodeValue = $challan->customer_relation->customer_gst_code;
        $gstEntry = $gstCodeValue
            ? tbl_gst_code::where('gst_code', (int) $gstCodeValue)->first()
            : null;
        $state = $gstEntry ? $gstEntry->state_name : '';

        $invoice = (object) [
            'customer_company_name' => $challan->customer_relation->customer_company_name,
            'customer_address' => $challan->customer_relation->customer_address,
            'customer_gst_no' => $challan->customer_relation->customer_gst_no,
            'customer_gst_code' => $challan->customer_relation->customer_gst_code,
            'broker_name' => $challan->broker->broker_name,
            'bank_name' => $data->bank->bank_name,
            'branch_name' => $data->bank->branch_name,
            'account_no' => $data->bank->account_no,
            'ifsc_code' => $data->bank->ifsc_code,
            'invoice_date' => $data->invoice_date,
            'due_date' => $data->due_date,
            'invoice_mst_id' => (int) $data->invoice_mst_id,
            'challan_no' => $challan->challan_no,
            'quality_name' => $challan->quality->quality_name,
            'total_qty' => $challan->total_qty,
            'weight_grams' => $data->weight_grams ?? $challan->weight_grams,
            'qty_unit' => $challan->qty_unit,
            'state_name' => $state,
            'rate' => $data->rate,
            'gst_percentage' => $data->gst_percentage,
        ];

        $pdf = PDF::loadView('invoicePDF', ['invoice' => $invoice, 'piecesCount' => $piecesCount]);
        return $pdf->stream('Invoice - ' . $invoice_id . '.pdf');
    }

    public function generateInvestorReportPDF(Request $req, $investorId, $period)
    {
        $validated = validator([
            'investor_id' => $investorId,
            'period' => $period,
            'date' => $req->query('date'),
            'from_date' => $req->query('from_date'),
            'to_date' => $req->query('to_date'),
        ], [
            'investor_id' => 'required|integer|exists:tbl_investors,investor_id',
            'period' => 'required|in:daily,weekly,monthly,custom',
            'date' => 'nullable|date',
            'from_date' => 'nullable|date|required_if:period,custom',
            'to_date' => 'nullable|date|required_if:period,custom|after_or_equal:from_date',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => -1,
                'message' => 'Invalid report parameters.',
                'errors' => $validated->errors(),
            ], 422);
        }

        $investor = tbl_investor::findOrFail($investorId);
        $profitService = app(InvestorProfitService::class);
        $summary = $profitService->buildPortalSummary(
            $investor,
            $period,
            $req->query('date'),
            $req->query('from_date'),
            $req->query('to_date')
        );

        $transactions = tbl_investor_transaction::where('investor_id', $investorId)
            ->where('transaction_status', true)
            ->whereBetween('transaction_date', [$summary['period']['from_date'], $summary['period']['to_date']])
            ->orderBy('transaction_date')
            ->get();

        $labJobs = tbl_lab_job::with([
            'jobInvestors' => function ($q) use ($investorId) {
                $q->where('investor_id', $investorId);
            },
        ])
            ->where('lab_job_status', true)
            ->whereBetween('job_date', [$summary['period']['from_date'], $summary['period']['to_date']])
            ->whereHas('jobInvestors', function ($q) use ($investorId) {
                $q->where('investor_id', $investorId);
            })
            ->orderBy('job_date')
            ->get()
            ->map(function ($job) use ($investorId) {
                $participant = $job->jobInvestors->firstWhere('investor_id', $investorId);
                $job->my_share_percentage = $participant ? (float) $participant->share_percentage : 0;
                $job->my_profit_share = $participant && $participant->profit_share !== null
                    ? (float) $participant->profit_share
                    : null;

                return $job;
            });

        $reportTitle = ucfirst(str_replace('_', ' ', $summary['period']['type'])) . ' Lab Investor Report';

        $pdf = PDF::loadView('investorReportPDF', [
            'investor' => $investor,
            'reportTitle' => $reportTitle,
            'period' => $summary['period'],
            'investmentSummary' => $summary['investment_summary'],
            'labSummary' => $summary['lab_summary'],
            'goldHoldings' => $summary['gold_holdings'],
            'expenseAllocations' => $summary['expense_allocations'],
            'transactions' => $transactions,
            'labJobs' => $labJobs,
            'generatedAt' => now()->format('d-m-Y H:i'),
        ]);

        $filename = 'Investor-Report-' . $investor->investor_name . '-' . $summary['period']['from_date'] . '.pdf';
        return $pdf->stream($filename);
    }
}
