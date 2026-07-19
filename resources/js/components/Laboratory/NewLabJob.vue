<template>
    <div class="row">
        <div class="col-md-12 mt-3">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">{{ $t('lab.newTitle') }}</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body lab-job-form">
                    <p class="text-muted small mb-3">{{ $t('lab.helperSelectInvestors') }}</p>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label class="lab-label">{{ $t('lab.jobDateTime') }} <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" v-model="form.job_date">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="lab-label">{{ $t('lab.investors') }} <span class="text-danger">*</span></label>
                            <investor-multi-select
                                :investors="investors"
                                v-model="form.investor_ids"
                                :placeholder="$t('lab.phInvestors')"
                                @change="refreshSharePreview"
                            />
                        </div>
                        <div class="form-group col-md-2">
                            <label class="lab-label">{{ $t('common.reference') }}</label>
                            <input type="text" class="form-control" v-model="form.job_reference" :placeholder="$t('lab.phJobNo')">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="lab-label">{{ $t('common.metal') }} <span class="text-danger">*</span></label>
                            <select class="form-control" v-model="form.metal_type">
                                <option value="gold">{{ $t('common.gold') }}</option>
                                <option value="silver">{{ $t('common.silver') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label class="lab-label">{{ $t('common.weightG') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control text-right" v-model="form.weight_grams" min="0" step="0.001">
                        </div>
                        <div class="form-group col-md-2">
                            <label class="lab-label">{{ $t('lab.basePrice') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control text-right" v-model="form.base_price" min="0" step="0.01" @input="refreshSharePreview">
                        </div>
                        <div class="form-group col-md-2">
                            <label class="lab-label">{{ $t('lab.refineryCost') }}</label>
                            <input type="number" class="form-control text-right" v-model="form.refinery_cost" min="0" step="0.01" @input="refreshSharePreview">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="lab-label">{{ $t('lab.soldAmount') }}</label>
                            <input type="number" class="form-control text-right" v-model="form.sold_amount" min="0" step="0.01" @input="refreshSharePreview">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="lab-label">{{ $t('lab.estProfit') }}</label>
                            <input type="text" class="form-control text-right" :value="estimatedProfit" disabled>
                        </div>
                    </div>

                    <div v-if="sharePreview.participants.length" class="alert alert-light border mb-3">
                        <strong>{{ $t('lab.splitPreview') }}</strong>
                        <span class="text-muted small ml-2">
                            {{ $t('lab.splitHintPerInvestor') }}
                            · {{ $t('lab.shopCut', { pct: sharePreview.shop_profit_percentage }) }}
                            <span v-if="estimatedProfit !== '-' && sharePreview.shop_profit_amount != null">
                                ({{ formatAmount(sharePreview.shop_profit_amount) }})
                            </span>
                            · {{ $t('lab.investorPool', { pct: sharePreview.investor_pool_percentage }) }}
                            · {{ $t('lab.profitAllocated', { pct: sharePreview.total_profit_share_percentage }) }}
                            <span v-if="sharePreview.unallocated_profit_percentage > 0">
                                · {{ $t('lab.unallocated', { pct: sharePreview.unallocated_profit_percentage }) }}
                            </span>
                        </span>
                        <table class="table table-sm table-borderless mb-0 mt-2">
                            <thead>
                                <tr>
                                    <th>{{ $t('lab.investor') }}</th>
                                    <th class="text-right">{{ $t('lab.balance') }}</th>
                                    <th class="text-right">{{ $t('lab.purchaseCut') }}</th>
                                    <th class="text-right">{{ $t('lab.profitShare') }}</th>
                                    <th class="text-right" v-if="estimatedProfit !== '-'">{{ $t('lab.estProfit') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in sharePreview.participants" :key="row.investor_id">
                                    <td>{{ row.investor_name }}</td>
                                    <td class="text-right">{{ formatAmount(row.investment_basis) }}</td>
                                    <td class="text-right">{{ formatAmount(row.purchase_share) }}</td>
                                    <td class="text-right">{{ Number(row.share_percentage).toFixed(2) }}%</td>
                                    <td class="text-right" v-if="estimatedProfit !== '-'">
                                        {{ formatAmount(row.profit_share) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group mb-0">
                        <label class="lab-label">{{ $t('common.notes') }}</label>
                        <textarea class="form-control" v-model="form.notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-primary" @click="saveJob">{{ $t('lab.addJob') }}</button>
                    <button type="reset" class="btn btn-secondary" @click="resetForm">{{ $t('common.reset') }}</button>
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
                split_mode: 'custom',
                shop_profit_percentage: 0,
                shop_profit_amount: null,
                investor_pool_percentage: 100,
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
                    toastr['error'](this.$t('lab.loadInvestorsFail'));
                });
        },
        emptyPreview() {
            return {
                participants: [],
                total_investment_basis: 0,
                total_profit_share_percentage: 0,
                unallocated_profit_percentage: 100,
                split_mode: 'custom',
                shop_profit_percentage: 0,
                shop_profit_amount: null,
                investor_pool_percentage: 100,
            };
        },
        refreshSharePreview() {
            const investorIds = this.normalizedInvestorIds();
            if (!investorIds.length) {
                this.sharePreview = this.emptyPreview();
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
                    this.sharePreview = this.emptyPreview();
                    if (err.response?.data?.message) {
                        toastr['error'](err.response.data.message);
                    }
                });
        },
        saveJob() {
            const investorIds = this.normalizedInvestorIds();
            if (!this.form.job_date || !investorIds.length || this.form.weight_grams === '' || this.form.base_price === '') {
                toastr['error'](this.$t('lab.requiredFields'));
                return;
            }

            const payload = { ...this.form, investor_ids: investorIds };
            axios.post('/api/lab/jobs', payload)
                .then((res) => {
                    if (res.data.status === 1) {
                        swal.fire({
                            title: this.$t('common.success'),
                            html: "<h5 style='color:#9C9794'>" + this.$t('lab.added') + "</h5>",
                            icon: 'success',
                        }).then(() => {
                            this.resetForm();
                            this.$emit('refreshLabJobs');
                        });
                    } else {
                        toastr['error'](res.data.message || this.$t('lab.addFail'));
                    }
                })
                .catch((err) => {
                    toastr['error'](err.response?.data?.message || this.$t('lab.addFail'));
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
            this.sharePreview = this.emptyPreview();
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
