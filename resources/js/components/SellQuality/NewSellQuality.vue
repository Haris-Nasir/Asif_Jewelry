<template>
  <div>

    <div class="row">

      <!-- left column -->
      <div class="col-md-12 mt-3">

        <!-- New Sell Quality Form Elements -->
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">{{ $t('sellQuality.newTitle') }}</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <!-- /.card-header -->

          <div class="card-body">
            <div class="form-group row">
              <div class="col-md-2">
                <label for="qualitycategory" class="text-md col-form-label">{{ $t('common.category') }} <span class="required-mark"
                    style="color: red;">*</span></label>
              </div>
              <div class="col-md-4">
                <model-select :options="qualityCategories" v-model="selectedQualityCategory" :placeholder="$t('quality.selectCategory')">
                </model-select>
              </div>
            </div>

            <div class="form-group row">
              <div class="col-md-2">
                <label for="qualityname" class="text-md col-form-label">{{ $t('quality.qualityName') }} <span class="required-mark"
                    style="color: red;">*</span></label>
              </div>
              <div class="col-md-4">
                <input type="text" class="form-control" v-model="qualityName" maxlength="50"
                  :placeholder="$t('quality.phName')">
              </div>
            </div>
          </div>
          <!-- /.card-body -->

          <div class="card-footer">
            <button type="submit" v-on:click="addSellQuality" class="btn btn-primary text-md">{{ $t('common.add') }}</button>
            <button type="reset" v-on:click="resetFields" class="btn btn-primary ml-3 text-md">{{ $t('common.reset') }}</button>
          </div>
        </div>
        <!-- /.card -->

      </div>
      <!--/.col (right) -->

    </div>
    <!-- /.row -->
  </div>
</template>

<script>
  import toastr from 'toastr';
  import swal from 'sweetalert2';
  import { ModelSelect } from 'vue-search-select'

  toastr.options = {
    closeButton: true,
    closeDuration: 200,
    progressBar: true,
    newestOnTop: true,
    positionClass: "toast-top-center",
  };

  export default {
    name: "NewSellQuality",
    components: {
      ModelSelect
    },
    data() {
      return {
        qualityCategories: [],
        qualityName: '',
        status: null,
        message: null,
        errors: null,
        selectedQualityCategory: {
          value: '',
          text: ''
        },
      }
    },

    mounted() {
      this.loadQualityCategories();
    },

    methods: {

      loadQualityCategories() {
        axios.get('../api/sellqualitycategories').then((response) => {
          this.qualityCategories = response.data.qualityCategories.map(c => this.$categoryOption(c));
        }).catch(err => {
          console.log(err);
          toastr["error"](this.$t('common.somethingWrong'));
        })
      },

      addSellQuality() {
        var addData = {};
        addData["qualityName"] = this.qualityName;
        addData["qualityCategoryId"] = this.selectedQualityCategory.value;

        if ((this.selectedQualityCategory == '') || (this.qualityName == '')) {
          toastr["error"](this.$t('common.allFieldsRequired'));
        } else {
          axios
            .post("../api/sellquality/insert", addData)
            .then((res) => {

              if (res.data.status == -1) {
                var errormsg = res.data.errors;

                try {
                  if (errormsg.qualityName)
                    toastr["error"](errormsg.qualityName);
                } catch (err) { }

                try {
                  if (errormsg.qualityCategoryId)
                    toastr["error"](errormsg.qualityCategoryId);
                } catch (err) { }

              } else if (res.data.status == 0) {
                toastr["warning"](res.data.message);
              } else if (res.data.status == 1) {
                swal.fire({
                  title: this.$t('common.success'),
                  html: "<h5 style='color:#9C9794'>" + this.$t('sellQuality.added') + "</h5>",
                  icon: 'success'
                }).then((result) => {
                  this.resetFields();
                  this.$emit("refreshSellQualityTable");
                });
              }
            }).catch((err) => {
              console.log(err.response.data.message);
              toastr["error"](this.$t('common.somethingWrong'));
            });
        }
      },

      resetFields() {
        this.selectedQualityCategory = {};
        this.qualityName = '';
      }

    }
  };

</script>

<style scoped>
  /* The below thing is mandatory no need to remove it this thing is for reference.*/
  /* .select2-container--default .select2-selection--single .select2-selection__arrow{
    height: 34px;
  }

  .select2-container .select2-selection--single{
    height: 100px;
  } */
</style>