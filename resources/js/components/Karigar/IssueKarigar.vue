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
          <label class="col-md-2 col-form-label">Item Type <span style="color:red">*</span></label>
          <div class="col-md-4">
            <model-select :options="qualityOptions" v-model="form.sellQualityId" placeholder="Select item type in stock"></model-select>
          </div>
          <label class="col-md-2 col-form-label">Metal</label>
          <div class="col-md-4">
            <input type="text" class="form-control" :value="metalLabel" disabled>
          </div>
        </div>
        <div class="col-md-12" v-if="qualityStock">
          <p class="text-muted small mb-2">
            In stock for <strong>{{ qualityStock.quality_name }}</strong>:
            {{ qualityStock.weight_grams }}g, {{ qualityStock.pieces }} pcs
            <span v-if="qualityStock.available_piece_weights_label">
              ({{ qualityStock.available_piece_weights_label }})
            </span>
          </p>
          <div class="mb-3" v-if="availableWeightOptions.length">
            <span class="small text-muted mr-2">Pick from available weights:</span>
            <button
              v-for="opt in availableWeightOptions"
              :key="opt.weight"
              type="button"
              class="btn btn-sm mr-1 mb-1"
              :class="isWeightSelected(opt.weight) ? 'btn-success' : 'btn-outline-secondary'"
              @click="selectAvailableWeight(opt)"
            >
              {{ opt.weight }}g
              <span v-if="opt.count > 1" class="badge badge-light ml-1">{{ opt.count }} pcs</span>
            </button>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label">Weight / pc (g) <span style="color:red">*</span></label>
          <div class="col-md-2">
            <input type="number" step="0.001" min="0.001" class="form-control text-right" v-model="form.weightPerPiece">
          </div>
          <label class="col-md-2 col-form-label">Pieces <span style="color:red">*</span></label>
          <div class="col-md-2">
            <input
              type="number"
              min="1"
              :max="maxPiecesForSelectedWeight || undefined"
              class="form-control text-right"
              v-model.number="form.issuedPieces"
            >
          </div>
          <label class="col-md-2 col-form-label">Total (g)</label>
          <div class="col-md-2">
            <input type="text" class="form-control text-right" :value="totalWeightLabel" disabled>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label">Item to make</label>
          <div class="col-md-10">
            <input type="text" class="form-control" v-model="form.itemDescription" placeholder="Ready product name, e.g. Angothi (matches Item Type on inward)">
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
        <button class="btn btn-success" @click="issueMetal" :disabled="!canSubmit">Record Outward</button>
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
      allQualities: [],
      stockByQualityId: {},
      qualityStock: null,
      form: {
        karigarId: '',
        jobDate: getNowDateTime(),
        sellQualityId: '',
        metalType: 'gold',
        weightPerPiece: '',
        issuedPieces: 1,
        itemDescription: '',
        notes: '',
      },
    };
  },
  computed: {
    karigarOptions() {
      return this.karigars.map(k => ({ value: k.karigar_id, text: k.karigar_name }));
    },
    qualityOptions() {
      return this.allQualities
        .filter(q => {
          const stock = this.stockByQualityId[q.sell_quality_id];
          return stock && stock.pieces > 0;
        })
        .map(q => {
          const stock = this.stockByQualityId[q.sell_quality_id];
          return {
            value: q.sell_quality_id,
            text: q.quality_name + ' — ' + stock.pieces + ' pc (' + stock.weight_grams + 'g)',
          };
        });
    },
    metalLabel() {
      return this.form.metalType === 'silver' ? 'Silver' : 'Gold';
    },
    availableWeightOptions() {
      if (!this.qualityStock || !this.qualityStock.available_piece_weights) {
        return [];
      }
      const counts = {};
      this.qualityStock.available_piece_weights.forEach(w => {
        const key = parseFloat(w).toFixed(3);
        counts[key] = (counts[key] || 0) + 1;
      });
      return Object.keys(counts)
        .sort((a, b) => parseFloat(a) - parseFloat(b))
        .map(key => ({ weight: parseFloat(key), count: counts[key] }));
    },
    maxPiecesForSelectedWeight() {
      const weight = parseFloat(this.form.weightPerPiece || 0);
      if (!weight || !this.qualityStock || !this.qualityStock.available_piece_weights) {
        return 0;
      }
      return this.qualityStock.available_piece_weights.filter(
        w => Math.abs(parseFloat(w) - weight) <= 0.0005
      ).length;
    },
    totalWeightLabel() {
      const perPiece = parseFloat(this.form.weightPerPiece || 0);
      const pieces = parseInt(this.form.issuedPieces || 0, 10);
      if (perPiece <= 0 || pieces <= 0) {
        return '';
      }
      return (perPiece * pieces).toFixed(3);
    },
    canSubmit() {
      return !!(
        this.form.karigarId &&
        this.form.jobDate &&
        this.form.sellQualityId &&
        this.form.weightPerPiece &&
        this.form.issuedPieces > 0 &&
        this.maxPiecesForSelectedWeight >= this.form.issuedPieces
      );
    },
  },
  watch: {
    'form.sellQualityId'(val) {
      const quality = this.allQualities.find(q => q.sell_quality_id == val);
      if (quality && quality.metalType) {
        this.form.metalType = quality.metalType;
      }
      this.form.weightPerPiece = '';
      this.form.issuedPieces = 1;
      this.loadQualityStock();
    },
    'form.weightPerPiece'() {
      if (this.maxPiecesForSelectedWeight > 0 && this.form.issuedPieces > this.maxPiecesForSelectedWeight) {
        this.form.issuedPieces = this.maxPiecesForSelectedWeight;
      }
    },
  },
  mounted() {
    this.loadQualities();
    this.loadStockBalances();
  },
  methods: {
    loadStockBalances() {
      axios.get('/api/stock/balances').then(res => {
        const byQuality = res.data.by_quality || [];
        this.stockByQualityId = {};
        byQuality.forEach(b => {
          this.stockByQualityId[b.sell_quality_id] = b;
        });
      });
    },
    loadQualities() {
      axios.get('/api/sellqualitycategories').then(catRes => {
        const cats = catRes.data.qualityCategories || [];
        const reqs = cats.map(c =>
          axios.get('/api/sellqualityofcategory/' + c.qualityCategoryId).then(r =>
            (r.data || []).map(q => ({
              ...q,
              metalType: c.metalType,
              categoryName: c.qualityCategoryName,
            }))
          )
        );
        Promise.all(reqs).then(results => {
          this.allQualities = results.flat();
        });
      });
    },
    loadQualityStock() {
      if (!this.form.sellQualityId) {
        this.qualityStock = null;
        return;
      }
      axios.get('/api/stock/quality/' + this.form.sellQualityId)
        .then(res => {
          this.qualityStock = res.data;
          const weights = res.data.available_piece_weights || [];
          if (weights.length === 1) {
            this.form.weightPerPiece = parseFloat(weights[0]);
            this.form.issuedPieces = 1;
          }
        })
        .catch(() => { this.qualityStock = null; });
    },
    isWeightSelected(weight) {
      return Math.abs(parseFloat(this.form.weightPerPiece || 0) - weight) <= 0.0005;
    },
    selectAvailableWeight(opt) {
      this.form.weightPerPiece = opt.weight;
      this.form.issuedPieces = 1;
    },
    validateIssueWeight() {
      if (
        !this.qualityStock ||
        !this.qualityStock.available_piece_weights ||
        !this.qualityStock.available_piece_weights.length
      ) {
        toastr.error('No stock for this item type. Purchase it first.');
        return false;
      }

      const perPiece = parseFloat(this.form.weightPerPiece || 0);
      const pieces = parseInt(this.form.issuedPieces || 0, 10);

      if (perPiece <= 0 || pieces <= 0) {
        toastr.info('Select an available weight and number of pieces.');
        return false;
      }

      const matchingCount = this.qualityStock.available_piece_weights.filter(
        weight => Math.abs(parseFloat(weight) - perPiece) <= 0.0005
      ).length;

      if (matchingCount < pieces) {
        toastr.error(
          'Only ' + matchingCount + ' pc at ' + perPiece.toFixed(3) +
          'g available for "' + this.qualityStock.quality_name + '". ' +
          'Available: ' + this.qualityStock.available_piece_weights_label + '.'
        );
        return false;
      }

      return true;
    },
    issueMetal() {
      if (!this.form.karigarId || !this.form.jobDate || !this.form.sellQualityId) {
        toastr.info('Karigar, date, and item type are required.');
        return;
      }
      if (!this.validateIssueWeight()) {
        return;
      }
      const perPiece = parseFloat(this.form.weightPerPiece);
      const pieces = parseInt(this.form.issuedPieces, 10);
      const totalWeight = Number((perPiece * pieces).toFixed(3));

      axios.post('/api/karigar/jobs/issue', {
        karigarId: this.form.karigarId,
        jobDate: this.form.jobDate,
        metalType: this.form.metalType,
        sellQualityId: this.form.sellQualityId,
        issuedWeightGrams: totalWeight,
        issuedPieces: pieces,
        itemDescription: this.form.itemDescription,
        notes: this.form.notes,
      }).then(res => {
        if (res.data.status === 1) {
          swal.fire('Success', res.data.message, 'success');
          this.resetForm();
          this.loadStockBalances();
          this.$emit('job-changed');
        } else {
          toastr.error(res.data.message);
        }
      }).catch(err => {
        const data = err.response?.data;
        const msg = data?.message
          || (data?.errors && Object.values(data.errors).flat()[0])
          || 'Issue failed.';
        toastr.error(msg);
      });
    },
    resetForm() {
      this.qualityStock = null;
      this.form = {
        karigarId: '',
        jobDate: getNowDateTime(),
        sellQualityId: '',
        metalType: 'gold',
        weightPerPiece: '',
        issuedPieces: 1,
        itemDescription: '',
        notes: '',
      };
    },
  },
};
</script>
