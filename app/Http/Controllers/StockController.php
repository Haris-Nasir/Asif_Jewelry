<?php

namespace App\Http\Controllers;

use App\Models\tbl_metal_balance;
use App\Models\tbl_stock_ledger;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function balances()
    {
        $balances = tbl_metal_balance::all()->keyBy('metal_type');

        return response()->json([
            'gold' => $balances->get('gold'),
            'silver' => $balances->get('silver'),
            'by_quality' => $this->stockService->getAllQualityBalances(),
        ]);
    }

    public function qualityBalance(int $sellQualityId)
    {
        return response()->json($this->stockService->getQualityBalance($sellQualityId));
    }

    public function ledger(Request $request)
    {
        $paginate = (int) $request->input('paginate', 20);
        $query = tbl_stock_ledger::with('item:sell_quality_id,quality_name')
            ->orderByDesc('created_at');

        if ($request->filled('metal_type')) {
            $query->where('metal_type', $request->metal_type);
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        return response()->json($query->paginate($paginate));
    }
}
