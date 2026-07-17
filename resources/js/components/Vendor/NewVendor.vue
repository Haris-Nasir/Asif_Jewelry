<template>
  <div>
    <div class="row">
      <div class="col-md-12">
        <div class="card card-primary mt-3">
          <div class="card-header">
            <h3 class="card-title">{{ $t('vendor.newTitle') }}</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
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
            <button type="submit" class="btn btn-primary" @click="addVendor">
              {{ $t('common.add') }}
            </button>
            <button
              type="reset"
              class="btn btn-primary"
              @click="resetNewVendor"
            >
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

toastr.options = {
  closeButton: true,
  closeDuration: 200,
  progressBar: true,
  newsOnTop: true,
  positionClass: "toast-top-center",
};

export default {
  name: "NewVendor",
  data() {
    return {
      companyName: "",
      companyContact: "",
      companyAddress: "",
      emailAddress: "",
      gstNumber: "",
      gstCode: "",
    };
  },
  methods: {
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

    addVendor: function () {
      if (
        this.validateCompanyName() &&
        this.validateCompanyContact() &&
        this.validateCompanyAddress() &&
        this.validateEmailAddress() &&
        this.validateGSTNumber() &&
        this.validateGSTCode()
      ) {
        let payload = {
          companyName: this.companyName,
          companyContact: this.companyContact,
          companyAddress: this.companyAddress,
          emailAddress: this.emailAddress,
          gstNumber: this.gstNumber,
          gstCode: this.gstCode,
        };
        axios
          .post("/api/vendor", payload)
          .then((res) => {
            if (res.data.status == 1) {
              swal
                .fire({
                  title: this.$t('common.success'),
                  html: "<h5 style='color:#9C9794'>" + this.$t('vendor.added') + "</h5>",
                  icon: "success",
                })
                .then(() => {
                  this.resetNewVendor();
                  this.$emit("refreshVendorsTable");
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

    resetNewVendor: function () {
      this.companyName = "";
      this.companyContact = "";
      this.companyAddress = "";
      this.emailAddress = "";
      this.gstNumber = "";
      this.gstCode = "";
    },
  },
};
</script>

