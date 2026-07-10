<template>
  <div>
    <div class="card card-info mt-3">
      <div class="card-header">
        <h3 class="card-title">Karigar Jobs — Outward / Inward</h3>
        <div class="card-tools">
          <select class="form-control form-control-sm" v-model="statusFilter" @change="loadJobs">
            <option value="">All statuses</option>
            <option value="issued">Issued (with karigar)</option>
            <option value="returned">Returned (ready for sale)</option>
          </select>
        </div>
      </div>
      <div class="card-body table-responsive">
        <table class="table table-bordered table-sm">
          <thead>
            <tr>
              <th>Date</th>
              <th>Karigar</th>
              <th>Metal</th>
              <th class="text-right">Issued (g)</th>
              <th class="text-right">Returned (g)</th>
              <th>Item Type</th>
              <th class="text-right">Mazduri</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="job in jobs.data" :key="job.karigar_job_id">
              <td class="text-nowrap">{{ formatJobDate(job.job_date) }}</td>
              <td>{{ job.karigar ? job.karigar.karigar_name : '-' }}</td>
              <td>{{ job.metal_type }}</td>
              <td class="text-right">{{ job.issued_weight_grams }}</td>
              <td class="text-right">{{ job.returned_weight_grams || '-' }}</td>
              <td>{{ job.quality ? job.quality.quality_name : (job.item_description || '-') }}</td>
              <td class="text-right">{{ job.mazduri_cost ? 'Rs. ' + job.mazduri_cost : '-' }}</td>
              <td><span class="badge" :class="statusClass(job.job_status)">{{ job.job_status }}</span></td>
              <td>
                <button v-if="job.job_status === 'issued'" class="btn btn-sm btn-primary" @click="openReturn(job)">
                  Record Inward
                </button>
                <button
                  v-if="!job.invoice_mst_id"
                  class="btn btn-sm btn-danger"
                  :class="{ 'ml-1': job.job_status === 'issued' }"
                  @click="deleteJob(job)"
                  title="Delete job and restore stock"
                >
                  <i class="fas fa-trash"></i>
                </button>
                <span v-else-if="job.invoice_mst_id" class="text-muted small">Invoiced</span>
              </td>
            </tr>
          </tbody>
        </table>
        <pagination :data="jobs" @pagination-change-page="loadJobs"></pagination>
      </div>
    </div>

    <div v-if="returnJob" class="card card-warning mt-3">
      <div class="card-header">
        <h3 class="card-title">Inward — Receive from {{ returnJob.karigar ? returnJob.karigar.karigar_name : 'Karigar' }}</h3>
      </div>
      <div class="card-body">
        <p class="text-muted">Issued: <strong>{{ returnJob.issued_weight_grams }}g</strong> {{ returnJob.metal_type }} — {{ returnJob.item_description || 'No description' }}</p>
        <div class="form-group row">
          <label class="col-md-2 col-form-label">Return Date <span style="color:red">*</span></label>
          <div class="col-md-4">
            <input type="datetime-local" class="form-control" v-model="returnForm.returnDate">
          </div>
          <label class="col-md-2 col-form-label">Item Type <span style="color:red">*</span></label>
          <div class="col-md-4">
            <model-select :options="qualityOptions" v-model="returnForm.sellQualityId" placeholder="Select item type"></model-select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label">Returned Weight (g) <span style="color:red">*</span></label>
          <div class="col-md-2">
            <input type="number" step="0.001" class="form-control text-right" v-model="returnForm.returnedWeightGrams">
          </div>
          <label class="col-md-2 col-form-label">Pieces</label>
          <div class="col-md-2">
            <input type="number" min="1" class="form-control text-right" v-model="returnForm.returnedPieces">
          </div>
          <label class="col-md-2 col-form-label">Wastage (g)</label>
          <div class="col-md-2">
            <input type="number" step="0.001" min="0" class="form-control text-right" v-model="returnForm.wastageGrams">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label">Mazduri (Rs.)</label>
          <div class="col-md-4">
            <input type="number" step="0.01" min="0" class="form-control text-right" v-model="returnForm.mazduriCost">
            <small class="text-muted">Deducted from profit when this item is sold on invoice</small>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <button class="btn btn-warning" @click="submitReturn">Save Inward</button>
        <button class="btn btn-secondary ml-2" @click="returnJob = null">Cancel</button>
      </div>
    </div>
  </div>
