<template>
  <div>
    <div class="row">
      <div class="col-md-12 mt-3">
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">{{ $t('credit.manageTitle') }}</h3>
            <div class="card-tools">
              <button
                type="button"
                class="btn btn-tool"
                data-card-widget="collapse"
              >
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>

          <div class="card-body table-responsive">
            <div
              class="d-flex justify-content-between align-content-center mb-2"
            >
              <div class="d-flex">
                <div>
                  <div class="d-flex align-items-center ml-4">
                    <label for="paginate" class="text-nowrap mr-2 mb-0">
                      {{ $t('common.perPage') }}
                    </label>
                    <select
                      v-model="paginate"
                      class="form-control form-control-sm"
                    >
                      <option value="10">10</option>
                      <option value="20">20</option>
                      <option value="30">30</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <input
                  v-model="search"
                  type="search"
                  class="form-control"
                  :placeholder="$t('common.searchBy')"
                />
              </div>
            </div>

            <div class="p-0">
              <table
                class="table table-hover table-bordered table-striped table-sm"
              >
                <thead class="text-md">
                  <tr>
                    <th width="10%">
                      <a href="#" @click.prevent="updateSorting('credit_id')"
                        >{{ $t('common.srNo') }}</a
                      >
                      <span v-if="sort_field == 'credit_id' ? 1 : 0">
                        <span v-if="sort_direction == 'asc' ? 1 : 0"
                          >&uarr;</span
                        >
                        <span v-if="sort_direction == 'desc' ? 1 : 0"
                          >&darr;</span
                        >
                      </span>
                    </th>
                    <th>
                      <a href="#" @click.prevent="updateSorting('credit_date')"
                        >{{ $t('credit.date') }}</a
                      >
                      <span v-if="sort_field == 'credit_date' ? 1 : 0">
                        <span v-if="sort_direction == 'asc' ? 1 : 0"
                          >&uarr;</span
                        >
                        <span v-if="sort_direction == 'desc' ? 1 : 0"
                          >&darr;</span
                        >
                      </span>
                    </th>
                    <th width="20%">{{ $t('credit.description') }}</th>
                    <th>
                      <a
                        href="#"
                        @click.prevent="updateSorting('credit_amount')"
                        >{{ $t('credit.amount') }}</a
                      >
                      <span v-if="sort_field == 'credit_amount' ? 1 : 0">
                        <span v-if="sort_direction == 'asc' ? 1 : 0"
                          >&uarr;</span
                        >
                        <span v-if="sort_direction == 'desc' ? 1 : 0"
                          >&darr;</span
                        >
                      </span>
                    </th>
                    <th width="110" class="text-center">{{ $t('common.action') }}</th>
                  </tr>
                </thead>
                <tbody class="text-md">
                  <tr
                    v-for="detail in details.data"
                    v-bind:key="detail.credit_id"
                  >
                    <td>
                      {{ detail.credit_id }}
                    </td>
                    <td>
                      {{ detail.credit_date }}
                    </td>
                    <td>
                      {{ detail.credit_description }}
                    </td>
                    <td>
                      {{ detail.credit_amount }}
                    </td>
                    <td class="text-center">
                        <div class="table-actions"><button type="button"
                        class="btn btn-primary btn-sm"
                        @click="
                          updateDetailBtn(
                            detail.credit_id,
                            detail.credit_date,
                            detail.credit_description,
                            detail.credit_amount
                          )
                        "
                      >
                        <i class="fas fa-pen"></i>
                      </button>

                      <button type="button"
                        class="btn btn-danger btn-sm"
                        @click="deleteDetail(detail.credit_id)"
                      >
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
                <pagination
                  :data="details"
                  @pagination-change-page="getAllCreditDetails"
                ></pagination>
              </div>
            </div>
          </div>
        </div>

        <div v-if="creditId == -1 ? 0 : 1" class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">{{ $t('credit.updateTitle') }}</h3>
            <div class="card-tools">
              <button
                type="button"
                class="btn btn-tool"
                data-card-widget="collapse"
              >
                <i class="fas fa-minus"></i>
              </button>
              <button
                type="button"
                class="btn btn-tool"
                @click="closeEditCredit"
              >
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="form-group" style="display: flex; flex-direction: row">
              <label for="creditDate" class="col-md-2 text-md"
                >{{ $t('credit.date') }}
                <span class="required-mark" style="color: red">*</span></label
              >
              <input
                type="date"
                class="form-control col-md-2"
                v-model="creditDate"
                :placeholder="$t('credit.phDate')"
              />
              <div class="col-md-2"></div>
              <label for="creditAmount" class="col-md-2 text-md"
                >{{ $t('credit.amount') }}
                <span class="required-mark" style="color: red">*</span></label
              >
              <input
                type="text"
                class="form-control col-md-3"
                v-model="creditAmount"
                :placeholder="$t('credit.phAmount')"
              />
            </div>
            <div class="form-group" style="display: flex; flex-direction: row">
              <label for="creditDesc" class="col-md-2 text-md"
                >{{ $t('credit.description') }}
                <span class="required-mark" style="color: red">*</span></label
              >
              <textarea
                class="form-control col-md-3"
                v-model="creditDesc"
                :placeholder="$t('credit.phDesc')"
              ></textarea>
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-primary" @click="updateDetailsSaveUpdate">
              {{ $t('common.update') }}
            </button>
            <button class="btn btn-primary" @click="resetUpdateDetail">
              {{ $t('common.reset') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import toastr from "toastr";
import swal from "sweetalert2";

export default {
  name: "SMCredit",
  data() {
    return {
      details: {},
      paginate: "10",
      search: "",
      sort_direction: "asc",
      sort_field: "credit_id",
      creditId: -1,
      creditDate: "",
      creditDesc: "",
      creditAmount: "",
      status: null,
      message: null,
      errors: null,
    };
  },
  mounted() {
    this.getAllCreditDetails();
  },
  watch: {
    paginate: function () {
      this.getAllCreditDetails();
    },
    search: function () {
      this.getAllCreditDetails();
    },
  },
  methods: {
    getStdDate: function (date) {
      date = date.split("-");

      return date[2] + "-" + date[1] + "-" + date[0];
    },

    getAllCreditDetails: function (page = 1) {
      axios
        .get(
          "/api/credits?page=" +
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
          this.details = response.data;
        });
    },

    updateSorting: function (field) {
      if (this.sort_field == field) {
        this.sort_direction = this.sort_direction == "asc" ? "desc" : "asc";
      } else {
        this.sort_field = field;
      }
      this.getAllCreditDetails(this.details.current_page);
    },

    updateDetailBtn: function (
      credit_id,
      credit_date,
      credit_description,
      credit_amount
    ) {
      toastr.info(this.$t('common.scrollDown'));
      this.creditId = credit_id;
      this.creditDate = this.getStdDate(credit_date);
      this.creditDesc = credit_description;
      this.creditAmount = credit_amount;
    },

    updateDetailsSaveUpdate: function () {
      if (
        this.creditDate == "" ||
        this.creditDesc == "" ||
        this.creditAmount == ""
      ) {
        toastr["error"](this.$t('common.allFieldsRequired'));
      } else {
        axios
          .put("/api/credit/update/" + this.creditId, {
            creditDateDetail: this.creditDate,
            creditDescDetail: this.creditDesc,
            creditAmountDetail: this.creditAmount,
          })
          .then((res) => {
            if (res.data.status == 1) {
              swal
                .fire({
                  title: this.$t('common.success'),
                  html: "<h5 style='color:#9C9794'>" + this.$t('credit.updated') + "</h5>",
                  icon: "success",
                })
                .then((result) => {
                  this.creditId = -1;
                  this.creditDate = "";
                  this.creditDesc = "";
                  this.creditAmount = "";
                  this.getAllCreditDetails(this.details.current_page);
                });
            } else if (res.data.status == 0) {
              toastr.info(res.data.message);
            }
          })
          .catch((err) => {
            toastr.error(this.$t('common.somethingWrong'));
          });
      }
    },

    resetUpdateDetail: function () {
      this.creditDate = "";
      this.creditDesc = "";
      this.creditAmount = "";
    },

    closeEditCredit: function () {
      this.creditId = -1;
    },

    deleteDetail: function (credit_id) {
      axios
        .delete("/api/credit/delete/" + credit_id)
        .then((res) => {
          swal
            .fire({
              title: this.$t('common.success'),
              html: "<h5 style='color:#9C9794'>" + this.$t('credit.deleted') + "</h5>",
              icon: "success",
            })
            .then((result) => {
              this.$emit("refreshDetailsTable");
              this.getAllCreditDetails(this.details.current_page);
            });
        })
        .catch((err) => {
          toastr.error(res.error.message);
          console.log(res.error.message);
        });
    },
  },
};
</script>