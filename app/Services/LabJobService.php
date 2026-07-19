<?php

namespace App\Services;

use App\Models\tbl_app_setting;
use App\Models\tbl_investor;
use App\Models\tbl_investor_transaction;
use App\Models\tbl_lab_job;
use App\Models\tbl_lab_job_investor;
use InvalidArgumentException;

class LabJobService
{
    public const SPLIT_INVESTMENT = 'investment';
    public const SPLIT_CUSTOM = 'custom';

    protected InvestorProfitService $profitService;

    public function __construct(InvestorProfitService $profitService)
    {
        $this->profitService = $profitService;
    }

    public function finalizeJobFinancing(
        tbl_lab_job $job,
        array $investorIds,
        float $basePrice,
        ?float $profitAmount,
        ?int $userId
    ): void {
        $this->reverseJobTransactions($job->lab_job_id);
        $this->syncJobInvestors($job, $investorIds, $basePrice, $profitAmount);
        $this->applyPurchaseDeductions($job, $investorIds, $basePrice, $userId);

        if ($job->sold_amount !== null) {
            $this->applyCapitalReturns($job, $investorIds, $userId);
        }

        if ($profitAmount !== null && $profitAmount > 0) {
            $this->applyProfitCredits($job, $investorIds, $profitAmount, $userId);
        }
    }

    public function syncJobInvestors(
        tbl_lab_job $job,
        array $investorIds,
        float $basePrice,
        ?float $profitAmount = null
    ): void {
        $investorIds = array_values(array_unique(array_map('intval', $investorIds)));
        $count = count($investorIds);

        if ($count === 0) {
            throw new InvalidArgumentException('Select at least one investor for this laboratory job.');
        }

        $activeCount = tbl_investor::whereIn('investor_id', $investorIds)
            ->where('investor_status', true)
            ->count();

        if ($activeCount !== $count) {
            throw new InvalidArgumentException('One or more selected investors are invalid or inactive.');
        }

        $shopPct = tbl_app_setting::shopProfitPercentage();
        $allocations = $this->buildAllocations($investorIds, $basePrice, $profitAmount, $shopPct);

        foreach ($allocations['participants'] as $row) {
            if ($basePrice > 0 && $row['available'] < $row['purchase_share'] - 0.005) {
                throw new InvalidArgumentException(
                    'Investor "' . $row['investor_name'] . '" does not have enough balance. Required: Rs. '
                    . number_format($row['purchase_share'], 2) . ', available: Rs. '
                    . number_format($row['available'], 2) . '.'
                );
            }
        }

        tbl_lab_job_investor::where('lab_job_id', $job->lab_job_id)->delete();

        foreach ($allocations['participants'] as $row) {
            tbl_lab_job_investor::create([
                'lab_job_id' => $job->lab_job_id,
                'investor_id' => $row['investor_id'],
                'investment_basis' => $row['purchase_share'],
                'share_percentage' => $row['share_percentage'],
                'profit_share' => $row['profit_share'],
            ]);
        }

        $job->investor_id = $investorIds[0];
        $job->split_mode = $allocations['split_mode'];
        $job->shop_profit_percentage = $shopPct;
        $job->shop_profit_amount = $allocations['shop_profit_amount'];
        $job->save();
    }

    public function reverseJobTransactions(int $labJobId): void
    {
        tbl_investor_transaction::where('lab_job_id', $labJobId)
            ->where('transaction_status', true)
            ->whereIn('transaction_type', ['lab_purchase', 'lab_profit', 'lab_sale_return'])
            ->update(['transaction_status' => false]);
    }

