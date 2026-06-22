<?php

namespace App\Services;

use App\Models\tbl_metal_balance;
use App\Models\tbl_stock_ledger;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockService
{
    public function getBalance(string $metalType): tbl_metal_balance
    {
        return tbl_metal_balance::firstOrCreate(
            ['metal_type' => $metalType],
            ['total_weight_grams' => 0, 'total_pieces' => 0]
        );
    }

    public function getAverageRate(string $metalType): float
    {
        $purchases = tbl_stock_ledger::where('metal_type', $metalType)
            ->where('transaction_type', 'purchase')
            ->where('weight_grams', '>', 0)
            ->get(['weight_grams', 'amount']);

        if ($purchases->isEmpty()) {
            return 0;
        }

        $totalWeight = $purchases->sum('weight_grams');
        $totalAmount = $purchases->sum('amount');

        return $totalWeight > 0 ? round($totalAmount / $totalWeight, 2) : 0;
    }

    public function addPurchase(
        string $metalType,
        ?int $sellQualityId,
        float $weightGrams,
        int $pieces,
        float $ratePerGram,
        float $amount,
        string $referenceType,
        int $referenceId,
        ?int $userId = null
    ): tbl_stock_ledger {
        if ($weightGrams <= 0) {
            throw new RuntimeException('Purchase weight must be greater than zero.');
        }

        return DB::transaction(function () use (
            $metalType,
            $sellQualityId,
            $weightGrams,
            $pieces,
            $ratePerGram,
            $amount,
            $referenceType,
            $referenceId,
            $userId
        ) {
            $balance = $this->getBalance($metalType);
            $balance->total_weight_grams = round((float) $balance->total_weight_grams + $weightGrams, 3);
            $balance->total_pieces = (int) $balance->total_pieces + $pieces;
            $balance->save();

            return tbl_stock_ledger::create([
                'metal_type' => $metalType,
                'sell_quality_id' => $sellQualityId,
                'transaction_type' => 'purchase',
                'weight_grams' => $weightGrams,
                'quantity_pieces' => $pieces,
                'rate_per_gram' => $ratePerGram,
                'amount' => $amount,
                'balance_weight_after' => $balance->total_weight_grams,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => $userId,
            ]);
        });
    }

    public function recordSale(
        string $metalType,
        ?int $sellQualityId,
        float $weightGrams,
        int $pieces,
        float $soldAmount,
        string $referenceType,
        int $referenceId,
        ?int $userId = null
    ): array {
        if ($weightGrams <= 0) {
            throw new RuntimeException('Sale weight must be greater than zero.');
        }

        return DB::transaction(function () use (
            $metalType,
            $sellQualityId,
            $weightGrams,
            $pieces,
            $soldAmount,
            $referenceType,
            $referenceId,
            $userId
        ) {
            $balance = $this->getBalance($metalType);

            if ((float) $balance->total_weight_grams < $weightGrams) {
                throw new RuntimeException('Insufficient ' . $metalType . ' stock. Available: ' . $balance->total_weight_grams . 'g');
            }

            $avgRate = $this->getAverageRate($metalType);
            $costAmount = round($weightGrams * $avgRate, 2);
            $profitAmount = round($soldAmount - $costAmount, 2);

            $balance->total_weight_grams = round((float) $balance->total_weight_grams - $weightGrams, 3);
            $balance->total_pieces = max(0, (int) $balance->total_pieces - $pieces);
            $balance->save();

            tbl_stock_ledger::create([
                'metal_type' => $metalType,
                'sell_quality_id' => $sellQualityId,
                'transaction_type' => 'sale',
                'weight_grams' => $weightGrams,
                'quantity_pieces' => $pieces,
                'rate_per_gram' => $weightGrams > 0 ? round($soldAmount / $weightGrams, 2) : 0,
                'amount' => $soldAmount,
                'balance_weight_after' => $balance->total_weight_grams,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => $userId,
                'notes' => 'Cost: ' . $costAmount . ', Profit: ' . $profitAmount,
            ]);

            return [
                'cost_amount' => $costAmount,
                'profit_amount' => $profitAmount,
                'average_rate' => $avgRate,
            ];
        });
    }

    public function resolveMetalTypeFromCategory(int $categoryId): string
    {
        $category = DB::table('tbl_sell_quality_categories')
            ->where('sell_quality_category_id', $categoryId)
            ->first();

        return $category && $category->metal_type === 'silver' ? 'silver' : 'gold';
    }
}
