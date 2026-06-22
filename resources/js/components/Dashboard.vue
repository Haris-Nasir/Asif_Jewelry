<template>
    <div>
        <aside></aside>
        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-3 col-6 mt-3">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ goldWeight }}<sup style="font-size: 16px">g</sup></h3>
                                    <p>Gold Stock</p>
                                </div>
                                <div class="icon">
                                    <i class="nav-icon fas fa-coins"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6 mt-3">
                            <div class="small-box bg-secondary">
                                <div class="inner">
                                    <h3>{{ silverWeight }}<sup style="font-size: 16px">g</sup></h3>
                                    <p>Silver (Chandi) Stock</p>
                                </div>
                                <div class="icon">
                                    <i class="nav-icon fas fa-ring"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6 mt-3">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3 v-html="inward"></h3>
                                    <p>Purchases</p>
                                    <p style="margin-top: -1rem; margin-bottom: -0.5rem">
                                        <b>{{ financialYear }}</b>
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="nav-icon nav-icon far bi bi-box-arrow-in-right"></i>
                                </div>
                                <router-link to="/manageinward" class="nav-link small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </router-link>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6 mt-3">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3 v-html="invoice"></h3>
                                    <p>Sales Bills</p>
                                    <p style="margin-top: -1rem; margin-bottom: -0.5rem">
                                        <b>{{ financialYear }}</b>
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="nav-icon nav-icon far bi bi-receipt-cutoff"></i>
                                </div>
                                <router-link to="/managechallaninvoice" class="nav-link small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </router-link>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6 mt-3">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3 v-html="profit"></h3>
                                    <p>Profit (FY)</p>
                                    <p style="margin-top: -1rem; margin-bottom: -0.5rem">
                                        <b>{{ financialYear }}</b>
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="nav-icon fas fa-chart-line"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6 mt-3">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3 v-html="credit"></h3>
                                    <p>Credit</p>
                                    <p style="margin-top: -1rem; margin-bottom: -0.5rem">
                                        <b>{{ financialYear }}</b>
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="nav-icon fas fa-rupee-sign"></i>
                                </div>
                                <router-link to="/credit" class="nav-link small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </router-link>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6 mt-3">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3 v-html="expense"></h3>
                                    <p>Expense</p>
                                    <p style="margin-top: -1rem; margin-bottom: -0.5rem">
                                        <b>{{ financialYear }}</b>
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="nav-icon far bi bi-dash-circle"></i>
                                </div>
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
    export default {
        name: "Dashboard",
        data() {
            return {
                inward: "",
                invoice: "",
                financialYear: "",
                credit: "",
                expense: "",
                profit: "",
                goldWeight: "0.000",
                silverWeight: "0.000",
            };
        },
        mounted() {
            this.loadCalculations();
            this.loadMetalBalances();
        },
        methods: {
            loadCalculations: function () {
                axios
                    .get("api/dashboarddata")
                    .then((res) => {
                        this.inward = res.data[1];
                        this.invoice = res.data[2];
                        this.financialYear =
                            this.printDate(res.data[0]["fromDate"]) +
                            " - " +
                            this.printDate(res.data[0]["toDate"]);
                        this.credit = "₹" + res.data[3];
                        this.expense = "₹" + res.data[4];
                        this.profit = "₹" + (res.data[5] || 0);
                    })
                    .catch((err) => {
                        console.log(err);
                        toastr.error("Something Went Wrong!");
                    });
            },

            loadMetalBalances: function () {
                axios
                    .get("api/stock/balances")
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

            printDate: function (date) {
                let printableDate = date.split("-");
                return printableDate[2] + "/" + printableDate[1] + "/" + printableDate[0];
            },
        },
    };
</script>
