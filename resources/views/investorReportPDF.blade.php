<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle }} - {{ $investor->investor_name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1, h2 { margin: 0 0 8px; }
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
        <h1>Asif Jewelry</h1>
        <h2>{{ $reportTitle }}</h2>
    </div>

    <div class="meta">
        <p><strong>Investor:</strong> {{ $investor->investor_name }}</p>
        <p><strong>Period:</strong> {{ \Carbon\Carbon::parse($period['from_date'])->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($period['to_date'])->format('d-m-Y') }}</p>
        <p><strong>Profit Share:</strong> {{ number_format($profitSummary['share_percentage'], 2) }}%</p>
    </div>

    <table class="summary-grid">
        <tr>
            <td><strong>Gross Profit (sales)</strong></td>
            <td class="text-right">₹{{ number_format($profitSummary['gross_profit'], 2) }}</td>
            <td><strong>Total Expenses</strong></td>
            <td class="text-right">₹{{ number_format($profitSummary['total_expenses'], 2) }}</td>
        </tr>
        <tr>
            <td><strong>Net Profit</strong></td>
            <td class="text-right">₹{{ number_format($profitSummary['net_profit'], 2) }}</td>
            <td><strong>Your Share</strong></td>
            <td class="text-right">₹{{ number_format($profitSummary['investor_share'], 2) }}</td>
        </tr>
        <tr>
            <td><strong>Total Invested</strong></td>
            <td class="text-right">₹{{ number_format($profitSummary['total_invested'], 2) }}</td>
            <td><strong>Gold / Silver Held</strong></td>
            <td class="text-right">{{ number_format($goldHoldings['gold'], 3) }}g / {{ number_format($goldHoldings['silver'], 3) }}g</td>
        </tr>
    </table>

    <h3>Transactions in Period</h3>
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
                    <td class="text-right">₹{{ number_format($txn->amount, 2) }}</td>
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
