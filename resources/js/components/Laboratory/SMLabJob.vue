<template>
    <div class="row">
        <div class="col-md-12 mt-3">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Manage Laboratory Jobs</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ summary.total_jobs }}</h3>
                                    <p>Total Jobs</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ summary.open_jobs }}</h3>
                                    <p>Open</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>₹{{ formatMoney(summary.total_lab_profit) }}</h3>
                                    <p>Lab Profit</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-secondary">
                                <div class="inner">
                                    <h3>{{ summary.total_weight_grams }}g</h3>
                                    <p>Total Weight</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-row mb-3">
                        <div class="col-md-2">
                            <label>From Date</label>
                            <input type="date" class="form-control" v-model="filters.from_date" @change="loadJobs">
                        </div>
                        <div class="col-md-2">
                            <label>To Date</label>
                            <input type="date" class="form-control" v-model="filters.to_date" @change="loadJobs">
                        </div>
                        <div class="col-md-3">
                            <label>Investor</label>
                            <select class="form-control" v-model="filters.investor_id" @change="loadJobs">
                                <option value="">All investors</option>
                                <option v-for="inv in investors" :key="inv.investor_id" :value="inv.investor_id">
                                    {{ inv.investor_name }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Status</label>
                            <select class="form-control" v-model="filters.job_status" @change="loadJobs">
                                <option value="">All</option>
                                <option value="open">Open</option>
                                <option value="sold">Sold</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Ref</th>
                                    <th>Investor</th>
                                    <th>Metal</th>
                                    <th class="text-right">Weight (g)</th>
                                    <th class="text-right">Base (₹)</th>
                                    <th class="text-right">Cash (₹)</th>
                                    <th class="text-right">Refinery (₹)</th>
                                    <th class="text-right">Sold (₹)</th>
                                    <th class="text-right">Profit (₹)</th>
                                    <th>Status</th>
                                    <th width="12%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="job in jobs.data" :key="job.lab_job_id">
                                    <td>{{ job.job_date }}</td>
                                    <td>{{ job.job_reference || '-' }}</td>
                                    <td>{{ job.investor ? job.investor.investor_name : '-' }}</td>
                                    <td>{{ job.metal_type }}</td>
                                    <td class="text-right">{{ job.weight_grams }}</td>
                                    <td class="text-right">{{ formatMoney(job.base_price) }}</td>
                                    <td class="text-right">{{ formatMoney(job.cash_amount) }}</td>
                                    <td class="text-right">{{ formatMoney(job.refinery_cost) }}</td>
                                    <td class="text-right">{{ job.sold_amount != null ? formatMoney(job.sold_amount) : '-' }}</td>
                                    <td class="text-right">{{ job.profit_amount != null ? formatMoney(job.profit_amount) : '-' }}</td>
                                    <td>
                                        <span class="badge" :class="job.job_status === 'sold' ? 'badge-success' : 'badge-warning'">
                                            {{ job.job_status }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-primary btn-sm" @click="startEdit(job)"><i class="fas fa-pen"></i></button>
                                        <button class="btn btn-danger btn-sm" @click="deleteJob(job.lab_job_id)"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr v-if="!jobs.data || !jobs.data.length">
                                    <td colspan="12" class="text-center text-muted">No laboratory jobs found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-3" v-if="jobs.data && jobs.data.length">
                        <div class="col-sm-6 offset-5">
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
                        <h5 class="modal-title">Edit Laboratory Job</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Job Date</label>
                                <input type="date" class="form-control" v-model="editForm.job_date">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Investor</label>
                                <select class="form-control" v-model="editForm.investor_id">
                                    <option v-for="inv in investors" :key="inv.investor_id" :value="inv.investor_id">
                                        {{ inv.investor_name }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Reference</label>
                                <input type="text" class="form-control" v-model="editForm.job_reference">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>Metal</label>
                                <select class="form-control" v-model="editForm.metal_type">
                                    <option value="gold">Gold</option>
                                    <option value="silver">Silver</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Weight (g)</label>
                                <input type="number" class="form-control" v-model="editForm.weight_grams" step="0.001">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Base Price (₹)</label>
                                <input type="number" class="form-control" v-model="editForm.base_price" step="0.01">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Cash (₹)</label>
                                <input type="number" class="form-control" v-model="editForm.cash_amount" step="0.01">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Refinery Cost (₹)</label>
                                <input type="number" class="form-control" v-model="editForm.refinery_cost" step="0.01">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Sold Amount (₹)</label>
                                <input type="number" class="form-control" v-model="editForm.sold_amount" step="0.01">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Notes</label>
                                <input type="text" class="form-control" v-model="editForm.notes">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" @click="updateJob">Save Changes</button>
                    </div>
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
    name: 'SMLabJob',
    data() {
        return {
            jobs: { data: [] },
            investors: [],
            summary: {
                total_jobs: 0,
                open_jobs: 0,
                sold_jobs: 0,
                total_weight_grams: 0,
                total_cash_amount: 0,
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
            return parseFloat(value || 0).toFixed(2);
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
                    toastr['error']('Unable to load laboratory jobs.');
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
                job_date: job.job_date,
                investor_id: job.investor_id,
                job_reference: job.job_reference || '',
                metal_type: job.metal_type,
                weight_grams: job.weight_grams,
                base_price: job.base_price,
                cash_amount: job.cash_amount,
                refinery_cost: job.refinery_cost,
                sold_amount: job.sold_amount,
                notes: job.notes || '',
            };
            $('#editLabJobModal').modal('show');
        },
        updateJob() {
            axios.put(`/api/lab/jobs/${this.editForm.lab_job_id}`, this.editForm)
                .then((res) => {
                    if (res.data.status === 1) {
                        swal.fire({
                            title: 'Success',
                            html: "<h5 style='color:#9C9794'>Laboratory Job Updated Successfully!</h5>",
                            icon: 'success',
                        }).then(() => {
                            $('#editLabJobModal').modal('hide');
                            this.loadJobs(this.jobs.current_page || 1);
                        });
                    } else {
                        toastr['error'](res.data.message || 'Update failed.');
                    }
                })
                .catch((err) => {
                    toastr['error'](err.response?.data?.message || 'Update failed.');
                });
        },
        deleteJob(labJobId) {
            swal.fire({
                title: 'Delete job?',
                text: 'This laboratory job will be removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
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
                            toastr['error'](err.response?.data?.message || 'Delete failed.');
                        });
                }
            });
        },
    },
};
</script>
