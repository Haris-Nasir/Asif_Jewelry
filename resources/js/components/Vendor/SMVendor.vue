<template>
  <div>
    <div class="row">
      <!-- left column -->
      <div class="col-md-12 mt-3">
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">{{ $t('vendor.manageTitle') }}</h3>
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
          <!-- /.card-header -->

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
                    <th>
                      <a href="#" @click.prevent="updateSorting('vendor_id')"
                        >{{ $t('common.srNo') }}</a
                      >
                      <span v-if="sort_field == 'vendor_id' ? 1 : 0">
                        <span v-if="sort_direction == 'asc' ? 1 : 0"
                          >&uarr;</span
                        >
                        <span v-if="sort_direction == 'desc' ? 1 : 0"
                          >&darr;</span
                        >
                      </span>
                    </th>
                    <th>
                      <a
                        href="#"
                        @click.prevent="updateSorting('vendor_company_name')"
                        >{{ $t('vendor.name') }}</a
                      >
                      <span v-if="sort_field == 'vendor_company_name' ? 1 : 0">
                        <span v-if="sort_direction == 'asc' ? 1 : 0"
                          >&uarr;</span
                        >
                        <span v-if="sort_direction == 'desc' ? 1 : 0"
                          >&darr;</span
                        >
                      </span>
                    </th>
                    <th>
                        {{ $t('common.contactNo') }}
                    </th>
                    <th>{{ $t('common.email') }}
                    </th>
                    <th>{{ $t('common.gstNo') }}
                    </th>
                    <th>{{ $t('common.gstCode') }}
                    </th>
                    <th>{{ $t('common.address') }}
                    </th>
                    <th width="110" class="text-center">{{ $t('common.action') }}</th>
                  </tr>
                </thead>
                <tbody class="text-md">
                  <tr
                    v-for="vendor in vendors.data"
                    v-bind:key="vendor.vendor_id"
                  >
                    <td>{{ vendor.vendor_id }}</td>
                    <td>{{ vendor.vendor_company_name }}</td>
                    <td>{{ vendor.vendor_contact_no }}</td>
                    <td>{{ vendor.vendor_email }}</td>
                    <td>{{ vendor.vendor_gst_no }}</td>
                    <td>{{ vendor.vendor_gst_code }}</td>
                    <td>{{ vendor.vendor_address }}</td>

                    <td class="text-center">
                        <div class="table-actions"><button type="button"
                        class="btn btn-primary btn-sm"
                        @click="
                          editVendorBtn(
                            vendor.vendor_id,
                            vendor.vendor_company_name,
                            vendor.vendor_contact_no,
                            vendor.vendor_email,
                            vendor.vendor_gst_no,
                            vendor.vendor_gst_code,
                            vendor.vendor_address
                          )
                        "
                      >
                        <i class="fas fa-pen"></i>
                      </button>

                      <button type="button"
                        class="btn btn-danger btn-sm"
                        @click="deleteVendor(vendor.vendor_id)"
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
                  :data="vendors"
                  @pagination-change-page="getAllVendors"
                ></pagination>
              </div>
            </div>
          </div>
        </div>

        <div v-if="vendorId == -1 ? 0 : 1" class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">{{ $t('vendor.updateTitle') }}</h3>
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
                @click="closeEditVendor"
              >
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <div
              class="form-group"
              style="
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: center;
              "
            >
              <label for="companyName" class="text-md col-md-2">{{ $t('vendor.name') }} <span class="required-mark" style="color: red;">*</span></label>
              <input
                type="text"
                class="form-control col-md-5"
                v-model="companyName"
                :placeholder="$t('vendor.phName')"
              />
              <div class="col-md-1"></div>
              <label for="companyContact" class="text-md col-md-2"
                >{{ $t('common.contactNo') }}</label
              >
              <input
                type="tel"
                class="form-control col-md-2"
                v-model="companyContact"
                maxlength="11"
                :placeholder="$t('customer.phContact')"
              />
            </div>
            <div
              class="form-group"
              style="
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: center;
              "
            >
              <label for="companyAddress" class="text-md col-md-2"
                >{{ $t('common.companyAddress') }}</label
              >
              <textarea
                class="form-control col-md-10"
                v-model="companyAddress"
                :placeholder="$t('customer.phAddress')"
              ></textarea>
            </div>
            <div
              class="form-group"
              style="
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: center;
              "
            >
              <label for="emailAddress" class="text-md col-md-2">{{ $t('common.emailAddress') }}</label>
              <input
                type="email"
                class="form-control col-md-2"
                v-model="emailAddress"
                :placeholder="$t('customer.phEmail')"
              />
              <div class="col-md-1"></div>
              <label for="gstNumber" class="text-md col-md-1">{{ $t('common.gstNo') }}</label>
              <input
                type="text"
                class="form-control col-md-2"
                v-model="gstNumber"
                :placeholder="$t('customer.phGst')"
              />
              <div class="col-md-1"></div>
              <label for="gstCode" class="text-md col-md-1">{{ $t('common.gstCode') }}</label>
              <input
                type="text"
                class="form-control col-md-2"
                v-model="gstCode"
                :placeholder="$t('customer.phGstCode')"
              />
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-primary" @click="UpdateVendor">
              {{ $t('common.update') }}
            </button>
            <button class="btn btn-primary" @click="ResetUpdateVendor">
              {{ $t('common.reset') }}
            </button>
          </div>
        </div>
      </div>
      <!--/.col (right) -->
    </div>
  </div>
