<template>
    <div>
        <aside></aside>
        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid">
                    <div class="row dashboard-stat-row">
                        <div class="col-xl-4 col-lg-4 col-md-6 col-6 mt-3">
                            <div class="small-box dashboard-stat-box bg-warning">
                                <div class="inner">
                                    <h3 :class="['stat-value', statSizeClass(goldWeight + 'g')]">
                                        {{ goldWeight }}<sup class="stat-unit">g</sup>
                                    </h3>
                                    <p class="stat-label">Gold Stock</p>
                                </div>
                                <div class="icon"><i class="fas fa-coins"></i></div>
                                <div class="dashboard-stat-footer-spacer" aria-hidden="true"></div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-6 mt-3">
                            <div class="small-box dashboard-stat-box bg-secondary">
                                <div class="inner">
                                    <h3 :class="['stat-value', statSizeClass(silverWeight + 'g')]">
                                        {{ silverWeight }}<sup class="stat-unit">g</sup>
                                    </h3>
                                    <p class="stat-label">Silver (Chandi) Stock</p>
                                </div>
                                <div class="icon"><i class="fas fa-ring"></i></div>
                                <div class="dashboard-stat-footer-spacer" aria-hidden="true"></div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-6 mt-3">
                            <div class="small-box dashboard-stat-box bg-info has-footer">
                                <div class="inner">
                                    <h3 :class="['stat-value', statSizeClass(inward)]">
                                        <span v-html="inward"></span>
                                    </h3>
                                    <p class="stat-label">Purchases</p>
                                    <p class="stat-fy"><b>{{ financialYear }}</b></p>
                                </div>
                                <div class="icon"><i class="bi bi-box-arrow-in-right"></i></div>
                                <router-link to="/manageinward" class="nav-link small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </router-link>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-6 mt-3">
                            <div class="small-box dashboard-stat-box bg-success has-footer">
                                <div class="inner">
                                    <h3 :class="['stat-value', statSizeClass(invoice)]">
                                        <span v-html="invoice"></span>
                                    </h3>
                                    <p class="stat-label">Sales Bills</p>
                                    <p class="stat-fy"><b>{{ financialYear }}</b></p>
                                </div>
                                <div class="icon"><i class="bi bi-receipt-cutoff"></i></div>
                                <router-link to="/managechallaninvoice" class="nav-link small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </router-link>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-6 mt-3">
                            <div class="small-box dashboard-stat-box bg-primary">
                                <div class="inner">
                                    <h3 :class="['stat-value', statSizeClass(profit)]">{{ profit }}</h3>
                                    <p class="stat-label">Profit (FY)</p>
                                    <p class="stat-fy"><b>{{ financialYear }}</b></p>
                                </div>
                                <div class="icon"><i class="fas fa-chart-line"></i></div>
                                <div class="dashboard-stat-footer-spacer" aria-hidden="true"></div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-6 mt-3">
                            <div class="small-box dashboard-stat-box bg-orange has-footer">
                                <div class="inner">
                                    <h3 :class="['stat-value', statSizeClass(credit)]">{{ credit }}</h3>
                                    <p class="stat-label">Credit</p>
                                    <p class="stat-fy"><b>{{ financialYear }}</b></p>
                                </div>
                                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                                <router-link to="/credit" class="nav-link small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </router-link>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-6 mt-3">
                            <div class="small-box dashboard-stat-box bg-dark has-footer">
                                <div class="inner">
                                    <h3 :class="['stat-value', statSizeClass(labJobs)]">
                                        <span v-html="labJobs"></span>
                                    </h3>
                                    <p class="stat-label">Lab Jobs (FY)</p>
                                    <p class="stat-fy"><b>{{ financialYear }}</b></p>
                                </div>
                                <div class="icon"><i class="fas fa-flask"></i></div>
                                <router-link to="/laboratory" class="nav-link small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </router-link>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-6 mt-3">
                            <div class="small-box dashboard-stat-box bg-teal">
                                <div class="inner">
                                    <h3 :class="['stat-value', statSizeClass(labProfit)]">{{ labProfit }}</h3>
                                    <p class="stat-label">Lab Profit (FY)</p>
                                    <p class="stat-fy"><b>{{ financialYear }}</b></p>
                                </div>
                                <div class="icon"><i class="fas fa-vial"></i></div>
                                <div class="dashboard-stat-footer-spacer" aria-hidden="true"></div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-6 mt-3">
                            <div class="small-box dashboard-stat-box bg-danger has-footer">
                                <div class="inner">
                                    <h3 :class="['stat-value', statSizeClass(expense)]">{{ expense }}</h3>
                                    <p class="stat-label">Expense</p>
                                    <p class="stat-fy"><b>{{ financialYear }}</b></p>
                                </div>
                                <div class="icon"><i class="bi bi-dash-circle"></i></div>
                                <router-link to="/expensemanagement" class="nav-link small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </router-link>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
import { formatCurrency } from '../currency';
import { hasPermission } from '../auth';