    protected function applyPurchaseDeductions(
        tbl_lab_job $job,
        array $investorIds,
        float $basePrice,
        ?int $userId
    ): void {
        if ($basePrice <= 0) {
            return;
        }

        $reference = $job->job_reference ?: ('#' . $job->lab_job_id);
        $participants = tbl_lab_job_investor::where('lab_job_id', $job->lab_job_id)
            ->whereIn('investor_id', $investorIds)
            ->get(['investor_id', 'investment_basis']);

        foreach ($participants as $participant) {
            $amount = round((float) $participant->investment_basis, 2);
            if ($amount <= 0) {
                continue;
            }

            tbl_investor_transaction::create([
                'investor_id' => $participant->investor_id,
                'lab_job_id' => $job->lab_job_id,
                'transaction_date' => $job->job_date,
                'transaction_type' => 'lab_purchase',
                'metal_type' => $job->metal_type,
                'amount' => $amount,
                'notes' => 'Lab job purchase share (' . $reference . ')',
                'created_by' => $userId,
                'transaction_status' => true,
            ]);
        }
    }

    protected function applyCapitalReturns(
        tbl_lab_job $job,
        array $investorIds,
        ?int $userId
    ): void {
        $reference = $job->job_reference ?: ('#' . $job->lab_job_id);
        $participants = tbl_lab_job_investor::where('lab_job_id', $job->lab_job_id)
            ->whereIn('investor_id', $investorIds)
            ->get(['investor_id', 'investment_basis']);

        foreach ($participants as $participant) {
            $amount = round((float) $participant->investment_basis, 2);
            if ($amount <= 0) {
                continue;
            }

            tbl_investor_transaction::create([
                'investor_id' => $participant->investor_id,
                'lab_job_id' => $job->lab_job_id,
                'transaction_date' => $job->job_date,
                'transaction_type' => 'lab_sale_return',
                'metal_type' => $job->metal_type,
                'amount' => $amount,
                'notes' => 'Lab job capital returned (' . $reference . ')',
                'created_by' => $userId,
                'transaction_status' => true,
            ]);
        }
    }

    protected function applyProfitCredits(
        tbl_lab_job $job,
        array $investorIds,
        float $profitAmount,
        ?int $userId
    ): void {
        $reference = $job->job_reference ?: ('#' . $job->lab_job_id);
        $participants = tbl_lab_job_investor::where('lab_job_id', $job->lab_job_id)
            ->whereIn('investor_id', $investorIds)
            ->get(['investor_id', 'share_percentage', 'profit_share']);

        foreach ($participants as $participant) {
            $profitShare = $participant->profit_share !== null
                ? round((float) $participant->profit_share, 2)
                : round($profitAmount * ((float) $participant->share_percentage / 100), 2);

            if ($profitShare <= 0) {
                continue;
            }

            tbl_investor_transaction::create([
                'investor_id' => $participant->investor_id,
                'lab_job_id' => $job->lab_job_id,
                'transaction_date' => $job->job_date,
                'transaction_type' => 'lab_profit',
                'metal_type' => $job->metal_type,
                'amount' => $profitShare,
                'notes' => 'Lab job profit share ('
                    . round((float) $participant->share_percentage, 2) . '%, ' . $reference . ')',
                'created_by' => $userId,
                'transaction_status' => true,
            ]);
        }
    }

    public function previewInvestorShares(
        array $investorIds,
        ?float $basePrice = null,
        ?float $profitAmount = null
    ): array {
        $investorIds = array_values(array_unique(array_map('intval', $investorIds)));
        $shopPct = tbl_app_setting::shopProfitPercentage();

        if (count($investorIds) === 0) {
            return $this->emptyPreview($shopPct);
        }

        $allocations = $this->buildAllocations(
            $investorIds,
            $basePrice ?? 0,
            $profitAmount,
            $shopPct,
            false
        );

        $rows = [];
        $totalBalance = 0.0;
        $totalPurchase = 0.0;
        $totalPct = 0.0;

        foreach ($allocations['participants'] as $row) {
            $totalBalance += $row['available'];
            $totalPurchase += (float) ($row['purchase_share'] ?? 0);
            $totalPct += $row['share_percentage'];

            $rows[] = [
                'investor_id' => $row['investor_id'],
                'investor_name' => $row['investor_name'],
                'investment_basis' => $row['available'],
                'purchase_share' => $row['purchase_share'],
                'share_percentage' => $row['share_percentage'],
                'profit_share' => $row['profit_share'],
                'profit_split_mode' => $row['profit_split_mode'],
            ];
        }

        $investorPoolPct = round(max(100 - $shopPct, 0), 2);

        return [
            'participants' => $rows,
            'total_investment_basis' => round($totalBalance, 2),
            'total_purchase_share' => round($totalPurchase, 2),
            'total_profit_share_percentage' => round($totalPct, 2),
            'unallocated_profit_percentage' => round(max($investorPoolPct - $totalPct, 0), 2),
            'split_mode' => $allocations['split_mode'],
            'shop_profit_percentage' => $shopPct,
            'shop_profit_amount' => $allocations['shop_profit_amount'],
            'investor_pool_percentage' => $investorPoolPct,
            'investor_pool_amount' => $allocations['investor_pool_amount'],
        ];
    }

