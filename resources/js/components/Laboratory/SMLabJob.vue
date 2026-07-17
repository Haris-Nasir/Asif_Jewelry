<template>
    <div class="row">
        <div class="col-md-12 mt-3">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">{{ $t('lab.manageTitle') }}</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row lab-stat-row mb-3">
                        <div class="col-6 col-md-3 mb-2">
                            <div class="small-box lab-stat-box bg-info mb-0">
                                <div class="inner">
                                    <h3>{{ summary.total_jobs }}</h3>
                                    <p>{{ $t('lab.totalJobs') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <div class="small-box lab-stat-box bg-warning mb-0">
                                <div class="inner">
                                    <h3>{{ summary.open_jobs }}</h3>
                                    <p>{{ $t('common.open') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <div class="small-box lab-stat-box bg-success mb-0">
                                <div class="inner">
                                    <h3 class="stat-amount">{{ formatMoney(summary.total_lab_profit) }}</h3>
                                    <p>{{ $t('lab.labProfit') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <div class="small-box lab-stat-box bg-secondary mb-0">
                                <div class="inner">
                                    <h3 class="stat-amount">{{ formatWeight(summary.total_weight_grams) }}g</h3>
                                    <p>{{ $t('lab.totalWeight') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-row lab-filter-row mb-3">
                        <div class="form-group col-md-3 col-sm-6 mb-2">
                            <label>{{ $t('common.fromDate') }}</label>
                            <input type="date" class="form-control" v-model="filters.from_date" @change="loadJobs">
                        </div>
                        <div class="form-group col-md-3 col-sm-6 mb-2">
                            <label>{{ $t('common.toDate') }}</label>
                            <input type="date" class="form-control" v-model="filters.to_date" @change="loadJobs">
                        </div>
                        <div class="form-group col-md-3 col-sm-6 mb-2">
                            <label>{{ $t('lab.investor') }}</label>
                            <select class="form-control" v-model="filters.investor_id" @change="loadJobs">
                                <option value="">{{ $t('common.allInvestors') }}</option>
                                <option v-for="inv in investors" :key="inv.investor_id" :value="inv.investor_id">
                                    {{ inv.investor_name }}
                                </option>
                            </select>
                        </div>
                        <div class="form-group col-md-3 col-sm-6 mb-2">
                            <label>{{ $t('common.status') }}</label>
                            <select class="form-control" v-model="filters.job_status" @change="loadJobs">
                                <option value="">{{ $t('common.all') }}</option>
                                <option value="open">{{ $t('common.open') }}</option>
                                <option value="sold">{{ $t('lab.sold') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm lab-jobs-table">
                            <thead>
                                <tr>
                                    <th>{{ $t('common.date') }}</th>
                                    <th>{{ $t('lab.ref') }}</th>
                                    <th>{{ $t('lab.investors') }}</th>
                                    <th>{{ $t('common.metal') }}</th>
                                    <th class="text-right text-nowrap">{{ $t('common.weightG') }}</th>
                                    <th class="text-right">{{ $t('lab.base') }}</th>
                                    <th class="text-right">{{ $t('lab.refinery') }}</th>
                                    <th class="text-right">{{ $t('lab.sold') }}</th>
                                    <th class="text-right">{{ $t('common.profit') }}</th>
                                    <th>{{ $t('common.status') }}</th>
                                    <th class="text-center" width="90">{{ $t('common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="job in jobs.data" :key="job.lab_job_id">
                                    <td class="text-nowrap">{{ formatDate(job.job_date) }}</td>
                                    <td>{{ job.job_reference || '-' }}</td>
                                    <td class="investor-cell">{{ formatParticipants(job) }}</td>
                                    <td class="text-capitalize">{{ job.metal_type }}</td>
                                    <td class="text-right text-nowrap">{{ formatWeight(job.weight_grams) }}</td>
                                    <td class="text-right text-nowrap">{{ formatAmount(job.base_price) }}</td>
                                    <td class="text-right text-nowrap">{{ formatAmount(job.refinery_cost) }}</td>
                                    <td class="text-right text-nowrap">{{ job.sold_amount != null ? formatAmount(job.sold_amount) : '-' }}</td>
                                    <td class="text-right text-nowrap">{{ job.profit_amount != null ? formatAmount(job.profit_amount) : '-' }}</td>
                                    <td>
                                        <span class="badge text-capitalize" :class="job.job_status === 'sold' ? 'badge-success' : 'badge-warning'">
                                            {{ job.job_status }}
                                        </span>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <div class="table-actions">
                                            <button type="button" class="btn btn-primary btn-sm" @click="startEdit(job)" :title="$t('common.edit')">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" @click="deleteJob(job.lab_job_id)" :title="$t('common.delete')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!jobs.data || !jobs.data.length">
                                    <td colspan="11" class="text-center text-muted py-3">{{ $t('lab.noJobsFound') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted small mb-0 mt-2">{{ $t('common.amountsInRs') }}</p>

                    <div class="row mt-3" v-if="jobs.data && jobs.data.length">
                        <div class="col-sm-6 offset-sm-3 col-md-4 offset-md-4">
                            <pagination :data="jobs" @pagination-change-page="loadJobs"></pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editLabJobModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content" v-if="editForm.lab_job_id">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $t('lab.editTitle') }}</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>{{ $t('lab.jobDate') }}</label>
                                <input type="datetime-local" class="form-control" v-model="editForm.job_date">
                            </div>
                            <div class="form-group col-md-5">
                                <label>{{ $t('lab.investors') }} *</label>
                                <investor-multi-select
                                    :investors="investors"
                                    v-model="editForm.investor_ids"
                                    :placeholder="$t('lab.phInvestors')"
                                />
                            </div>
                            <div class="form-group col-md-3">
                                <label>{{ $t('common.reference') }}</label>
                                <input type="text" class="form-control" v-model="editForm.job_reference">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>{{ $t('common.metal') }}</label>
                                <select class="form-control" v-model="editForm.metal_type">
                                    <option value="gold">{{ $t('common.gold') }}</option>
                                    <option value="silver">{{ $t('common.silver') }}</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>{{ $t('common.weightG') }}</label>
                                <input type="number" class="form-control" v-model="editForm.weight_grams" step="0.001">
                            </div>
                            <div class="form-group col-md-3">
                                <label>{{ $t('lab.basePrice') }}</label>
                                <input type="number" class="form-control" v-model="editForm.base_price" step="0.01">
                            </div>
                            <div class="form-group col-md-3">
                                <label>{{ $t('lab.refineryCost') }}</label>
                                <input type="number" class="form-control" v-model="editForm.refinery_cost" step="0.01">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>{{ $t('lab.soldAmount') }}</label>
                                <input type="number" class="form-control" v-model="editForm.sold_amount" step="0.01">
                            </div>
                            <div class="form-group col-md-8">
                                <label>{{ $t('common.notes') }}</label>
                                <input type="text" class="form-control" v-model="editForm.notes">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ $t('common.cancel') }}</button>
                        <button type="button" class="btn btn-primary" @click="updateJob">{{ $t('lab.saveChanges') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import toastr from 'toastr';
import swal from 'sweetalert2';
import { formatCurrency, formatAmount as formatAmountValue, formatDate, toInputDateTime } from '../../currency';
import InvestorMultiSelect from './InvestorMultiSelect.vue';

toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-center',
};

export default {
    name: 'SMLabJob',
    components: { InvestorMultiSelect },
    data() {
        return {
            jobs: { data: [] },
            investors: [],
            summary: {
                total_jobs: 0,
                open_jobs: 0,
                sold_jobs: 0,
                total_weight_grams: 0,
                total_lab_profit: 0,
            },
            filters: {
                from_date: '',
                to_date: '',
                investor_id: '',
                job_status: '',
            },
            editForm: {},
        };
    },
    mounted() {
        this.setDefaultDateRange();
        this.loadInvestors();
        this.loadJobs();
        this.loadSummary();
    },
    methods: {
        setDefaultDateRange() {
            const d = new Date();
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            this.filters.from_date = `${year}-${month}-01`;
            this.filters.to_date = `${year}-${month}-${String(d.getDate()).padStart(2, '0')}`;
        },
        formatMoney(value) {
            return formatCurrency(value);
        },
        formatAmount(value) {
            return formatAmountValue(value);
        },
        formatDate(value) {
            return formatDate(value);
        },
        formatWeight(value) {
            const weight = parseFloat(value || 0);
            return Number.isNaN(weight) ? '0.000' : weight.toFixed(3);
        },
        formatParticipants(job) {
            const rows = job.participants || [];
            if (!rows.length) {
                return job.investor ? job.investor.investor_name : '-';
            }
            return rows.map((row) => `${row.investor_name} (${row.share_percentage}%)`).join(', ');
        },
        loadInvestors() {
            axios.get('/api/investor/list')
                .then((res) => {
                    this.investors = res.data.data || [];
                })
                .catch((err) => console.log(err));
        },
        loadJobs(page = 1) {
            axios.get('/api/lab/jobs', {
                params: {
                    page,
                    from_date: this.filters.from_date,
                    to_date: this.filters.to_date,
                    investor_id: this.filters.investor_id || undefined,
                    job_status: this.filters.job_status || undefined,
                },
            })
                .then((res) => {
                    this.jobs = res.data;
                    this.loadSummary();
                })
                .catch((err) => {
                    console.log(err);
                    toastr['error'](this.$t('lab.loadFail'));
                });
        },
        loadSummary() {
            axios.get('/api/lab/summary', {
                params: {
                    from_date: this.filters.from_date,
                    to_date: this.filters.to_date,
                    investor_id: this.filters.investor_id || undefined,
                },
            })
                .then((res) => {
                    this.summary = res.data;
                })
                .catch((err) => console.log(err));
        },
        startEdit(job) {
            this.editForm = {
                lab_job_id: job.lab_job_id,
                job_date: toInputDateTime(job.job_date),
                investor_ids: (job.participants || []).map((row) => parseInt(row.investor_id, 10)),
                job_reference: job.job_reference || '',
                metal_type: job.metal_type,
                weight_grams: job.weight_grams,
                base_price: job.base_price,
                refinery_cost: job.refinery_cost,
                sold_amount: job.sold_amount,
                notes: job.notes || '',
            };
            $('#editLabJobModal').modal('show');
        },
        updateJob() {
            const investorIds = (this.editForm.investor_ids || []).map((id) => parseInt(id, 10)).filter((id) => !Number.isNaN(id));
            if (!investorIds.length) {
                toastr['error'](this.$t('lab.selectInvestor'));
                return;
            }
            axios.put(`/api/lab/jobs/${this.editForm.lab_job_id}`, { ...this.editForm, investor_ids: investorIds })
                .then((res) => {
                    if (res.data.status === 1) {
                        swal.fire({
                            title: this.$t('common.success'),
                            html: "<h5 style='color:#9C9794'>" + this.$t('lab.updated') + "</h5>",
                            icon: 'success',
                        }).then(() => {
                            $('#editLabJobModal').modal('hide');
                            this.loadJobs(this.jobs.current_page || 1);
                        });
                    } else {
                        toastr['error'](res.data.message || this.$t('lab.updateFail'));
                    }
                })
                .catch((err) => {
                    toastr['error'](err.response?.data?.message || this.$t('lab.updateFail'));
                });
        },
        deleteJob(labJobId) {
            swal.fire({
                title: this.$t('lab.deleteConfirm'),
                text: this.$t('lab.deleteJobText'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: this.$t('common.delete'),
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(`/api/lab/jobs/${labJobId}`)
                        .then((res) => {
                            if (res.data.status === 1) {
                                toastr['success'](res.data.message);
                                this.loadJobs(this.jobs.current_page || 1);
                            }
                        })
                        .catch((err) => {
                            toastr['error'](err.response?.data?.message || this.$t('lab.deleteFail'));
                        });
                }
            });
        },
    },
};
</script>

<style scoped>
.lab-stat-row > [class*="col-"] {
    display: flex;
}

.lab-stat-box {
    width: 100%;
    min-height: 6.5rem;
    margin-bottom: 0;
    display: flex;
    flex-direction: column;
}

.lab-stat-box .inner {
    padding: 0.85rem 1rem !important;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    overflow: hidden;
}

.lab-stat-box .inner h3,
.lab-stat-box h3.stat-amount {
    font-size: 1.35rem;
    line-height: 1.3;
    font-weight: 700;
    margin: 0 0 0.35rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.lab-stat-box .inner p {
    margin: 0;
    font-size: 0.9rem;
}

.lab-filter-row .form-group {
    margin-bottom: 0.5rem;
}

.lab-filter-row label {
    display: block;
    margin-bottom: 0.25rem;
    white-space: nowrap;
}

.lab-jobs-table th,
.lab-jobs-table td {
    vertical-align: middle;
    padding: 0.45rem 0.6rem;
}

.lab-jobs-table thead th {
    white-space: nowrap;
}

.lab-jobs-table .investor-cell {
    min-width: 8rem;
    max-width: 12rem;
    white-space: normal;
    word-break: break-word;
}
</style>
