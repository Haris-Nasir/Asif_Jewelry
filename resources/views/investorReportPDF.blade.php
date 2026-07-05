<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle }} - {{ $investor->investor_name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1, h2, h3 { margin: 0 0 8px; }
        .header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 16px; }
        .meta { margin-bottom: 16px; }
        .meta p { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; }
        .text-right { text-align: right; }
        .summary-grid { width: 100%; margin: 12px 0; }
        .summary-grid td { border: none; padding: 4px 8px 4px 0; }
        .footer { margin-top: 20px; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('company.name') }}</h1>
        <h2>{{ $reportTitle }}</h2>
    </div>

    <div class="meta">
        <p><strong>Investor:</strong> {{ $investor->investor_name }}</p>
        <p><strong>Your profit share:</strong> {{ number_format($labSummary['share_percentage'], 2) }}% (set in Manage Investors)</p>
        <p><strong>Period:</strong> {{ \Carbon\Carbon::parse($period['from_date'])->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($period['to_date'])->format('d-m-Y') }}</p>
        <p><strong>Scope:</strong> Laboratory investment only (shop sales not included)</p>
    </div>

    <table class="summary-grid">
        <tr>
            <td><strong>Total Deposited</strong></td>
            <td class="text-right">{{ config('currency.code') }} {{ number_format($investmentSummary['total_deposited'], 2) }}</td>
            <td><strong>Used in Lab Jobs</strong></td>
            <td class="text-right">{{ config('currency.code') }} {{ number_format($investmentSummary['lab_purchases'], 2) }}</td>
        </tr>
        <tr>
            <td><strong>Lab Profit Credited</strong></td>
            <td class="text-right">{{ config('currency.code') }} {{ number_format($investmentSummary['lab_profit_earned'], 2) }}</td>
            <td><strong>Expenses Assigned (all time)</strong></td>
            <td class="text-right">{{ config('currency.code') }} {{ number_format($investmentSummary['expenses_assigned'], 2) }}</td>
        </tr>
        <tr>
            <td><strong>Net Lab Profit (all time)</strong></td>
            <td class="text-right">{{ config('currency.code') }} {{ number_format($investmentSummary['net_lab_profit'], 2) }}</td>
            <td><strong>Capital Returned (sold)</strong></td>
            <td class="text-right">{{ config('currency.code') }} {{ number_format($investmentSummary['lab_capital_returned'], 2) }}</td>
        </tr>
        <tr>
            <td><strong>In Open Jobs</strong></td>
            <td class="text-right">{{ config('currency.code') }} {{ number_format($investmentSummary['capital_in_open_jobs'], 2) }}</td>
            <td><strong>Paid Out / Redeemed</strong></td>
            <td class="text-right">{{ config('currency.code') }} {{ number_format($investmentSummary['total_paid_out'], 2) }}</td>
        </tr>
        <tr>
            <td><strong>Balance Payable</strong></td>
            <td class="text-right">{{ config('currency.code') }} {{ number_format($investmentSummary['current_balance'], 2) }}</td>
            <td colspan="2" style="font-size:10px;color:#666;">Deposited − open job capital + profit − expenses − paid out</td>
        </tr>
        <tr>
            <td><strong>Your Lab Profit (period)</strong></td>
            <td class="text-right">{{ config('currency.code') }} {{ number_format($labSummary['total_lab_profit'], 2) }}</td>
            <td><strong>Expenses Assigned (period)</strong></td>
            <td class="text-right">{{ config('currency.code') }} {{ number_format($labSummary['allocated_expenses'], 2) }}</td>
        </tr>
        <tr>
            <td><strong>Net Lab Profit (period)</strong></td>
            <td class="text-right">{{ config('currency.code') }} {{ number_format($labSummary['net_lab_profit'], 2) }}</td>
            <td><strong>Lab Jobs</strong></td>
            <td class="text-right">{{ $labSummary['total_jobs'] }} ({{ $labSummary['open_jobs'] }} open)</td>
        </tr>
        <tr>
            <td><strong>Gold / Silver Held</strong></td>
            <td class="text-right">{{ number_format($goldHoldings['gold'], 3) }}g / {{ number_format($goldHoldings['silver'], 3) }}g</td>
            <td colspan="2"></td>
        </tr>
    </table>

    <h3>Expense Assignments in Period</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th class="text-right">Amount</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenseAllocations as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->allocation_date)->format('d-m-Y') }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ config('currency.code') }} {{ number_format($item->allocated_amount, 2) }}</td>
                    <td>{{ $item->notes ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No expenses assigned in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3>Laboratory Jobs in Period</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Ref</th>
                <th>Metal</th>
                <th class="text-right">Weight (g)</th>
                <th class="text-right">Base</th>
                <th class="text-right">Refinery</th>
                <th class="text-right">Sold</th>
                <th class="text-right">Job Profit</th>
                <th class="text-right">Your %</th>
                <th class="text-right">Your Profit</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($labJobs as $job)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($job->getAttributes()['job_date'] ?? $job->job_date)->format('d-m-Y') }}</td>
                    <td>{{ $job->job_reference ?: '-' }}</td>
                    <td>{{ ucfirst($job->metal_type) }}</td>
                    <td class="text-right">{{ number_format($job->weight_grams, 3) }}</td>
                    <td class="text-right">{{ number_format($job->base_price, 2) }}</td>
                    <td class="text-right">{{ number_format($job->refinery_cost, 2) }}</td>
                    <td class="text-right">{{ $job->sold_amount !== null ? number_format($job->sold_amount, 2) : '-' }}</td>
                    <td class="text-right">{{ $job->profit_amount !== null ? number_format($job->profit_amount, 2) : '-' }}</td>
                    <td class="text-right">{{ number_format($job->my_share_percentage, 2) }}%</td>
                    <td class="text-right">{{ $job->my_profit_share !== null ? number_format($job->my_profit_share, 2) : '-' }}</td>
                    <td>{{ ucfirst($job->job_status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11">No laboratory jobs in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3>Investment Transactions in Period</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Metal</th>
                <th class="text-right">Weight (g)</th>
                <th class="text-right">Rate/g</th>
                <th class="text-right">Amount</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $txn)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($txn->transaction_date)->format('d-m-Y') }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($txn->transaction_type)) }}</td>
                    <td>{{ $txn->metal_type ?: '-' }}</td>
                    <td class="text-right">{{ $txn->weight_grams ? number_format($txn->weight_grams, 3) : '-' }}</td>
                    <td class="text-right">{{ $txn->rate_per_gram ? number_format($txn->rate_per_gram, 2) : '-' }}</td>
                    <td class="text-right">{{ config('currency.code') }} {{ number_format($txn->amount, 2) }}</td>
                    <td>{{ $txn->notes ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No transactions in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ $generatedAt }}
    </div>
</body>
</html>