    protected function emptyPreview(float $shopPct): array
    {
        return [
            'participants' => [],
            'total_investment_basis' => 0,
            'total_profit_share_percentage' => 0,
            'unallocated_profit_percentage' => round(max(100 - $shopPct, 0), 2),
            'split_mode' => self::SPLIT_INVESTMENT,
            'shop_profit_percentage' => $shopPct,
            'shop_profit_amount' => null,
            'investor_pool_percentage' => round(max(100 - $shopPct, 0), 2),
            'investor_pool_amount' => null,
        ];
    }

    /**
     * Shop takes shop%, then remaining pool is split per investor:
     * - custom: uses that investor's Profit % of the investor pool
     * - investment: remaining pool (after custom cuts) split by balances
     *
     * @return array{
     *   participants: array<int, array>,
     *   shop_profit_amount:?float,
     *   investor_pool_amount:?float,
     *   split_mode:string
     * }
     */
    protected function buildAllocations(
        array $investorIds,
        float $basePrice,
        ?float $profitAmount,
        float $shopPct,
        bool $requireActive = true
    ): array {
        $shopPct = max(0, min(100, round($shopPct, 2)));
        $investorPoolPct = round(max(100 - $shopPct, 0), 4);

        $shopProfitAmount = null;
        $investorPoolAmount = null;
        if ($profitAmount !== null) {
            $shopProfitAmount = round($profitAmount * ($shopPct / 100), 2);
            $investorPoolAmount = round($profitAmount - $shopProfitAmount, 2);
        }

        $customRows = [];
        $investmentRows = [];
        $hasCustom = false;
        $hasInvestment = false;

        foreach ($investorIds as $investorId) {
            $investor = $this->findInvestor((int) $investorId, $requireActive);
            if (!$investor) {
                continue;
            }

            $available = round(max($this->profitService->getTotalInvested((int) $investorId), 0), 2);
            $row = [
                'investor_id' => (int) $investorId,
                'investor_name' => $investor->investor_name,
                'available' => $available,
                'profit_split_mode' => $investor->usesCustomProfitSplit() ? self::SPLIT_CUSTOM : self::SPLIT_INVESTMENT,
                'custom_pct' => round((float) $investor->profit_share_percentage, 4),
            ];

            if ($row['profit_split_mode'] === self::SPLIT_CUSTOM) {
                $hasCustom = true;
                $customRows[] = $row;
            } else {
                $hasInvestment = true;
                $investmentRows[] = $row;
            }
        }

        $customPctSum = round(array_sum(array_column($customRows, 'custom_pct')), 4);
        if ($customPctSum > 100.001) {
            throw new InvalidArgumentException(
                'Custom investor Profit % total cannot exceed 100% of the investor pool. Current total: '
                . number_format($customPctSum, 2) . '%.'
            );
        }

        $participants = [];
        $assignedCustomPoolPct = 0.0;
        $assignedCustomPurchase = 0.0;
        $assignedCustomProfit = 0.0;

        foreach ($customRows as $row) {
            $ratio = $row['custom_pct'] / 100;
            $shareOfTotal = round($investorPoolPct * $ratio, 4);
            $purchaseShare = $basePrice > 0 ? round($basePrice * $ratio, 2) : 0.0;
            $profitShare = $investorPoolAmount !== null
                ? round($investorPoolAmount * $ratio, 2)
                : null;

            $assignedCustomPoolPct += $shareOfTotal;
            $assignedCustomPurchase += $purchaseShare;
            if ($profitShare !== null) {
                $assignedCustomProfit += $profitShare;
            }

            $participants[] = [
                'investor_id' => $row['investor_id'],
                'investor_name' => $row['investor_name'],
                'available' => $row['available'],
                'purchase_share' => $purchaseShare,
                'share_percentage' => $shareOfTotal,
                'profit_share' => $profitShare,
                'profit_split_mode' => self::SPLIT_CUSTOM,
            ];
        }

        $remainingPoolPct = round(max($investorPoolPct - $assignedCustomPoolPct, 0), 4);
        $remainingPurchase = $basePrice > 0 ? round(max($basePrice - $assignedCustomPurchase, 0), 2) : 0.0;
        $remainingProfit = $investorPoolAmount !== null
            ? round(max($investorPoolAmount - $assignedCustomProfit, 0), 2)
            : null;

        if ($investmentRows) {
            $totalBalance = array_sum(array_column($investmentRows, 'available'));
            if ($remainingPoolPct > 0.0001 && $totalBalance <= 0) {
                throw new InvalidArgumentException(
                    'Investment-mode investors have no balance to receive the remaining profit pool. Add capital or change their profit setting to Custom %.'
                );
            }

            $assignedPoolPct = 0.0;
            $assignedPurchase = 0.0;
            $assignedProfit = 0.0;
            $lastIndex = count($investmentRows) - 1;

            foreach ($investmentRows as $index => $row) {
                $isLast = $index === $lastIndex;

                if ($totalBalance <= 0) {
                    $poolSharePct = 0.0;
                    $purchaseShare = 0.0;
                    $profitShare = $remainingProfit !== null ? 0.0 : null;
                } elseif ($isLast) {
                    $poolSharePct = round($remainingPoolPct - $assignedPoolPct, 4);
                    $purchaseShare = $remainingPurchase > 0
                        ? round($remainingPurchase - $assignedPurchase, 2)
                        : 0.0;
                    $profitShare = $remainingProfit !== null
                        ? round($remainingProfit - $assignedProfit, 2)
                        : null;
                } else {
                    $ratio = $row['available'] / $totalBalance;
                    $poolSharePct = round($remainingPoolPct * $ratio, 4);
                    $purchaseShare = $remainingPurchase > 0
                        ? round($remainingPurchase * $ratio, 2)
                        : 0.0;
                    $profitShare = $remainingProfit !== null
                        ? round($remainingProfit * $ratio, 2)
                        : null;
                    $assignedPoolPct += $poolSharePct;
                    $assignedPurchase += $purchaseShare;
                    if ($profitShare !== null) {
                        $assignedProfit += $profitShare;
                    }
                }

                $participants[] = [
                    'investor_id' => $row['investor_id'],
                    'investor_name' => $row['investor_name'],
                    'available' => $row['available'],
                    'purchase_share' => $purchaseShare,
                    'share_percentage' => $poolSharePct,
                    'profit_share' => $profitShare,
                    'profit_split_mode' => self::SPLIT_INVESTMENT,
                ];
            }
        }

        $splitMode = self::SPLIT_INVESTMENT;
        if ($hasCustom && $hasInvestment) {
            $splitMode = 'mixed';
        } elseif ($hasCustom) {
            $splitMode = self::SPLIT_CUSTOM;
        }

        // Keep participant order aligned with selected investor ids.
        $order = array_flip(array_map('intval', $investorIds));
        usort($participants, function ($a, $b) use ($order) {
            return ($order[$a['investor_id']] ?? 0) <=> ($order[$b['investor_id']] ?? 0);
        });

        return [
            'participants' => $participants,
            'shop_profit_amount' => $shopProfitAmount,
            'investor_pool_amount' => $investorPoolAmount,
            'split_mode' => $splitMode,
        ];
    }

    protected function findInvestor(int $investorId, bool $requireActive = true): ?tbl_investor
    {
        $query = tbl_investor::where('investor_id', $investorId);
        if ($requireActive) {
            $query->where('investor_status', true);
        }

        return $query->first();
    }
}
