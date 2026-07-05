<template>
  <div>
    <div class="card card-success mt-3">
      <div class="card-header"><h3 class="card-title">Outward — Issue Metal to Karigar (g)</h3></div>
      <div class="card-body">
        <div class="form-group row">
          <label class="col-md-2 col-form-label">Karigar <span style="color:red">*</span></label>
          <div class="col-md-4">
            <model-select :options="karigarOptions" v-model="form.karigarId" placeholder="Select karigar"></model-select>
          </div>
          <label class="col-md-2 col-form-label">Date &amp; Time <span style="color:red">*</span></label>
          <div class="col-md-4">
            <input type="datetime-local" class="form-control" v-model="form.jobDate">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label">Metal <span style="color:red">*</span></label>
          <div class="col-md-4">
            <select class="form-control" v-model="form.metalType">
              <option value="gold">Gold</option>
              <option value="silver">Silver</option>
            </select>
          </div>
          <label class="col-md-2 col-form-label">Weight (g) <span style="color:red">*</span></label>
          <div class="col-md-4">
            <input type="number" step="0.001" min="0.001" class="form-control text-right" v-model="form.issuedWeightGrams">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label">Item to make</label>
          <div class="col-md-10">
            <input type="text" class="form-control" v-model="form.itemDescription" placeholder="e.g. Gold tops, ring set...">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label">Notes</label>
          <div class="col-md-10">
            <textarea class="form-control" v-model="form.notes" rows="2"></textarea>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <button class="btn btn-success" @click="issueMetal">Record Outward</button>
        <button class="btn btn-secondary ml-2" @click="resetForm">Reset</button>
      </div>
    </div>
  </div>
</template>

<script>
import toastr from 'toastr';
import swal from 'sweetalert2';
import { ModelSelect } from 'vue-search-select';
import { getNowDateTime } from '../../currency';

export default {
  name: 'IssueKarigar',
  components: { ModelSelect },
  props: { karigars: { type: Array, default: () => [] } },
  data() {
    return {
      form: {
        karigarId: '',
        jobDate: getNowDateTime(),
        metalType: 'gold',
        issuedWeightGrams: '',
        itemDescription: '',
        notes: '',
      },
    };
  },
  computed: {
    karigarOptions() {
      return this.karigars.map(k => ({ value: k.karigar_id, text: k.karigar_name }));
    },
  },
  methods: {
    issueMetal() {
      if (!this.form.karigarId || !this.form.jobDate || !this.form.issuedWeightGrams) {
        toastr.info('Karigar, date, and weight are required.');
        return;
      }
      axios.post('/api/karigar/jobs/issue', {
        karigarId: this.form.karigarId,
        jobDate: this.form.jobDate,
        metalType: this.form.metalType,
        issuedWeightGrams: this.form.issuedWeightGrams,
        itemDescription: this.form.itemDescription,
        notes: this.form.notes,
      }).then(res => {
        if (res.data.status === 1) {
          swal.fire('Success', res.data.message, 'success');
          this.resetForm();
          this.$emit('job-changed');
        } else {
          toastr.error(res.data.message);
        }
      }).catch(err => toastr.error(err.response?.data?.message || 'Issue failed.'));
    },
    resetForm() {
      this.form = {
        karigarId: '',
        jobDate: getNowDateTime(),
        metalType: 'gold',
        issuedWeightGrams: '',
        itemDescription: '',
        notes: '',
      };
    },
  },
};
</script>
