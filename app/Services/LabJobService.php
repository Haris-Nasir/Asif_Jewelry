<?php

namespace App\Services;

use App\Models\tbl_investor;
use App\Models\tbl_investor_transaction;
use App\Models\tbl_lab_job;
use App\Models\tbl_lab_job_investor;
use InvalidArgumentException;

class LabJobService
{
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

        $purchaseShare = round($basePrice / $count, 2);

        foreach ($investorIds as $investorId) {
            $available = $this->profitService->getTotalInvested($investorId);
            if ($available < $purchaseShare - 0.005) {
                $name = tbl_investor::where('investor_id', $investorId)->value('investor_name') ?: ('#' . $investorId);
                throw new InvalidArgumentException(
                    'Investor "' . $name . '" does not have enough balance. Required: Rs. '
                    . number_format($purchaseShare, 2) . ', available: Rs. ' . number_format($available, 2) . '.'
                );
            }
        }

        tbl_lab_job_investor::where('lab_job_id', $job->lab_job_id)->delete();

        foreach ($investorIds as $investorId) {
            $investor = tbl_investor::where('investor_id', $investorId)->firstOrFail();
            $sharePercentage = (float) $investor->profit_share_percentage;
            $profitShare = $profitAmount !== null
                ? round($profitAmount * ($sharePercentage / 100), 2)
                : null;

            tbl_lab_job_investor::create([
                'lab_job_id' => $job->lab_job_id,
                'investor_id' => $investorId,
                'investment_basis' => $purchaseShare,
                'share_percentage' => round($sharePercentage, 4),
                'profit_share' => $profitShare,
            ]);
        }

        $job->investor_id = $investorIds[0];
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

        $purchaseShare = round($basePrice / count($investorIds), 2);
        $reference = $job->job_reference ?: ('#' . $job->lab_job_id);

        foreach ($investorIds as $investorId) {
            tbl_investor_transaction::create([
                'investor_id' => $investorId,
                'lab_job_id' => $job->lab_job_id,
                'transaction_date' => $job->job_date,
                'transaction_type' => 'lab_purchase',
                'metal_type' => $job->metal_type,
                'amount' => $purchaseShare,
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

        foreach ($investorIds as $investorId) {
            $investor = tbl_investor::where('investor_id', $investorId)->firstOrFail();
            $sharePercentage = (float) $investor->profit_share_percentage;
            $profitShare = round($profitAmount * ($sharePercentage / 100), 2);

            if ($profitShare <= 0) {
                continue;
            }

            tbl_investor_transaction::create([
                'investor_id' => $investorId,
                'lab_job_id' => $job->lab_job_id,
                'transaction_date' => $job->job_date,
                'transaction_type' => 'lab_profit',
                'metal_type' => $job->metal_type,
                'amount' => $profitShare,
                'notes' => 'Lab job profit share (' . $sharePercentage . '%, ' . $reference . ')',
                'created_by' => $userId,
                'transaction_status' => true,
            ]);
        }
    }

    public function previewInvestorShares(array $investorIds, ?float $basePrice = null, ?float $profitAmount = null): array
    {
        $investorIds = array_values(array_unique(array_map('intval', $investorIds)));
        $count = count($investorIds);
        $rows = [];

        if ($count === 0) {
            return [
                'participants' => [],
                'total_investment_basis' => 0,
                'total_profit_share_percentage' => 0,
                'unallocated_profit_percentage' => 100,
            ];
        }

        $purchaseShare = $basePrice !== null ? round($basePrice / $count, 2) : null;
        $totalProfitSharePercentage = 0.0;

        foreach ($investorIds as $investorId) {
            $investor = tbl_investor::where('investor_id', $investorId)
                ->where('investor_status', true)
                ->first();

            if (!$investor) {
                continue;
            }

            $sharePercentage = (float) $investor->profit_share_percentage;
            $totalProfitSharePercentage += $sharePercentage;
            $profitShare = ($profitAmount !== null && $sharePercentage > 0)
                ? round($profitAmount * ($sharePercentage / 100), 2)
                : null;

            $rows[] = [
                'investor_id' => $investorId,
                'investor_name' => $investor->investor_name,
                'investment_basis' => round($this->profitService->getTotalInvested($investorId), 2),
                'purchase_share' => $purchaseShare,
                'share_percentage' => round($sharePercentage, 2),
                'profit_share' => $profitShare,
            ];
        }

        return [
            'participants' => $rows,
            'total_investment_basis' => $purchaseShare !== null ? round($purchaseShare * $count, 2) : 0,
            'total_profit_share_percentage' => round($totalProfitSharePercentage, 2),
            'unallocated_profit_percentage' => round(max(100 - $totalProfitSharePercentage, 0), 2),
        ];
    }
}