</template>

<script>
import toastr from 'toastr';
import swal from 'sweetalert2';
import { ModelSelect } from 'vue-search-select';
import { getNowDateTime, formatDate } from '../../currency';

export default {
  name: 'SMKarigarJobs',
  components: { ModelSelect },
  data() {
    return {
      jobs: {},
      statusFilter: '',
      returnJob: null,
      qualities: [],
      returnForm: {
        returnDate: getNowDateTime(),
        sellQualityId: '',
        returnedWeightGrams: '',
        returnedPieces: 1,
        wastageGrams: 0,
        mazduriCost: '',
      },
    };
  },
  computed: {
    qualityOptions() {
      return this.qualities.map(q => ({ value: q.sell_quality_id, text: q.quality_name }));
    },
  },
  mounted() {
    this.loadJobs();
    this.loadQualities();
  },
  methods: {
    formatJobDate(d) { return formatDate(d); },
    statusClass(s) {
      return s === 'issued' ? 'badge-warning' : s === 'returned' ? 'badge-success' : 'badge-secondary';
    },
    loadJobs(page = 1) {
      axios.get('/api/karigar/jobs?page=' + page + '&paginate=10&status=' + this.statusFilter).then(res => {
        this.jobs = res.data;
      });
    },
    loadQualities() {
      axios.get('/api/sellqualitycategories').then(catRes => {
        const cats = catRes.data.qualityCategories || [];
        const reqs = cats.map(c => axios.get('/api/sellqualityofcategory/' + c.qualityCategoryId));
        Promise.all(reqs).then(results => {
          this.qualities = results.flatMap(r => r.data || []);
        });
      });
    },
    openReturn(job) {
      this.returnJob = job;
      this.returnForm = {
        returnDate: getNowDateTime(),
        sellQualityId: job.sell_quality_id || '',
        returnedWeightGrams: job.issued_weight_grams,
        returnedPieces: 1,
        wastageGrams: 0,
        mazduriCost: '',
      };
    },
    submitReturn() {
      if (!this.returnJob || !this.returnForm.sellQualityId || !this.returnForm.returnedWeightGrams) {
        toastr.info('Item type and returned weight are required.');
        return;
      }
      axios.post('/api/karigar/jobs/' + this.returnJob.karigar_job_id + '/return', {
        returnDate: this.returnForm.returnDate,
        sellQualityId: this.returnForm.sellQualityId,
        returnedWeightGrams: this.returnForm.returnedWeightGrams,
        returnedPieces: this.returnForm.returnedPieces,
        wastageGrams: this.returnForm.wastageGrams,
        mazduriCost: this.returnForm.mazduriCost || 0,
      }).then(res => {
        if (res.data.status === 1) {
          swal.fire('Success', res.data.message, 'success');
          this.returnJob = null;
          this.loadJobs();
          this.$emit('job-changed');
        } else {
          toastr.error(res.data.message);
        }
      }).catch(err => toastr.error(err.response?.data?.message || 'Return failed.'));
    },
    deleteJob(job) {
      swal.fire({
        title: 'Delete karigar job?',
        html: 'This will remove the job and <strong>restore stock</strong> to your inventory.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Delete',
      }).then(result => {
        if (!result.isConfirmed) {
          return;
        }
        axios.delete('/api/karigar/jobs/' + job.karigar_job_id).then(res => {
          if (res.data.status === 1) {
            swal.fire('Deleted', res.data.message, 'success');
            if (this.returnJob && this.returnJob.karigar_job_id === job.karigar_job_id) {
              this.returnJob = null;
            }
            this.loadJobs();
            this.$emit('job-changed');
          } else {
            toastr.error(res.data.message);
          }
        }).catch(err => {
          const status = err.response?.status;
          const data = err.response?.data;
          const msg = data?.message
            || (data?.errors && Object.values(data.errors).flat()[0])
            || (status === 404 ? 'Delete route not found — restart the server or run: php artisan route:clear' : null)
            || 'Delete failed.';
          toastr.error(msg);
        });
      });
    },
  },
};
</script>
