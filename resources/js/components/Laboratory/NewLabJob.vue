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
                <div class="card-body lab-job-form">
                    <p class="text-muted small mb-3">
                        Select investors from the dropdown. Base price is cut equally from each selected investor.
                        Profit uses each investor's configured profit share % from Manage Investors.
                    </p>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label class="lab-label">Job Date &amp; Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" v-model="form.job_date">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="lab-label">Investors <span class="text-danger">*</span></label>
                            <investor-multi-select
                                :investors="investors"
                                v-model="form.investor_ids"
                                placeholder="Select investors..."
                                @change="refreshSharePreview"
                            />
                        </div>
                        <div class="form-group col-md-2">
                            <label class="lab-label">Reference</label>
                            <input type="text" class="form-control" v-model="form.job_reference" placeholder="Job no.">
                        </div>
                        <div class="form-group col-md-2">
                            <label class="lab-label">Metal <span class="text-danger">*</span></label>
                            <select class="form-control" v-model="form.metal_type">
                                <option value="gold">Gold</option>
                                <option value="silver">Silver</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label class="lab-label">Weight (g) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control text-right" v-model="form.weight_grams" min="0" step="0.001">
                        </div>
                    </div>

                    <div v-if="sharePreview.participants.length" class="alert alert-light border mb-3">
                        <strong>Split preview</strong>
                        <span class="text-muted small ml-2">
                            Profit allocated: {{ sharePreview.total_profit_share_percentage }}%
                            <span v-if="sharePreview.unallocated_profit_percentage > 0">
                                · Unallocated: {{ sharePreview.unallocated_profit_percentage }}%
                            </span>
                        </span>
                        <table class="table table-sm table-borderless mb-0 mt-2">
                            <thead>
                                <tr>
                                    <th>Investor</th>
                                    <th class="text-right">Balance (Rs.)</th>
                                    <th class="text-right">Purchase cut (Rs.)</th>
                                    <th class="text-right">Profit share %</th>
                                    <th class="text-right" v-if="estimatedProfit !== '-'">Est. profit (Rs.)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in sharePreview.participants" :key="row.investor_id">
                                    <td>{{ row.investor_name }}</td>
                                    <td class="text-right">{{ formatAmount(row.investment_basis) }}</td>
                                    <td class="text-right">{{ formatAmount(row.purchase_share) }}</td>
                                    <td class="text-right">{{ row.share_percentage }}%</td>
                                    <td class="text-right" v-if="estimatedProfit !== '-'">
                                        {{ formatAmount(row.profit_share) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label class="lab-label">Base Price (Rs.) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control text-right" v-model="form.base_price" min="0" step="0.01" @input="refreshSharePreview">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="lab-label">Refinery Cost (Rs.)</label>
                            <input type="number" class="form-control text-right" v-model="form.refinery_cost" min="0" step="0.01" @input="refreshSharePreview">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="lab-label">Sold Amount (Rs.)</label>
                            <input type="number" class="form-control text-right" v-model="form.sold_amount" min="0" step="0.01" @input="refreshSharePreview">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="lab-label">Est. Profit (Rs.)</label>
                            <input type="text" class="form-control text-right" :value="estimatedProfit" disabled>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="lab-label">Notes</label>
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
import { formatAmount as formatAmountValue, getNowDateTime } from '../../currency';
import InvestorMultiSelect from './InvestorMultiSelect.vue';

toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-center',
};

export default {
    name: 'NewLabJob',
    components: { InvestorMultiSelect },
    data() {
        return {
            investors: [],
            sharePreview: {
                participants: [],
                total_investment_basis: 0,
                total_profit_share_percentage: 0,
                unallocated_profit_percentage: 100,
            },
            form: {
                job_date: '',
                investor_ids: [],
                job_reference: '',
                metal_type: 'gold',
                weight_grams: '',
                base_price: '',
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
        this.form.job_date = getNowDateTime();
        this.loadInvestors();
    },
    methods: {
        formatAmount(value) {
            return formatAmountValue(value);
        },
        getTodaysDate() {
            const d = new Date();
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        },
        normalizedInvestorIds() {
            return this.form.investor_ids.map((id) => parseInt(id, 10)).filter((id) => !Number.isNaN(id));
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
        refreshSharePreview() {
            const investorIds = this.normalizedInvestorIds();
            if (!investorIds.length) {
                this.sharePreview = { participants: [], total_investment_basis: 0, total_profit_share_percentage: 0, unallocated_profit_percentage: 100 };
                return;
            }

            const params = { investor_ids: investorIds };
            if (this.form.base_price !== '') {
                params.base_price = this.form.base_price;
            }
            if (this.estimatedProfit !== '-') {
                params.profit_amount = this.estimatedProfit;
            }

            axios.get('/api/lab/preview-shares', { params })
                .then((res) => {
                    this.sharePreview = res.data;
                })
                .catch((err) => {
                    this.sharePreview = { participants: [], total_investment_basis: 0, total_profit_share_percentage: 0, unallocated_profit_percentage: 100 };
                    if (err.response?.data?.message) {
                        toastr['error'](err.response.data.message);
                    }
                });
        },
        saveJob() {
            const investorIds = this.normalizedInvestorIds();
            if (!this.form.job_date || !investorIds.length || this.form.weight_grams === '' || this.form.base_price === '') {
                toastr['error']('Job date, at least one investor, weight, and base price are required.');
                return;
            }

            const payload = { ...this.form, investor_ids: investorIds };
            axios.post('/api/lab/jobs', payload)
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
                job_date: getNowDateTime(),
                investor_ids: [],
                job_reference: '',
                metal_type: 'gold',
                weight_grams: '',
                base_price: '',
                refinery_cost: '',
                sold_amount: '',
                notes: '',
            };
            this.sharePreview = { participants: [], total_investment_basis: 0, total_profit_share_percentage: 0, unallocated_profit_percentage: 100 };
        },
    },
};
</script>

<style scoped>
.lab-job-form .lab-label {
    display: block;
    margin-bottom: 0.3rem;
    white-space: nowrap;
    font-weight: 600;
    color: #4a4035;
}

.lab-job-form .form-group {
    margin-bottom: 0.9rem;
}

.lab-job-form .form-control {
    width: 100%;
}
</style>