export default {
    name: 'Dashboard',
    data() {
        return {
            inward: '',
            invoice: '',
            financialYear: '',
            credit: '',
            expense: '',
            profit: '',
            labJobs: '',
            labProfit: '',
            goldWeight: '0.000',
            silverWeight: '0.000',
        };
    },
    mounted() {
        if (!hasPermission('dashboard')) {
            return;
        }

        this.loadCalculations();
        this.loadMetalBalances();
    },
    methods: {
        loadCalculations() {
            axios
                .get('api/dashboarddata')
                .then((res) => {
                    this.inward = res.data[1];
                    this.invoice = res.data[2];
                    this.financialYear =
                        this.printDate(res.data[0].fromDate) +
                        ' - ' +
                        this.printDate(res.data[0].toDate);
                    this.credit = formatCurrency(res.data[3]);
                    this.expense = formatCurrency(res.data[4]);
                    this.profit = formatCurrency(res.data[5] || 0);
                    this.labJobs = res.data[7] || 0;
                    this.labProfit = formatCurrency(res.data[8] || 0);
                })
                .catch((err) => {
                    console.log(err);
                    toastr.error('Something Went Wrong!');
                });
        },

        loadMetalBalances() {
            axios
                .get('api/stock/balances')
                .then((res) => {
                    if (res.data.gold) {
                        this.goldWeight = parseFloat(res.data.gold.total_weight_grams).toFixed(3);
                    }
                    if (res.data.silver) {
                        this.silverWeight = parseFloat(res.data.silver.total_weight_grams).toFixed(3);
                    }
                })
                .catch((err) => {
                    console.log(err);
                });
        },

        printDate(date) {
            const printableDate = date.split('-');
            return printableDate[2] + '/' + printableDate[1] + '/' + printableDate[0];
        },

        statSizeClass(value) {
            const text = String(value ?? '').replace(/<[^>]+>/g, '');
            const len = text.length;

            if (len <= 7) return 'stat-xl';
            if (len <= 10) return 'stat-lg';
            if (len <= 13) return 'stat-md';
            if (len <= 16) return 'stat-sm';
            if (len <= 20) return 'stat-xs';
            return 'stat-xxs';
        },
    },
};
</script>

<style scoped>
.dashboard-stat-row {
    margin-left: -0.5rem;
    margin-right: -0.5rem;
}

.dashboard-stat-row > [class*='col-'] {
    display: flex;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}

.dashboard-stat-box {
    width: 100%;
    min-height: 8.5rem;
    display: flex;
    flex-direction: column;
    border-radius: 0.45rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.14);
    overflow: hidden;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.dashboard-stat-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
}

.dashboard-stat-box .inner {
    flex: 1;
    position: relative;
    z-index: 1;
    padding: 1rem 4.25rem 0.5rem 1rem;
    overflow: hidden;
}

.dashboard-stat-box h3.stat-value {
    font-weight: 700;
    margin: 0 0 0.35rem;
    line-height: 1.2;
    white-space: normal;
    word-break: break-word;
    max-width: 100%;
    letter-spacing: -0.02em;
}

.dashboard-stat-box h3.stat-value.stat-xl { font-size: 2rem; }
.dashboard-stat-box h3.stat-value.stat-lg { font-size: 1.55rem; }
.dashboard-stat-box h3.stat-value.stat-md { font-size: 1.28rem; }
.dashboard-stat-box h3.stat-value.stat-sm { font-size: 1.08rem; }
.dashboard-stat-box h3.stat-value.stat-xs { font-size: 0.92rem; }
.dashboard-stat-box h3.stat-value.stat-xxs { font-size: 0.8rem; }

.dashboard-stat-box .stat-unit {
    font-size: 0.55em;
    font-weight: 600;
    margin-left: 0.1rem;
}

.dashboard-stat-box .stat-label {
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.25;
    font-weight: 500;
}

.dashboard-stat-box .stat-fy {
    margin: 0.35rem 0 0;
    font-size: 0.72rem;
    line-height: 1.3;
    opacity: 0.88;
    word-break: break-word;
}

.dashboard-stat-box .icon {
    font-size: 3.25rem;
    top: 0.85rem;
    right: 0.85rem;
    opacity: 0.22;
    transition: opacity 0.18s ease;
}

.dashboard-stat-box:hover .icon {
    opacity: 0.3;
}

.dashboard-stat-footer-spacer {
    min-height: 2.1rem;
    flex-shrink: 0;
}

.dashboard-stat-box.has-footer .dashboard-stat-footer-spacer {
    display: none;
}

.dashboard-stat-box .small-box-footer {
    position: relative;
    z-index: 1;
    margin-top: auto;
}

.bg-orange {
    background-color: #fd7e14 !important;
    color: #fff;
}

.bg-teal {
    background-color: #20c997 !important;
    color: #fff;
}

@media (max-width: 576px) {
    .dashboard-stat-box {
        min-height: 7.75rem;
    }

    .dashboard-stat-box h3.stat-value.stat-xl { font-size: 1.55rem; }
    .dashboard-stat-box h3.stat-value.stat-lg { font-size: 1.3rem; }
    .dashboard-stat-box h3.stat-value.stat-md { font-size: 1.1rem; }
    .dashboard-stat-box h3.stat-value.stat-sm { font-size: 0.95rem; }
    .dashboard-stat-box h3.stat-value.stat-xs { font-size: 0.85rem; }
    .dashboard-stat-box h3.stat-value.stat-xxs { font-size: 0.75rem; }

    .dashboard-stat-box .icon {
        font-size: 2.5rem;
    }
}
</style>
