<template>
    <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">{{ $t('portal.title') }}</h3>
                                </div>
                                <div class="card-body" v-if="summary">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label class="text-md">{{ $t('portal.reportPeriod') }}</label>
                                            <select class="form-control" v-model="filters.period" @change="loadSummary">
                                                <option value="daily">{{ $t('portal.daily') }}</option>
                                                <option value="weekly">{{ $t('portal.weekly') }}</option>
                                                <option value="monthly">{{ $t('portal.monthly') }}</option>
                                                <option value="custom">{{ $t('portal.custom') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2" v-if="filters.period !== 'custom'">
                                            <label class="text-md">{{ $t('portal.refDate') }}</label>
                                            <input type="date" class="form-control" v-model="filters.date" @change="loadSummary">
                                        </div>
                                        <template v-if="filters.period === 'custom'">
                                            <div class="col-md-2">
                                                <label class="text-md">{{ $t('portal.startDate') }}</label>
                                                <input type="date" class="form-control" v-model="filters.from_date" @change="loadSummary">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="text-md">{{ $t('portal.endDate') }}</label>
                                                <input type="date" class="form-control" v-model="filters.to_date" @change="loadSummary">
                                            </div>
                                        </template>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <a :href="pdfUrl()" target="_blank" class="btn btn-danger btn-sm">
                                                <i class="fas fa-file-pdf"></i> {{ $t('portal.downloadPdf') }}
                                            </a>
                                        </div>
                                    </div>

                                    <p class="mb-1"><strong>{{ $t('portal.name') }}</strong> {{ summary.investor.investor_name }}</p>
                                    <p class="text-muted mb-3">
                                        {{ $t('portal.period') }} {{ summary.period.from_date }} {{ $t('portal.rangeTo') }} {{ summary.period.to_date }}
                                        · {{ $t('portal.yourShare') }} {{ summary.lab_summary.share_percentage }}%
                                        <span class="d-block small">{{ $t('portal.labOnly') }}</span>
                                    </p>

                                    <div class="alert alert-light border mb-3">
                                        <strong>{{ $t('portal.accountSummary') }}</strong>
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <p class="mb-1"><strong>{{ $t('portal.deposited') }}</strong> {{ formatMoney(summary.investment_summary.total_deposited) }}</p>
                                                <p class="mb-1 text-muted small">{{ $t('portal.helperCashGold') }}</p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-1"><strong>{{ $t('portal.usedInLab') }}</strong> {{ formatMoney(summary.investment_summary.lab_purchases) }}</p>
                                                <p class="mb-1"><strong>{{ $t('portal.capitalReturned') }}</strong> {{ formatMoney(summary.investment_summary.lab_capital_returned) }}</p>
                                                <p class="mb-1"><strong>{{ $t('portal.inOpenJobs') }}</strong> {{ formatMoney(summary.investment_summary.capital_in_open_jobs) }}</p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-1"><strong>{{ $t('portal.profitCredited') }}</strong> {{ formatMoney(summary.investment_summary.lab_profit_earned) }}</p>
                                                <p class="mb-1"><strong>{{ $t('portal.expensesAssigned') }}</strong> {{ formatMoney(summary.investment_summary.expenses_assigned) }}</p>
                                                <p class="mb-1"><strong>{{ $t('portal.netLabProfit') }}</strong> {{ formatMoney(summary.investment_summary.net_lab_profit) }}</p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-1"><strong>{{ $t('portal.paidOut') }}</strong> {{ formatMoney(summary.investment_summary.total_paid_out) }}</p>
                                                <p class="mb-1"><strong>{{ $t('portal.balancePayable') }}</strong> {{ formatMoney(summary.investment_summary.current_balance) }}</p>
                                                <p class="mb-0 text-muted small">{{ $t('portal.helperBalance') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row investor-stat-row">
                                        <div class="col-sm-6 col-lg-3 mb-3">
                                            <div class="small-box investor-stat-box bg-primary">
                                                <div class="inner">
                                                    <h3 class="stat-amount">{{ formatMoney(summary.investment_summary.current_balance) }}</h3>
                                                    <p>{{ $t('portal.statBalance') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-3 mb-3">
                                            <div class="small-box investor-stat-box bg-info">
                                                <div class="inner">
                                                    <h3 class="stat-amount">{{ formatMoney(summary.investment_summary.total_deposited) }}</h3>
                                                    <p>{{ $t('portal.statDeposited') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-3 mb-3">
                                            <div class="small-box investor-stat-box bg-info">
                                                <div class="inner">
                                                    <h3 class="stat-amount">{{ formatMoney(summary.investment_summary.lab_capital_returned) }}</h3>
                                                    <p>{{ $t('portal.statReturned') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-3 mb-3">
                                            <div class="small-box investor-stat-box bg-warning">
                                                <div class="inner">
                                                    <h3 class="stat-amount">{{ formatMoney(summary.investment_summary.capital_in_open_jobs) }}</h3>
                                                    <p>{{ $t('portal.statOpen') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-3 mb-3">
                                            <div class="small-box investor-stat-box bg-teal">
                                                <div class="inner">
                                                    <h3 class="stat-amount">{{ formatMoney(summary.investment_summary.lab_profit_earned) }}</h3>
                                                    <p>{{ $t('portal.statProfit') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-3 mb-3">
                                            <div class="small-box investor-stat-box bg-success">
                                                <div class="inner">
                                                    <h3 class="stat-amount">{{ formatMoney(summary.investment_summary.net_lab_profit) }}</h3>
                                                    <p>{{ $t('portal.statNet') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-3 mb-3">
                                            <div class="small-box investor-stat-box bg-warning">
                                                <div class="inner">
                                                    <h3 class="stat-amount">{{ formatMoney(summary.investment_summary.expenses_assigned) }}</h3>
                                                    <p>{{ $t('portal.statExpenses') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-3 mb-3">
                                            <div class="small-box investor-stat-box bg-secondary">
                                                <div class="inner">
                                                    <h3 class="stat-amount">{{ formatMoney(summary.investment_summary.total_paid_out) }}</h3>
                                                    <p>{{ $t('portal.statPaid') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-3 mb-3">
                                            <div class="small-box investor-stat-box bg-secondary">
                                                <div class="inner">
                                                    <h3 class="stat-amount stat-compact">{{ summary.gold_holdings.gold }}g</h3>
                                                    <p>{{ $t('portal.statGold') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-3 mb-3">
                                            <div class="small-box investor-stat-box bg-dark">
                                                <div class="inner">
                                                    <h3 class="stat-amount stat-compact">{{ summary.lab_summary.total_jobs }}</h3>
                                                    <p>{{ $t('portal.statJobs') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <p><strong>{{ $t('portal.yourLabProfit') }}</strong> {{ formatMoney(summary.lab_summary.total_lab_profit) }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p><strong>{{ $t('portal.expensesAssignedLabel') }}</strong> {{ formatMoney(summary.lab_summary.allocated_expenses) }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p><strong>{{ $t('portal.netLabProfitLabel') }}</strong> {{ formatMoney(summary.lab_summary.net_lab_profit) }}</p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <p><strong>{{ $t('portal.metalWeight') }}</strong> {{ formatWeight(summary.lab_summary.total_weight_grams) }}g</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p><strong>{{ $t('portal.openSold') }}</strong> {{ summary.lab_summary.open_jobs }} / {{ summary.lab_summary.sold_jobs }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p><strong>{{ $t('portal.silverHeld') }}</strong> {{ summary.gold_holdings.silver }}g</p>
                                        </div>
                                    </div>

                                    <h5 class="mt-4">{{ $t('portal.labJobs') }}</h5>
                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th>{{ $t('common.date') }}</th>
                                                    <th>{{ $t('lab.ref') }}</th>
                                                    <th>{{ $t('common.metal') }}</th>
                                                    <th class="text-right">{{ $t('common.weightG') }}</th>
                                                    <th class="text-right">{{ $t('lab.base') }}</th>
                                                    <th class="text-right">{{ $t('lab.refinery') }}</th>
                                                    <th class="text-right">{{ $t('lab.sold') }}</th>
                                                    <th class="text-right">{{ $t('portal.jobProfit') }}</th>
                                                    <th class="text-right">{{ $t('portal.yourPct') }}</th>
                                                    <th class="text-right">{{ $t('portal.yourProfit') }}</th>
                                                    <th>{{ $t('common.status') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="job in labJobs.data" :key="job.lab_job_id">
                                                    <td class="text-nowrap">{{ formatJobDate(job.job_date) }}</td>
                                                    <td>{{ job.job_reference || '-' }}</td>
                                                    <td class="text-capitalize">{{ job.metal_type }}</td>
                                                    <td class="text-right">{{ formatJobWeight(job.weight_grams) }}</td>
                                                    <td class="text-right text-nowrap">{{ formatTableAmount(job.base_price) }}</td>
                                                    <td class="text-right text-nowrap">{{ formatTableAmount(job.refinery_cost) }}</td>
                                                    <td class="text-right text-nowrap">{{ job.sold_amount != null ? formatTableAmount(job.sold_amount) : '-' }}</td>
                                                    <td class="text-right text-nowrap">{{ job.profit_amount != null ? formatTableAmount(job.profit_amount) : '-' }}</td>
                                                    <td class="text-right text-nowrap">{{ job.my_share_percentage != null ? job.my_share_percentage + '%' : '-' }}</td>
                                                    <td class="text-right text-nowrap">{{ job.my_profit_share != null ? formatTableAmount(job.my_profit_share) : '-' }}</td>
                                                    <td class="text-capitalize">{{ job.job_status }}</td>
                                                </tr>
                                                <tr v-if="!labJobs.data || !labJobs.data.length">
                                                    <td colspan="11" class="text-center text-muted">{{ $t('portal.noJobs') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <p class="text-muted small mb-4">{{ $t('common.labAmountsInRs') }}</p>

                                    <h5 class="mt-4">{{ $t('portal.expenseAssignments') }}</h5>
                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th>{{ $t('common.date') }}</th>
                                                    <th>{{ $t('common.description') }}</th>
                                                    <th class="text-right">{{ $t('common.amount') }}</th>
                                                    <th>{{ $t('common.notes') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="item in summary.expense_allocations" :key="item.investor_expense_allocation_id">
                                                    <td>{{ formatJobDate(item.allocation_date) }}</td>
                                                    <td>{{ item.description }}</td>
                                                    <td class="text-right">{{ formatMoney(item.allocated_amount) }}</td>
                                                    <td>{{ item.notes || '-' }}</td>
                                                </tr>
                                                <tr v-if="!summary.expense_allocations || !summary.expense_allocations.length">
                                                    <td colspan="4" class="text-center text-muted">{{ $t('portal.noExpenses') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <h5 class="mt-4">{{ $t('portal.txnPeriod') }}</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th>{{ $t('common.date') }}</th>
                                                    <th>{{ $t('common.type') }}</th>
                                                    <th>{{ $t('common.metal') }}</th>
                                                    <th class="text-right">{{ $t('common.weightG') }}</th>
                                                    <th class="text-right">{{ $t('common.rateG') }}</th>
                                                    <th class="text-right">{{ $t('common.amount') }}</th>
                                                    <th>{{ $t('common.notes') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="txn in summary.transactions" :key="txn.investor_transaction_id">
                                                    <td class="text-nowrap">{{ formatJobDate(txn.transaction_date) }}</td>
                                                    <td>{{ formatType(txn.transaction_type) }}</td>
                                                    <td>{{ txn.metal_type || '-' }}</td>
                                                    <td class="text-right">{{ txn.weight_grams || '-' }}</td>
                                                    <td class="text-right">{{ txn.rate_per_gram || '-' }}</td>
                                                    <td class="text-right">{{ formatMoney(txn.amount) }}</td>
                                                    <td>{{ txn.notes || '-' }}</td>
                                                </tr>
                                                <tr v-if="!summary.transactions.length">
                                                    <td colspan="7" class="text-center text-muted">{{ $t('portal.noTxns') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-body" v-else-if="summary && summary.error">
                                    <p class="text-danger mb-0">{{ $t('portal.loadFail') }}</p>
                                </div>
                                <div class="card-body" v-else>
                                    <p>{{ $t('portal.loading') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
</template>

<script>
import { pdfUrl } from '../auth';
import { formatCurrency, formatAmount, formatDate } from '../currency';

function todayIsoDate() {
    const d = new Date();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${d.getFullYear()}-${month}-${day}`;
}

function startOfMonthIsoDate() {
    const d = new Date();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    return `${d.getFullYear()}-${month}-01`;
}

export default {
    name: 'InvestorPortal',
    data() {
        return {
            summary: null,
            labJobs: { data: [] },
            filters: {
                period: 'monthly',
                date: todayIsoDate(),
                from_date: startOfMonthIsoDate(),
                to_date: todayIsoDate(),
            },
        };
    },
    mounted() {
        this.loadSummary();
    },
    methods: {
        loadSummary() {
            const params = {
                period: this.filters.period,
            };

            if (this.filters.period === 'custom') {
                params.from_date = this.filters.from_date;
                params.to_date = this.filters.to_date;
            } else {
                params.date = this.filters.date;
            }

            axios
                .get('/api/investor/summary', { params })
                .then((res) => {
                    this.summary = res.data;
                    this.loadLabJobs();
                })
                .catch((err) => {
                    console.log(err);
                    this.summary = { error: true };
                    toastr.error(err.response?.data?.message || this.$t('portal.summaryFail'));
                });
        },
        loadLabJobs() {
            if (!this.summary) {
                return;
            }
            const params = {
                from_date: this.summary.period.from_date,
                to_date: this.summary.period.to_date,
            };

            axios.get('/api/lab/jobs', { params: { ...params, paginate: 50 } })
                .then((res) => {
                    this.labJobs = res.data;
                })
                .catch((err) => console.log(err));
        },
        formatMoney(value) {
            return formatCurrency(value);
        },
        formatTableAmount(value) {
            return formatAmount(value);
        },
        formatJobDate(value) {
            return formatDate(value);
        },
        formatJobWeight(value) {
            const weight = parseFloat(value || 0);
            return Number.isNaN(weight) ? '0.000' : weight.toFixed(3);
        },
        formatWeight(value) {
            const weight = parseFloat(value || 0);
            return Number.isNaN(weight) ? '0.000' : weight.toFixed(3);
        },
        formatType(type) {
            const labels = {
                deposit: this.$t('portal.typeDeposit'),
                withdrawal: this.$t('portal.typePayout'),
                lab_purchase: this.$t('portal.typeLabPurchase'),
                lab_sale_return: this.$t('portal.typeCapitalReturned'),
                lab_profit: this.$t('portal.typeProfitCredited'),
                gold_buy: this.$t('portal.typeGoldBuy'),
                gold_sell: this.$t('portal.typeGoldSell'),
            };
            return labels[type] || (type || '').replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
        },
        pdfUrl() {
            if (!this.summary) {
                return '#';
            }

            const params = new URLSearchParams();
            if (this.filters.period === 'custom') {
                params.set('from_date', this.filters.from_date);
                params.set('to_date', this.filters.to_date);
            } else if (this.filters.date) {
                params.set('date', this.filters.date);
            }

            const query = params.toString();
            return pdfUrl(
                `/investor/pdf/${this.summary.investor.investor_id}/${this.filters.period}${query ? '?' + query : ''}`
            );
        },
    },
};
</script>

<style scoped>
.investor-stat-row {
    margin-left: -0.5rem;
    margin-right: -0.5rem;
}

.investor-stat-box {
    min-height: 7.25rem;
    margin-bottom: 0;
    display: flex;
}

.investor-stat-box .inner {
    position: relative;
    width: 100%;
    padding: 1rem 0.9rem;
    overflow: hidden;
}

.investor-stat-box h3.stat-amount {
    font-size: clamp(0.95rem, 0.85rem + 0.55vw, 1.4rem);
    line-height: 1.3;
    font-weight: 700;
    margin: 0 0 0.6rem;
    white-space: normal;
    word-break: break-word;
    max-width: 100%;
}

.investor-stat-box h3.stat-compact {
    font-size: clamp(1.2rem, 1rem + 0.8vw, 1.75rem);
}

.investor-stat-box p {
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.25;
}
</style>
