<?php

namespace App\Http\Controllers;

use App\Models\tbl_investor;
use Illuminate\Http\Request;

class InvestorController extends Controller
{
    public function summary(Request $request)
    {
        $investor = tbl_investor::where('user_id', $request->user()->id)->first();

        if (!$investor) {
            return response()->json(['message' => 'Investor profile not found.'], 404);
        }

        return response()->json([
            'investor' => $investor,
            'message' => 'Investor reports will be available in Phase 3.',
            'deposits' => [],
            'profit_summary' => [
                'total_invested' => 0,
                'total_profit' => 0,
                'share_percentage' => $investor->profit_share_percentage,
            ],
        ]);
    }
}
