<template>
  <div>
    <!-- Content Wrapper. Contains page content -->
    <div class="row">
      <!-- left column -->
      <div class="col-md-12 mt-3">
        <!-- Search and Manage Sell Quality-->
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">
              {{ $t('sellQuality.manageTitle') }}
            </h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <!-- /.card-header -->

          <div class="card-body table-responsive">
            <div class="d-flex justify-content-between align-content-center mb-2">
              <div class="d-flex">
                <div>
                  <div class="d-flex align-items-center ml-4">
                    <label for="paginate" class="text-nowrap mr-2 mb-0 text-md">
                      {{ $t('common.perPage') }}
                    </label>
                    <select v-model="paginate" class="form-control form-control-sm">
                      <option value="10">10</option>
                      <option value="20">20</option>
                      <option value="30">30</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <input v-model="search" type="search" class="form-control " :placeholder="$t('common.searchBy')" />
              </div>
            </div>

            <div class="p-0">
              <table class="table table-hover table-bordered table-striped table-sm">
                <thead class="text-md">
                  <tr>
                    <th width="10%">
                      <a href="#" @click.prevent="updateSorting('sell_quality_id')">{{ $t('common.srNo') }}</a>
                      <span v-if="sort_field == 'sell_quality_id' ? 1 : 0">
                        <span v-if="sort_direction == 'asc' ? 1 : 0">&uarr;</span>
                        <span v-if="sort_direction == 'desc' ? 1 : 0">&darr;</span>
                      </span>
                    </th>
                    <th>
                      <a href="#" @click.prevent="updateSorting('quality_name')">{{ $t('quality.qualityName') }}</a>
                      <span v-if="sort_field =='quality_name'? 1: 0">
                        <span v-if="sort_direction == 'asc'? 1: 0">&uarr;</span>
                        <span v-if="sort_direction == 'desc'? 1: 0">&darr;</span>
                      </span>
                    </th>
                    <th>
                      <a href="#" @click.prevent="updateSorting('sell_category_name')">{{ $t('common.category') }}</a>
                      <span v-if="sort_field =='sell_category_name'? 1: 0">
                        <span v-if="sort_direction == 'asc'? 1: 0">&uarr;</span>
                        <span v-if="sort_direction == 'desc'? 1: 0">&darr;</span>
                      </span>
                    </th>
                    <th width="110" class="text-center">{{ $t('common.action') }}</th>
                  </tr>
                </thead>
                <tbody class="text-md">
                  <tr v-for="sellquality in sellqualities.data" v-bind:key="sellquality.sell_quality_id">
                    <td>{{ sellquality.sell_quality_id }}</td>
                    <td>{{ $label(sellquality.quality_name) }}</td>
                    <td>{{ $label(sellquality.sell_category_name) }}</td>

                    <td class="text-center">
                        <div class="table-actions"><button type="button" class="btn btn-primary btn-sm"
                        @click="editSellQuality(sellquality.sell_quality_id, sellquality.sell_quality_category_id,sellquality.sell_category_name,sellquality.quality_name)">
                        <i class="fas fa-pen"></i>
                      </button>

                      <button type="button" class="btn btn-danger btn-sm"
                        @click="deleteSellQuality(sellquality.sell_quality_id)">
                        <i class="fas fa-trash"></i>
                      </button>
                        </div>
                      </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="row mt-4">
              <div class="col-sm-6 offset-5">
                <pagination :data="sellqualities" @pagination-change-page="getAllSellQualities"></pagination>
              </div>
            </div>
          </div>
        </div>

        <div v-if="sellQualityId == -1 ? 0 : 1" class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">{{ $t('sellQuality.editTitle') }}</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
              <button type="button" class="btn btn-tool" @click="closeEditSellQuality">
                <i class="fas fa-times"></i>
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
                <model-select :options="qualityCategories" v-model="selectedQualityCategory"
                  :placeholder="$t('quality.selectCategory')">
                </model-select>
              </div>
            </div>

            <div class="form-group row">
              <div class="col-md-2">
                <label for="qualityname" class="text-md col-form-label">{{ $t('quality.qualityName') }} <span class="required-mark"
                    style="color: red;">*</span></label>
              </div>
              <div class="col-md-4">
                <input type="text" class="form-control" v-model="editQualityName" maxlength="50"
                  :placeholder="$t('quality.phName')">
              </div>
            </div>
          </div>
          <!-- /.card-body -->

          <div class="card-footer">
            <button type="submit" @click="updateSellQuality" class="btn btn-primary text-md">{{ $t('common.update') }}</button>
          </div>
        </div>
      </div>
      <!--/.col (right) -->
    </div>
    <!-- /.row -->
  </div>