</template>

<script>
import toastr from "toastr";
import swal from "sweetalert2";

export default {
  name: "SMVendor",
  data() {
    return {
      vendors: {},
      paginate: "10",
      search: "",
      sort_direction: "desc",
      sort_field: "vendor_id",
      vendorId: -1,
      companyName: "",
      companyContact: "",
      companyAddress: "",
      emailAddress: "",
      gstNumber: "",
      gstCode: "",
    };
  },
  mounted() {
    this.getAllVendors();
  },
  watch: {
    paginate: function () {
      this.getAllVendors();
    },
    search: function () {
      this.getAllVendors();
    },
  },
  methods: {
    getAllVendors: function (page = 1) {
      axios
        .get(
          "/api/vendors?page=" +
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
          this.vendors = response.data;
        });
    },

    updateSorting: function (field) {
      if (this.sort_field == field) {
        this.sort_direction = this.sort_direction == "asc" ? "desc" : "asc";
      } else {
        this.sort_field = field;
      }
      this.getAllVendors(this.vendors.current_page);
    },

    editVendorBtn: function (
      vendor_id,
      vendor_company_name,
      vendor_contact_no,
      vendor_email,
      vendor_gst_no,
      vendor_gst_code,
      vendor_address
    ) {
      toastr.info(this.$t('common.scrollDown'));
      this.vendorId = vendor_id;
      this.companyName = vendor_company_name;
      this.companyContact = vendor_contact_no;
      this.companyAddress = vendor_address;
      this.emailAddress = vendor_email;
      this.gstNumber = vendor_gst_no;
      this.gstCode = vendor_gst_code;
    },

    validateCompanyName: function () {
      if (this.companyName == "") {
        toastr.info(this.$t('vendor.nameRequired'));
        return false;
      } else if (this.companyName.length > 50) {
        toastr.warning(this.$t('vendor.nameMax'));
        return false;
      } else {
        return true;
      }
    },

    validateCompanyContact: function () {
      if (this.companyContact === "") {
        return true;
      } else if (this.companyContact.length < 10 || this.companyContact.length > 11) {
        toastr.warning(this.$t('customer.contactDigits'));
        return false;
      } else {
        return true;
      }
    },

    validateCompanyAddress: function () {
      if (this.companyAddress === "") {
        return true;
      } else if (this.companyAddress.length > 255) {
        toastr.warning(this.$t('customer.addressMax'));
        return false;
      } else {
        return true;
      }
    },

    validateEmailAddress: function () {
      if (this.emailAddress.length > 255) {
        toastr.warning(this.$t('customer.emailMax'));
        return false;
      } 
      
      if (this.emailAddress != "") {
        if (!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(this.emailAddress))) 
        {
          toastr.warning(this.$t('customer.emailInvalid'));
          return false;
        }
      }
      return true;
    },

    validateGSTNumber: function () {
      if (this.gstNumber === "") {
        return true;
      }
      if (this.gstNumber.length != 15) {
        toastr.warning(this.$t('customer.gstLen'));
        return false;
      }
      return true;
    },

    validateGSTCode: function () {
      if (this.gstCode === "") {
        return true;
      }
      if (this.gstCode.length != 2) {
        toastr.warning(this.$t('customer.gstCodeLen'));
        return false;
      }
      if (this.gstNumber !== "" && this.gstCode !== this.gstNumber.substring(0, 2)) {
        toastr.warning(this.$t('customer.gstCodeMatch'));
        return false;
      }
      return true;
    },

    UpdateVendor: function () {
      if (
        this.validateCompanyName() &&
        this.validateCompanyContact() &&
        this.validateCompanyAddress() &&
        this.validateEmailAddress() &&
        this.validateGSTNumber() &&
        this.validateGSTCode()
      ) {
        axios
          .put("/api/vendor/update/" + this.vendorId, {
            companyName: this.companyName,
            companyContact: this.companyContact,
            companyAddress: this.companyAddress,
            emailAddress: this.emailAddress,
            gstNumber: this.gstNumber,
            gstCode: this.gstCode,
          })
          .then((res) => {
            if (res.data.status == 1) {
              swal
                .fire({
                  title: this.$t('common.success'),
                  html: "<h5 style='color:#9C9794'>" + this.$t('vendor.updated') + "</h5>",
                  icon: "success",
                })
                .then((result) => {
                  this.vendorId = -1;
                  this.ResetUpdateVendor();
                  this.$emit("refreshVendorsTable");
                  this.getAllVendors(this.vendors.current_page);
                });
            } else if (res.data.status == 0) {
              toastr.info(res.data.message);
            } else {
              toastr.error(res.data.message);
            }
          })
          .catch((err) => {
            toastr.error(res.error.message);
            console.log(res.error.message);
          });
      }
    },

    ResetUpdateVendor: function () {
      this.companyName = "";
      this.companyContact = "";
      this.companyAddress = "";
      this.emailAddress = "";
      this.gstNumber = "";
      this.gstCode = "";
    },

    closeEditVendor: function () {
      this.vendorId = -1;
    },

    deleteVendor: function (vendor_id) {
      axios
        .delete("/api/vendor/delete/" + vendor_id)
        .then((res) => {
          swal
            .fire({
              title: this.$t('common.success'),
              html: "<h5 style='color:#9C9794'>" + this.$t('vendor.deleted') + "</h5>",
              icon: "success",
            })
            .then((result) => {
              this.getAllVendors(this.vendors.current_page);
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
