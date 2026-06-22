<template>
    <div>
        <aside></aside>
        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Investor Portal</h3>
                                </div>
                                <div class="card-body" v-if="summary">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label class="text-md">Report Period</label>
                                            <select class="form-control" v-model="filters.period" @change="loadSummary">
                                                <option value="daily">Daily</option>
                                                <option value="monthly">Monthly</option>
                                                <option value="quarterly">Quarterly</option>
                                                <option value="financial_year">Financial Year</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="text-md">Reference Date</label>
                                            <input type="date" class="form-control" v-model="filters.date" @change="loadSummary">
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <a :href="pdfUrl('daily')" target="_blank" class="btn btn-danger btn-sm mr-2">
                                                <i class="fas fa-file-pdf"></i> Daily PDF
                                            </a>
                                            <a :href="pdfUrl('monthly')" target="_blank" class="btn btn-danger btn-sm mr-2">
                                                <i class="fas fa-file-pdf"></i> Monthly PDF
                                            </a>
                                            <a :href="pdfUrl('quarterly')" target="_blank" class="btn btn-danger btn-sm">
                                                <i class="fas fa-file-pdf"></i> Quarterly PDF
                                            </a>
                                        </div>
                                    </div>

                                    <p class="mb-1"><strong>Name:</strong> {{ summary.investor.investor_name }}</p>
                                    <p class="mb-3"><strong>Profit Share:</strong> {{ summary.profit_summary.share_percentage }}%</p>
                                    <p class="text-muted mb-3">
                                        Period: {{ summary.period.from_date }} to {{ summary.period.to_date }}
                                    </p>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="small-box bg-info">
                                                <div class="inner">
                                                    <h3>₹{{ formatMoney(summary.profit_summary.total_invested) }}</h3>
                                                    <p>Total Invested</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small-box bg-success">
                                                <div class="inner">
                                                    <h3>₹{{ formatMoney(summary.profit_summary.investor_share) }}</h3>
                                                    <p>Your Share (Period)</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small-box bg-warning">
                                                <div class="inner">
                                                    <h3>₹{{ formatMoney(summary.profit_summary.net_profit) }}</h3>
                                                    <p>Net Profit (All)</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small-box bg-secondary">
                                                <div class="inner">
                                                    <h3>{{ summary.gold_holdings.gold }}g</h3>
                                                    <p>Gold Held ({{ summary.gold_holdings.silver }}g silver)</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <p><strong>Gross Profit:</strong> ₹{{ formatMoney(summary.profit_summary.gross_profit) }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Expenses:</strong> ₹{{ formatMoney(summary.profit_summary.total_expenses) }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Net Profit:</strong> ₹{{ formatMoney(summary.profit_summary.net_profit) }}</p>
                                        </div>
                                    </div>

                                    <h5 class="mt-4">Transactions in Period</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
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
                                                <tr v-for="txn in summary.transactions" :key="txn.investor_transaction_id">
                                                    <td>{{ txn.transaction_date }}</td>
                                                    <td>{{ formatType(txn.transaction_type) }}</td>
                                                    <td>{{ txn.metal_type || '-' }}</td>
                                                    <td class="text-right">{{ txn.weight_grams || '-' }}</td>
                                                    <td class="text-right">{{ txn.rate_per_gram || '-' }}</td>
                                                    <td class="text-right">₹{{ formatMoney(txn.amount) }}</td>
                                                    <td>{{ txn.notes || '-' }}</td>
                                                </tr>
                                                <tr v-if="!summary.transactions.length">
                                                    <td colspan="7" class="text-center text-muted">No transactions in this period.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-body" v-else>
                                    <p>Loading investor data...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
export default {
    name: 'InvestorPortal',
    data() {
        return {
            summary: null,
            filters: {
                period: 'monthly',
                date: this.getTodaysDate(),
            },
        };
    },
    mounted() {
        this.loadSummary();
    },
    methods: {
        getTodaysDate() {
            const d = new Date();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${d.getFullYear()}-${month}-${day}`;
        },
        loadSummary() {
            axios
                .get('/api/investor/summary', {
                    params: {
                        period: this.filters.period,
                        date: this.filters.date,
                    },
                })
                .then((res) => {
                    this.summary = res.data;
                })
                .catch((err) => {
                    console.log(err);
                    toastr.error(err.response?.data?.message || 'Unable to load investor summary.');
                });
        },
        formatMoney(value) {
            return parseFloat(value || 0).toFixed(2);
        },
        formatType(type) {
            return (type || '').replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
        },
        pdfUrl(period) {
            if (!this.summary) {
                return '#';
            }
            const date = this.filters.date ? `?date=${this.filters.date}` : '';
            return `/investor/pdf/${this.summary.investor.investor_id}/${period}${date}`;
        },
    },
};
</script>