</template>

<script>
  import toastr from "toastr";
  import swal from "sweetalert2";
  import { ModelSelect } from 'vue-search-select'

  export default {
    name: "SMSellQuality",
    components: {
      ModelSelect
    },
    data() {
      return {
        sellqualities: {},
        paginate: "10",
        search: "",
        sort_direction: "asc",
        sort_field: "sell_quality_id",
        sellQualityId: -1,
        editQualityName: "",
        qualityCategories: [],
        selectedQualityCategory: "",
        status: null,
        message: null,
        errors: null
      };
    },
    mounted() {
      this.getAllSellQualities();
      this.loadQualityCategories();
    },
    watch: {
      paginate: function () {
        this.getAllSellQualities();
      },
      search: function () {
        this.getAllSellQualities();
      }
    },
    methods: {
      getAllSellQualities: function (page = 1) {
        axios
          .get(
            "../api/sellqualities?page=" +
            page +
            "&paginate=" +
            this.paginate +
            "&search=" +
            this.search +
            "&sortdirection=" +
            this.sort_direction +
            "&sortfield=" +
            this.sort_field
          )
          .then((response, err) => {
            if (err) {
            }
            this.sellqualities = response.data;
          });
      },

      updateSorting: function (field) {
        if (this.sort_field == field) {
          this.sort_direction =
            this.sort_direction == "asc" ? "desc" : "asc";
        } else {
          this.sort_field = field;
        }
        this.getAllSellQualities(this.sellqualities.current_page);
      },

      editSellQuality: function (sell_quality_id, sell_quality_category_id, sell_category_name, quality_name) {
        this.sellQualityId = sell_quality_id;
        this.selectedQualityCategory = sell_quality_category_id;
        this.editQualityName = quality_name;
      },

      loadQualityCategories: function () {
        axios.get('../api/sellqualitycategories').then((response) => {
          this.qualityCategories = response.data.qualityCategories.map(c => this.$categoryOption(c));
        }).catch(err => {
          console.log(err);
          toastr["error"](this.$t('common.somethingWrong'));
        })
      },

      updateSellQuality: function () {
        if ((this.selectedQualityCategory == '') || (this.editQualityName == '')) {
          toastr["error"](this.$t('common.allFieldsRequired'));
        } else {
          axios
            .put('../api/sellquality/update/' + this.sellQualityId, {
              editQualityName: this.editQualityName,
              editQualityCategoryId: this.selectedQualityCategory
            })
            .then((res) => {
              if (res.data.status == -1) {
                var errormsg = res.data.errors;

                try {
                  if (errormsg.editQualityName)
                    toastr["error"](errormsg.editQualityName);
                } catch (err) { }

                try {
                  if (errormsg.editQualityCategoryId)
                    toastr["error"](errormsg.editQualityCategoryId);
                } catch (err) { }

              } else if (res.data.status == 0) {
                toastr["warning"](res.data.message);
              } else if (res.data.status == 1) {
                swal.fire({
                  title: this.$t('common.success'),
                  html: "<h5 style='color:#9C9794'>" + this.$t('sellQuality.updated') + "</h5>",
                  icon: 'success'
                }).then((result) => {
                  this.resetFields();
                  this.sellQualityId = -1;
                  this.getAllSellQualities(this.sellqualities.current_page);
                });
              }
            }).catch((err) => {
              console.log(err.res.data.message);
              toastr["error"](this.$t('common.somethingWrong'));
            });
        }
      },

      deleteSellQuality: function (sell_quality_id) {
        axios
          .delete('../api/sellquality/delete/' + sell_quality_id)
          .then((res) => {
            swal.fire({
              title: this.$t('common.success'),
              html:
                "<h5 style='color:#9C9794'>" + this.$t('sellQuality.deleted') + "</h5>",
              icon: "success"
            }).then((result) => {
              this.getAllSellQualities(this.sellqualities.current_page);
            });
          })
          .catch((err) => {
            console.log(err.res.data.message);
            toastr["error"](this.$t('common.somethingWrong'));
          });
      },

      resetFields: function () {
        this.selectedQualityCategory = {};
        this.editQualityName = '';
      },

      closeEditSellQuality: function () {
        this.sellQualityId = -1;
        this.resetFields();
      }
    }
  };
</script>