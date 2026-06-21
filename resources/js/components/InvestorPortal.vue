<template>
    <div>
        <aside></aside>
        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Investor Portal</h3>
                                </div>
                                <div class="card-body" v-if="summary">
                                    <p><strong>Name:</strong> {{ summary.investor.investor_name }}</p>
                                    <p><strong>Profit Share:</strong> {{ summary.investor.profit_share_percentage }}%</p>
                                    <p class="text-muted">{{ summary.message }}</p>
                                    <div class="row mt-4">
                                        <div class="col-md-4">
                                            <div class="small-box bg-info">
                                                <div class="inner">
                                                    <h3>₹{{ summary.profit_summary.total_invested }}</h3>
                                                    <p>Total Invested</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="small-box bg-success">
                                                <div class="inner">
                                                    <h3>₹{{ summary.profit_summary.total_profit }}</h3>
                                                    <p>Total Profit</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="small-box bg-warning">
                                                <div class="inner">
                                                    <h3>{{ summary.profit_summary.share_percentage }}%</h3>
                                                    <p>Your Share %</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mt-3 text-info">
                                        Daily, monthly, and quarterly PDF reports — coming in Phase 3.
                                    </p>
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
        };
    },
    mounted() {
        this.loadSummary();
    },
    methods: {
        loadSummary() {
            axios
                .get('/api/investor/summary')
                .then((res) => {
                    this.summary = res.data;
                })
                .catch((err) => {
                    console.log(err);
                    toastr.error('Unable to load investor summary.');
                });
        },
    },
};
</script>
