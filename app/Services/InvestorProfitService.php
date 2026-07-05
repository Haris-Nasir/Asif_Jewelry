<?php

namespace App\Services;

use App\Models\tbl_expense;
use App\Models\tbl_investor;
use App\Models\tbl_investor_expense_allocation;
use App\Models\tbl_investor_transaction;
use App\Models\tbl_invoice_mst;
use App\Models\tbl_lab_job;
use App\Models\tbl_lab_job_investor;
use Carbon\Carbon;
use InvalidArgumentException;

class InvestorProfitService
{
    public function resolvePeriodBounds(
        string $period,
        ?string $date = null,
        ?string $fromDate = null,
        ?string $toDate = null
    ): array {
        $period = strtolower($period);
        $anchor = $date ? Carbon::parse($date) : Carbon::today();

        switch ($period) {
            case 'daily':
                return [
                    'type' => 'daily',
                    'from_date' => $anchor->toDateString(),
                    'to_date' => $anchor->toDateString(),
                ];

            case 'weekly':
                return [
                    'type' => 'weekly',
                    'from_date' => $anchor->copy()->startOfWeek(Carbon::MONDAY)->toDateString(),
                    'to_date' => $anchor->copy()->endOfWeek(Carbon::SUNDAY)->toDateString(),
                ];

            case 'monthly':
                return [
                    'type' => 'monthly',
                    'from_date' => $anchor->copy()->startOfMonth()->toDateString(),
                    'to_date' => $anchor->copy()->endOfMonth()->toDateString(),
                ];

            case 'custom':
                if (!$fromDate || !$toDate) {
                    throw new InvalidArgumentException('Custom period requires from_date and to_date.');
                }

                $from = Carbon::parse($fromDate);
                $to = Carbon::parse($toDate);

                if ($from->gt($to)) {
                    throw new InvalidArgumentException('Start date must be on or before end date.');
                }

                return [
                    'type' => 'custom',
                    'from_date' => $from->toDateString(),
                    'to_date' => $to->toDateString(),
                ];

            default:
                throw new InvalidArgumentException('Invalid period. Use daily, weekly, monthly, or custom.');
        }
    }

    public function getGrossProfit(string $fromDate, string $toDate): float
    {
        return (float) tbl_invoice_mst::whereBetween('invoice_date', [$fromDate, $toDate])
            ->where('invoice_mst_status', true)
            ->sum('profit_amount');
    }

    public function getTotalExpenses(string $fromDate, string $toDate): float
    {
        return (float) tbl_expense::whereBetween('expense_date', [$fromDate, $toDate])
            ->where('expense_status', true)
            ->sum('expense_amount');
    }

    public function getNetProfit(string $fromDate, string $toDate): float
    {
        return round($this->getGrossProfit($fromDate, $toDate) - $this->getTotalExpenses($fromDate, $toDate), 2);
    }

    public function getInvestorShare(float $netProfit, float $sharePercentage): float
    {
        return round($netProfit * ($sharePercentage / 100), 2);
    }

    public function getInvestorAllocatedExpenses(int $investorId, string $fromDate, string $toDate): float
    {
        return (float) tbl_investor_expense_allocation::where('investor_id', $investorId)
            ->where('allocation_status', true)
            ->whereBetween('allocation_date', [$fromDate, $toDate])
            ->sum('allocated_amount');
    }

    public function getInvestorExpenseAllocations(int $investorId, string $fromDate, string $toDate)
    {
        return tbl_investor_expense_allocation::where('investor_id', $investorId)
            ->where('allocation_status', true)
            ->whereBetween('allocation_date', [$fromDate, $toDate])
            ->orderByDesc('allocation_date')
            ->orderByDesc('investor_expense_allocation_id')
            ->get([
                'investor_expense_allocation_id',
                'allocation_date',
                'description',
                'allocated_amount',
                'notes',
                'expense_id',
            ]);
    }

    public function getTotalInvested(int $investorId): float
    {
        return round($this->getInvestmentSummary($investorId)['current_balance'], 2);
    }

    public function getInvestmentSummary(int $investorId): array
    {
        $transactions = tbl_investor_transaction::where('investor_id', $investorId)
            ->where('transaction_status', true)
            ->get(['transaction_type', 'amount']);

        $totalDeposited = 0.0;
        $labPurchases = 0.0;
        $labCapitalReturned = 0.0;
        $labProfit = 0.0;
        $totalPaidOut = 0.0;

        foreach ($transactions as $transaction) {
            $amount = (float) $transaction->amount;

            switch ($transaction->transaction_type) {
                case 'deposit':
                case 'gold_buy':
                    $totalDeposited += $amount;
                    break;
                case 'lab_purchase':
                    $labPurchases += $amount;
                    break;
                case 'lab_sale_return':
                    $labCapitalReturned += $amount;
                    break;
                case 'lab_profit':
                    $labProfit += $amount;
                    break;
                case 'withdrawal':
                case 'gold_sell':
                    $totalPaidOut += $amount;
                    break;
            }
        }

        $capitalInOpenJobs = round(max($labPurchases - $labCapitalReturned, 0), 2);
        $expensesAssigned = round($this->getInvestorTotalAllocatedExpenses($investorId), 2);
        $currentBalance = round(
            $totalDeposited - $capitalInOpenJobs + $labProfit - $expensesAssigned - $totalPaidOut,
            2
        );

        return [
            'total_deposited' => round($totalDeposited, 2),
            'lab_purchases' => round($labPurchases, 2),
            'lab_capital_returned' => round($labCapitalReturned, 2),
            'capital_in_open_jobs' => $capitalInOpenJobs,
            'lab_profit_earned' => round($labProfit, 2),
            'expenses_assigned' => $expensesAssigned,
            'net_lab_profit' => round($labProfit - $expensesAssigned, 2),
            'total_paid_out' => round($totalPaidOut, 2),
            'current_balance' => max($currentBalance, 0),
            'total_invested' => max($currentBalance, 0),
        ];
    }

