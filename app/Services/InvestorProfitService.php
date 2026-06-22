<?php

namespace App\Services;

use App\Models\tbl_expense;
use App\Models\tbl_investor;
use App\Models\tbl_investor_transaction;
use App\Models\tbl_invoice_mst;
use Carbon\Carbon;
use InvalidArgumentException;

class InvestorProfitService
{
    public function resolvePeriodBounds(string $period, ?string $date = null): array
    {
        $period = strtolower($period);
        $anchor = $date ? Carbon::parse($date) : Carbon::today();

        switch ($period) {
            case 'daily':
                return [
                    'type' => 'daily',
                    'from_date' => $anchor->toDateString(),
                    'to_date' => $anchor->toDateString(),
                ];

            case 'monthly':
                return [
                    'type' => 'monthly',
                    'from_date' => $anchor->copy()->startOfMonth()->toDateString(),
                    'to_date' => $anchor->copy()->endOfMonth()->toDateString(),
                ];

            case 'quarterly':
                $month = (int) $anchor->format('n');
                $year = (int) $anchor->format('Y');

                if ($month >= 4 && $month <= 6) {
                    $from = Carbon::create($year, 4, 1);
                    $to = Carbon::create($year, 6, 30);
                } elseif ($month >= 7 && $month <= 9) {
                    $from = Carbon::create($year, 7, 1);
                    $to = Carbon::create($year, 9, 30);
                } elseif ($month >= 10 && $month <= 12) {
                    $from = Carbon::create($year, 10, 1);
                    $to = Carbon::create($year, 12, 31);
                } else {
                    $from = Carbon::create($year, 1, 1);
                    $to = Carbon::create($year, 3, 31);
                }

                return [
                    'type' => 'quarterly',
                    'from_date' => $from->toDateString(),
                    'to_date' => $to->toDateString(),
                ];

            case 'year':
            case 'financial_year':
                $fy = $this->getFinancialYearBounds($anchor->toDateString());
                return [
                    'type' => 'financial_year',
                    'from_date' => $fy['from_date'],
                    'to_date' => $fy['to_date'],
                ];

            default:
                throw new InvalidArgumentException('Invalid period. Use daily, monthly, quarterly, or financial_year.');
        }
    }

    public function getFinancialYearBounds(string $date): array
    {
        $anchor = Carbon::parse($date);
        $year = (int) $anchor->format('Y');
        $month = (int) $anchor->format('n');

        if ($month < 4) {
            $fromYear = $year - 1;
            $toYear = $year;
        } else {
            $fromYear = $year;
            $toYear = $year + 1;
        }

        return [
            'from_date' => $fromYear . '-04-01',
            'to_date' => $toYear . '-03-31',
        ];
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

    public function getTotalInvested(int $investorId): float
    {
        $transactions = tbl_investor_transaction::where('investor_id', $investorId)
            ->where('transaction_status', true)
            ->get(['transaction_type', 'amount']);

        $total = 0;
        foreach ($transactions as $transaction) {
            if (in_array($transaction->transaction_type, ['deposit', 'gold_buy'], true)) {
                $total += (float) $transaction->amount;
            } elseif (in_array($transaction->transaction_type, ['withdrawal', 'gold_sell'], true)) {
                $total -= (float) $transaction->amount;
            }
        }

        return round(max($total, 0), 2);
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

    public function buildSummary(tbl_investor $investor, string $period, ?string $date = null): array
    {
        $bounds = $this->resolvePeriodBounds($period, $date);
        $grossProfit = $this->getGrossProfit($bounds['from_date'], $bounds['to_date']);
        $totalExpenses = $this->getTotalExpenses($bounds['from_date'], $bounds['to_date']);
        $netProfit = round($grossProfit - $totalExpenses, 2);
        $sharePercentage = (float) $investor->profit_share_percentage;
        $investorShare = $this->getInvestorShare($netProfit, $sharePercentage);

        return [
            'period' => $bounds,
            'profit_summary' => [
                'gross_profit' => round($grossProfit, 2),
                'total_expenses' => round($totalExpenses, 2),
                'net_profit' => $netProfit,
                'share_percentage' => $sharePercentage,
                'investor_share' => $investorShare,
                'total_invested' => $this->getTotalInvested($investor->investor_id),
            ],
            'gold_holdings' => $this->getGoldHoldings($investor->investor_id),
        ];
    }
}
