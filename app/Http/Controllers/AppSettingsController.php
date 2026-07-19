<?php

namespace App\Http\Controllers;

use App\Models\tbl_app_setting;
use Illuminate\Http\Request;

class AppSettingsController extends Controller
{
    public function show()
    {
        return response()->json([
            'shop_profit_percentage' => tbl_app_setting::shopProfitPercentage(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'shop_profit_percentage' => 'required|numeric|min:0|max:100',
        ]);

        tbl_app_setting::setValue(
            tbl_app_setting::KEY_SHOP_PROFIT_PERCENTAGE,
            round((float) $validated['shop_profit_percentage'], 2)
        );

        return response()->json([
            'status' => 1,
            'message' => 'Settings saved successfully.',
            'data' => [
                'shop_profit_percentage' => tbl_app_setting::shopProfitPercentage(),
            ],
        ]);
    }
}
