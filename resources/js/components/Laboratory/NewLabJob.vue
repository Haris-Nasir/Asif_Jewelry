<template>
    <div class="row">
        <div class="col-md-12 mt-3">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">New Laboratory Job</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-md-2">
                            <label class="text-md">Job Date *</label>
                            <input type="date" class="form-control" v-model="form.job_date">
                        </div>
                        <div class="col-md-3">
                            <label class="text-md">Investor *</label>
                            <select class="form-control" v-model="form.investor_id">
                                <option value="">Select investor</option>
                                <option v-for="inv in investors" :key="inv.investor_id" :value="inv.investor_id">
                                    {{ inv.investor_name }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="text-md">Reference</label>
                            <input type="text" class="form-control" v-model="form.job_reference" placeholder="Job no.">
                        </div>
                        <div class="col-md-2">
                            <label class="text-md">Metal *</label>
                            <select class="form-control" v-model="form.metal_type">
                                <option value="gold">Gold</option>
                                <option value="silver">Silver</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="text-md">Weight (g) *</label>
                            <input type="number" class="form-control text-right" v-model="form.weight_grams" min="0" step="0.001">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-2">
                            <label class="text-md">Base Price (₹) *</label>
                            <input type="number" class="form-control text-right" v-model="form.base_price" min="0" step="0.01">
                        </div>
                        <div class="col-md-2">
                            <label class="text-md">Cash Amount (₹)</label>
                            <input type="number" class="form-control text-right" v-model="form.cash_amount" min="0" step="0.01">
                        </div>
                        <div class="col-md-2">
                            <label class="text-md">Refinery Cost (₹)</label>
                            <input type="number" class="form-control text-right" v-model="form.refinery_cost" min="0" step="0.01">
                        </div>
                        <div class="col-md-2">
                            <label class="text-md">Sold Amount (₹)</label>
                            <input type="number" class="form-control text-right" v-model="form.sold_amount" min="0" step="0.01" @input="calcProfit">
                        </div>
                        <div class="col-md-2">
                            <label class="text-md">Est. Profit (₹)</label>
                            <input type="text" class="form-control text-right" :value="estimatedProfit" disabled>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="text-md">Notes</label>
                        <textarea class="form-control" v-model="form.notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-primary" @click="saveJob">Add Job</button>
                    <button type="reset" class="btn btn-secondary" @click="resetForm">Reset</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import toastr from 'toastr';
import swal from 'sweetalert2';

toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-center',
};

export default {
    name: 'NewLabJob',
    data() {
        return {
            investors: [],
            form: {
                job_date: '',
                investor_id: '',
                job_reference: '',
                metal_type: 'gold',
                weight_grams: '',
                base_price: '',
                cash_amount: '',
                refinery_cost: '',
                sold_amount: '',
                notes: '',
            },
        };
    },
    computed: {
        estimatedProfit() {
            if (this.form.sold_amount === '' || this.form.sold_amount === null) {
                return '-';
            }
            const sold = parseFloat(this.form.sold_amount) || 0;
            const base = parseFloat(this.form.base_price) || 0;
            const refinery = parseFloat(this.form.refinery_cost) || 0;
            return (sold - base - refinery).toFixed(2);
        },
    },
    mounted() {
        this.form.job_date = this.getTodaysDate();
        this.loadInvestors();
    },
    methods: {
        getTodaysDate() {
            const d = new Date();
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        },
        loadInvestors() {
            axios.get('/api/investor/list')
                .then((res) => {
                    this.investors = res.data.data || [];
                })
                .catch((err) => {
                    console.log(err);
                    toastr['error']('Unable to load investors.');
                });
        },
        calcProfit() {},
        saveJob() {
            if (!this.form.job_date || !this.form.investor_id || this.form.weight_grams === '' || this.form.base_price === '') {
                toastr['error']('Job date, investor, weight, and base price are required.');
                return;
            }
            axios.post('/api/lab/jobs', this.form)
                .then((res) => {
                    if (res.data.status === 1) {
                        swal.fire({
                            title: 'Success',
                            html: "<h5 style='color:#9C9794'>Laboratory Job Added Successfully!</h5>",
                            icon: 'success',
                        }).then(() => {
                            this.resetForm();
                            this.$emit('refreshLabJobs');
                        });
                    } else {
                        toastr['error'](res.data.message || 'Failed to add job.');
                    }
                })
                .catch((err) => {
                    toastr['error'](err.response?.data?.message || 'Failed to add job.');
                });
        },
        resetForm() {
            this.form = {
                job_date: this.getTodaysDate(),
                investor_id: '',
                job_reference: '',
                metal_type: 'gold',
                weight_grams: '',
                base_price: '',
                cash_amount: '',
                refinery_cost: '',
                sold_amount: '',
                notes: '',
            };
        },
    },
};
</script>