    public function getInvestorTotalAllocatedExpenses(int $investorId): float
    {
        return (float) tbl_investor_expense_allocation::where('investor_id', $investorId)
            ->where('allocation_status', true)
            ->sum('allocated_amount');
    }

    public function getGoldHoldings(int $investorId): array
    {
        $transactions = tbl_investor_transaction::where('investor_id', $investorId)
            ->where('transaction_status', true)
            ->whereIn('transaction_type', ['gold_buy', 'gold_sell'])
            ->get(['transaction_type', 'metal_type', 'weight_grams']);

        $holdings = ['gold' => 0.0, 'silver' => 0.0];

        foreach ($transactions as $transaction) {
            if (!$transaction->metal_type) {
                continue;
            }

            $weight = (float) $transaction->weight_grams;
            if ($transaction->transaction_type === 'gold_buy') {
                $holdings[$transaction->metal_type] += $weight;
            } else {
                $holdings[$transaction->metal_type] -= $weight;
            }
        }

        return [
            'gold' => round(max($holdings['gold'], 0), 3),
            'silver' => round(max($holdings['silver'], 0), 3),
        ];
    }

    public function getLabSummary(int $investorId, string $fromDate, string $toDate): array
    {
        $jobIds = tbl_lab_job::query()
            ->where('lab_job_status', true)
            ->whereBetween('job_date', [$fromDate, $toDate])
            ->whereHas('jobInvestors', function ($q) use ($investorId) {
                $q->where('investor_id', $investorId);
            })
            ->pluck('lab_job_id');

        $jobs = tbl_lab_job::whereIn('lab_job_id', $jobIds)->get();
        $soldJobs = $jobs->where('job_status', 'sold');
        $soldJobIds = $soldJobs->pluck('lab_job_id');

        $investorProfit = (float) tbl_lab_job_investor::query()
            ->where('investor_id', $investorId)
            ->whereIn('lab_job_id', $soldJobIds)
            ->sum('profit_share');

        return [
            'total_jobs' => $jobs->count(),
            'open_jobs' => $jobs->where('job_status', 'open')->count(),
            'sold_jobs' => $soldJobs->count(),
            'total_weight_grams' => round($jobs->sum('weight_grams'), 3),
            'total_lab_profit' => round($investorProfit, 2),
            'profit_share_mode' => 'configured',
        ];
    }

    public function buildPortalSummary(
        tbl_investor $investor,
        string $period,
        ?string $date = null,
        ?string $fromDate = null,
        ?string $toDate = null
    ): array {
        $bounds = $this->resolvePeriodBounds($period, $date, $fromDate, $toDate);
        $labSummary = $this->getLabSummary(
            $investor->investor_id,
            $bounds['from_date'],
            $bounds['to_date']
        );
        $allocatedExpenses = $this->getInvestorAllocatedExpenses(
            $investor->investor_id,
            $bounds['from_date'],
            $bounds['to_date']
        );

        $labSummary['share_percentage'] = (float) $investor->profit_share_percentage;
        $labSummary['profit_share_mode'] = 'configured';
        $labSummary['investor_lab_share'] = $labSummary['total_lab_profit'];
        $labSummary['allocated_expenses'] = round($allocatedExpenses, 2);
        $labSummary['net_lab_profit'] = round($labSummary['total_lab_profit'] - $allocatedExpenses, 2);

        return [
            'period' => $bounds,
            'investment_summary' => $this->getInvestmentSummary($investor->investor_id),
            'gold_holdings' => $this->getGoldHoldings($investor->investor_id),
            'lab_summary' => $labSummary,
            'expense_allocations' => $this->getInvestorExpenseAllocations(
                $investor->investor_id,
                $bounds['from_date'],
                $bounds['to_date']
            ),
        ];
    }

    public function buildSummary(
        tbl_investor $investor,
        string $period,
        ?string $date = null,
        ?string $fromDate = null,
        ?string $toDate = null
    ): array {
        $bounds = $this->resolvePeriodBounds($period, $date, $fromDate, $toDate);
        $grossProfit = $this->getGrossProfit($bounds['from_date'], $bounds['to_date']);
        $sharePercentage = (float) $investor->profit_share_percentage;
        $grossShare = $this->getInvestorShare($grossProfit, $sharePercentage);
        $allocatedExpenses = $this->getInvestorAllocatedExpenses(
            $investor->investor_id,
            $bounds['from_date'],
            $bounds['to_date']
        );
        $investorShare = round($grossShare - $allocatedExpenses, 2);

        return [
            'period' => $bounds,
            'profit_summary' => [
                'gross_profit' => round($grossProfit, 2),
                'gross_share' => $grossShare,
                'allocated_expenses' => round($allocatedExpenses, 2),
                'total_expenses' => round($allocatedExpenses, 2),
                'share_percentage' => $sharePercentage,
                'investor_share' => $investorShare,
                'total_invested' => $this->getTotalInvested($investor->investor_id),
            ],
            'expense_allocations' => $this->getInvestorExpenseAllocations(
                $investor->investor_id,
                $bounds['from_date'],
                $bounds['to_date']
            ),
            'gold_holdings' => $this->getGoldHoldings($investor->investor_id),
        ];
    }
}
