<?php

namespace App\Http\Controllers;

use App\Models\tbl_metal_balance;
use App\Models\tbl_stock_ledger;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function balances()
    {
        $balances = tbl_metal_balance::all()->keyBy('metal_type');

        return response()->json([
            'gold' => $balances->get('gold'),
            'silver' => $balances->get('silver'),
        ]);
    }

    public function ledger(Request $request)
    {
        $query = tbl_stock_ledger::with('item:sell_quality_id,quality_name')
            ->orderByDesc('created_at');

        if ($request->filled('metal_type')) {
            $query->where('metal_type', $request->metal_type);
        }

        return response()->json($query->paginate(20));
    }
}
