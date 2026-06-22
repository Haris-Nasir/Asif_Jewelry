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

                                    <h5 class="mt-4">Laboratory Jobs</h5>
                                    <div class="row mb-3" v-if="labSummary">
                                        <div class="col-md-3">
                                            <p><strong>Total Jobs:</strong> {{ labSummary.total_jobs }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p><strong>Open:</strong> {{ labSummary.open_jobs }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p><strong>Lab Profit:</strong> ₹{{ formatMoney(labSummary.total_lab_profit) }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p><strong>Weight:</strong> {{ labSummary.total_weight_grams }}g</p>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Ref</th>
                                                    <th>Metal</th>
                                                    <th class="text-right">Weight (g)</th>
                                                    <th class="text-right">Base (₹)</th>
                                                    <th class="text-right">Cash (₹)</th>
                                                    <th class="text-right">Refinery (₹)</th>
                                                    <th class="text-right">Sold (₹)</th>
                                                    <th class="text-right">Profit (₹)</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="job in labJobs.data" :key="job.lab_job_id">
                                                    <td>{{ job.job_date }}</td>
                                                    <td>{{ job.job_reference || '-' }}</td>
                                                    <td>{{ job.metal_type }}</td>
                                                    <td class="text-right">{{ job.weight_grams }}</td>
                                                    <td class="text-right">{{ formatMoney(job.base_price) }}</td>
                                                    <td class="text-right">{{ formatMoney(job.cash_amount) }}</td>
                                                    <td class="text-right">{{ formatMoney(job.refinery_cost) }}</td>
                                                    <td class="text-right">{{ job.sold_amount != null ? formatMoney(job.sold_amount) : '-' }}</td>
                                                    <td class="text-right">{{ job.profit_amount != null ? formatMoney(job.profit_amount) : '-' }}</td>
                                                    <td>{{ job.job_status }}</td>
                                                </tr>
                                                <tr v-if="!labJobs.data || !labJobs.data.length">
                                                    <td colspan="10" class="text-center text-muted">No laboratory jobs in this period.</td>
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
import { pdfUrl } from '../auth';

export default {
    name: 'InvestorPortal',
    data() {
        return {
            summary: null,
            labSummary: null,
            labJobs: { data: [] },
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
                    this.loadLabData();
                })
                .catch((err) => {
                    console.log(err);
                    toastr.error(err.response?.data?.message || 'Unable to load investor summary.');
                });
        },
        loadLabData() {
            if (!this.summary) {
                return;
            }
            const params = {
                from_date: this.summary.period.from_date,
                to_date: this.summary.period.to_date,
            };
            axios.get('/api/lab/summary', { params })
                .then((res) => {
                    this.labSummary = res.data;
                })
                .catch((err) => console.log(err));

            axios.get('/api/lab/jobs', { params: { ...params, paginate: 50 } })
                .then((res) => {
                    this.labJobs = res.data;
                })
                .catch((err) => console.log(err));
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
            return pdfUrl(`/investor/pdf/${this.summary.investor.investor_id}/${period}${date}`);
        },
    },
};
</script>
