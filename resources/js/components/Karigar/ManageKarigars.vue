<template>
  <div>
    <div class="row">
      <div class="col-md-12">
        <div class="card card-primary mt-3">
          <div class="card-header">
            <h3 class="card-title">{{ $t('karigar.craftsmanTitle') }}</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="form-group row">
              <label class="col-md-2 col-form-label">{{ $t('common.name') }} <span class="required-mark" style="color:red">*</span></label>
              <div class="col-md-4">
                <input type="text" class="form-control" v-model="form.karigarName" :placeholder="$t('karigar.phName')">
              </div>
              <label class="col-md-2 col-form-label">{{ $t('common.contact') }}</label>
              <div class="col-md-4">
                <input type="tel" class="form-control" v-model="form.contactNo" maxlength="11">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label">{{ $t('common.address') }}</label>
              <div class="col-md-10">
                <textarea class="form-control" v-model="form.address" rows="2"></textarea>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-primary" @click="saveKarigar">{{ editId ? $t('common.update') : $t('common.add') }}</button>
            <button class="btn btn-secondary ml-2" @click="resetForm">{{ $t('common.reset') }}</button>
          </div>
        </div>

        <div class="card card-secondary mt-3">
          <div class="card-header">
            <h3 class="card-title">{{ $t('karigar.listTitle') }}</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body table-responsive">
            <table class="table table-bordered table-sm">
              <thead>
                <tr><th>{{ $t('common.name') }}</th><th>{{ $t('common.contact') }}</th><th>{{ $t('common.address') }}</th><th width="120">{{ $t('common.action') }}</th></tr>
              </thead>
              <tbody>
                <tr v-for="k in karigars.data" :key="k.karigar_id">
                  <td>{{ k.karigar_name }}</td>
                  <td>{{ k.contact_no || '-' }}</td>
                  <td>{{ k.address || '-' }}</td>
                  <td class="text-center">
                    <div class="table-actions">
                      <button type="button" class="btn btn-sm btn-primary" :title="$t('common.edit')" @click="editKarigar(k)"><i class="fas fa-pen"></i></button>
                      <button type="button" class="btn btn-sm btn-danger" :title="$t('common.delete')" @click="deleteKarigar(k.karigar_id)"><i class="fas fa-trash"></i></button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
            <pagination :data="karigars" @pagination-change-page="loadKarigars"></pagination>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import toastr from 'toastr';
import swal from 'sweetalert2';

export default {
  name: 'ManageKarigars',
  data() {
    return {
      karigars: {},
      editId: null,
      form: { karigarName: '', contactNo: '', address: '' },
    };
  },
  mounted() { this.loadKarigars(); },
  methods: {
    loadKarigars(page = 1) {
      axios.get('/api/karigar?page=' + page + '&paginate=10').then(res => {
        this.karigars = res.data;
      });
    },
    saveKarigar() {
      if (!this.form.karigarName.trim()) {
        toastr.info(this.$t('karigar.nameRequired'));
        return;
      }
      const payload = { karigarName: this.form.karigarName, contactNo: this.form.contactNo, address: this.form.address };
      const req = this.editId
        ? axios.put('/api/karigar/' + this.editId, payload)
        : axios.post('/api/karigar', payload);
      req.then(res => {
        if (res.data.status === 1) {
          toastr.success(res.data.message);
          this.resetForm();
          this.loadKarigars();
          this.$emit('karigars-changed');
        } else {
          toastr.error(res.data.message || this.$t('karigar.saveFail'));
        }
      }).catch(() => toastr.error(this.$t('karigar.saveFail')));
    },
    editKarigar(k) {
      this.editId = k.karigar_id;
      this.form.karigarName = k.karigar_name;
      this.form.contactNo = k.contact_no || '';
      this.form.address = k.address || '';
    },
    deleteKarigar(id) {
      swal.fire({ title: this.$t('karigar.deleteConfirm'), icon: 'warning', showCancelButton: true }).then(r => {
        if (r.isConfirmed) {
          axios.delete('/api/karigar/' + id).then(() => {
            toastr.success(this.$t('karigar.deleted'));
            this.loadKarigars();
            this.$emit('karigars-changed');
          });
        }
      });
    },
    resetForm() {
      this.editId = null;
      this.form = { karigarName: '', contactNo: '', address: '' };
    },
  },
};
</script>
