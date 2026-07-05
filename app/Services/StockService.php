<?php

namespace App\Services;

use App\Models\tbl_metal_balance;
use App\Models\tbl_sell_quality;
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

    public function getQualityBalance(int $sellQualityId): array
    {
        $quality = tbl_sell_quality::find($sellQualityId);
        $qualityName = $quality ? $quality->quality_name : 'Unknown item';

        $purchasedWeight = (float) tbl_stock_ledger::where('sell_quality_id', $sellQualityId)
            ->where('transaction_type', 'purchase')
            ->sum('weight_grams');
        $soldWeight = (float) tbl_stock_ledger::where('sell_quality_id', $sellQualityId)
            ->where('transaction_type', 'sale')
            ->sum('weight_grams');

        $purchasedPieces = (int) tbl_stock_ledger::where('sell_quality_id', $sellQualityId)
            ->where('transaction_type', 'purchase')
            ->sum('quantity_pieces');
        $soldPieces = (int) tbl_stock_ledger::where('sell_quality_id', $sellQualityId)
            ->where('transaction_type', 'sale')
            ->sum('quantity_pieces');

        return [
            'sell_quality_id' => $sellQualityId,
            'quality_name' => $qualityName,
            'weight_grams' => round(max($purchasedWeight - $soldWeight, 0), 3),
            'pieces' => max($purchasedPieces - $soldPieces, 0),
        ];
    }

    public function getAllQualityBalances()
    {
        return tbl_sell_quality::where('sell_quality_status', true)
            ->orderBy('quality_name')
            ->get(['sell_quality_id', 'quality_name'])
            ->map(function ($quality) {
                $balance = $this->getQualityBalance((int) $quality->sell_quality_id);

                return [
                    'sell_quality_id' => $quality->sell_quality_id,
                    'quality_name' => $quality->quality_name,
                    'weight_grams' => $balance['weight_grams'],
                    'pieces' => $balance['pieces'],
                ];
            })
            ->values();
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

    public function reversePurchase(
        string $referenceType,
        int $referenceId,
        ?int $userId = null
    ): void {
        $entry = tbl_stock_ledger::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->where('transaction_type', 'purchase')
            ->first();

        if (!$entry) {
            return;
        }

        DB::transaction(function () use ($entry, $referenceType, $referenceId, $userId) {
            $weightGrams = (float) $entry->weight_grams;
            $pieces = (int) $entry->quantity_pieces;
            $balance = $this->getBalance($entry->metal_type);

            if ((float) $balance->total_weight_grams < $weightGrams - 0.0005) {
                throw new RuntimeException(
                    'Cannot delete purchase: ' . $entry->metal_type . ' stock has already been used.'
                );
            }

            if ($entry->sell_quality_id) {
                $qualityBalance = $this->getQualityBalance((int) $entry->sell_quality_id);

                if ($qualityBalance['weight_grams'] < $weightGrams - 0.0005) {
                    throw new RuntimeException(
                        'Cannot delete purchase: "' . $qualityBalance['quality_name'] . '" stock has already been sold.'
                    );
                }
            }

            $balance->total_weight_grams = round((float) $balance->total_weight_grams - $weightGrams, 3);
            $balance->total_pieces = max(0, (int) $balance->total_pieces - $pieces);
            $balance->save();

            tbl_stock_ledger::create([
                'metal_type' => $entry->metal_type,
                'sell_quality_id' => $entry->sell_quality_id,
                'transaction_type' => 'adjustment',
                'weight_grams' => -$weightGrams,
                'quantity_pieces' => 0,
                'rate_per_gram' => $entry->rate_per_gram,
                'amount' => $entry->amount ? -((float) $entry->amount) : null,
                'balance_weight_after' => $balance->total_weight_grams,
                'reference_type' => $referenceType . '_reversal',
                'reference_id' => $referenceId,
                'created_by' => $userId,
                'notes' => 'Purchase deleted (' . $pieces . ' pcs reversed)',
            ]);

            $entry->delete();
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
            $this->assertQualityStockAvailable($sellQualityId, $weightGrams, $pieces);

            $balance = $this->getBalance($metalType);

            if ((float) $balance->total_weight_grams < $weightGrams) {
                throw new RuntimeException(
                    'Insufficient total ' . $metalType . ' stock. Available: '
                    . number_format((float) $balance->total_weight_grams, 3) . 'g, requested: '
                    . number_format($weightGrams, 3) . 'g.'
                );
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

    public function issueMetalToKarigar(
        string $metalType,
        float $weightGrams,
        int $referenceId,
        ?int $userId = null,
        ?string $notes = null
    ): tbl_stock_ledger {
        if ($weightGrams <= 0) {
            throw new RuntimeException('Issue weight must be greater than zero.');
        }

        return DB::transaction(function () use ($metalType, $weightGrams, $referenceId, $userId, $notes) {
            $balance = $this->getBalance($metalType);
            $available = (float) $balance->total_weight_grams;

            if ($available < $weightGrams - 0.0005) {
                throw new RuntimeException(
                    'Insufficient ' . $metalType . ' stock for karigar issue. Available: '
                    . number_format($available, 3) . 'g.'
                );
            }

            $avgRate = $this->getAverageRate($metalType);
            $amount = round($weightGrams * $avgRate, 2);

            $balance->total_weight_grams = round($available - $weightGrams, 3);
            $balance->save();

            return tbl_stock_ledger::create([
                'metal_type' => $metalType,
                'sell_quality_id' => null,
                'transaction_type' => 'adjustment',
                'weight_grams' => $weightGrams,
                'quantity_pieces' => 0,
                'rate_per_gram' => $avgRate,
                'amount' => $amount,
                'balance_weight_after' => $balance->total_weight_grams,
                'reference_type' => 'karigar_issue',
                'reference_id' => $referenceId,
                'created_by' => $userId,
                'notes' => $notes ?: 'Metal issued to karigar',
            ]);
        });
    }

    public function returnMetalFromKarigar(
        string $metalType,
        int $sellQualityId,
        float $weightGrams,
        int $pieces,
        int $referenceId,
        ?int $userId = null,
        ?string $notes = null
    ): tbl_stock_ledger {
        $avgRate = $this->getAverageRate($metalType);
        $amount = round($weightGrams * $avgRate, 2);

        return $this->addPurchase(
            $metalType,
            $sellQualityId,
            $weightGrams,
            $pieces,
            $avgRate,
            $amount,
            'karigar_return',
            $referenceId,
            $userId
        );
    }

    protected function assertQualityStockAvailable(?int $sellQualityId, float $weightGrams, int $pieces): void
    {
        if (!$sellQualityId) {
            throw new RuntimeException('Item type (quality) is required before stock can be deducted.');
        }

        $balance = $this->getQualityBalance($sellQualityId);
        $name = $balance['quality_name'];

        if ($balance['weight_grams'] <= 0) {
            throw new RuntimeException(
                'No stock for "' . $name . '". Purchase this item type first — you cannot sell a type you do not have in stock.'
            );
        }

        if ($weightGrams > $balance['weight_grams'] + 0.0005) {
            throw new RuntimeException(
                'Insufficient "' . $name . '" stock. Available: '
                . number_format($balance['weight_grams'], 3) . 'g, requested: '
                . number_format($weightGrams, 3) . 'g.'
            );
        }

        if ($pieces > 0 && $pieces > $balance['pieces']) {
            throw new RuntimeException(
                'Insufficient "' . $name . '" pieces. Available: '
                . $balance['pieces'] . ', requested: ' . $pieces . '.'
            );
        }
    }
}
