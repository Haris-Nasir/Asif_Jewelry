<template>
  <div>
    <div class="row">
      <div class="col-md-12 mt-3">
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">{{ $t('credit.newTitle') }}</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="form-group" style="display: flex; flex-direction: row">
              <label for="creditDate" class="col-md-2 text-md">{{ $t('credit.date') }} <span class="required-mark"
                  style="color: red;">*</span></label>
              <input type="date" class="form-control col-md-2" v-model="creditDate"
                :placeholder="$t('credit.phDate')" />
              <div class="col-md-2"></div>
              <label for="creditAmount" class="col-md-2 text-md">{{ $t('credit.amount') }} <span class="required-mark"
                  style="color: red;">*</span></label>
              <input type="text" class="form-control col-md-3" v-model="creditAmount" :placeholder="$t('credit.phAmount')" />
            </div>
            <div class="form-group" style="display: flex; flex-direction: row">
              <label for="creditDesc" class="col-md-2 text-md">{{ $t('credit.description') }} <span class="required-mark"
                  style="color: red;">*</span></label>
              <textarea class="form-control col-md-3" v-model="creditDesc"
                :placeholder="$t('credit.phDesc')"></textarea>
            </div>
          </div>
          <div class="card-footer">
            <button type="submit" v-on:click="addCredit" class="btn btn-primary">
              {{ $t('common.add') }}
            </button>
            <button type="reset" v-on:click="resetFields" class="btn btn-primary">
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
    newestOnTop: true,
    positionClass: "toast-top-center",
  };

  export default {
    name: "NewCredit",
    data() {
      return {
        creditDate: "",
        creditDesc: "",
        creditAmount: "",
        status: null,
        message: null,
        errors: null,
      };
    },

    mounted() {
      this.creditDate = this.getTodaysDate();
    },

    methods: {
      getTodaysDate: function () {
        let d = new Date();
        let month = "" + (d.getMonth() + 1);
        let day = "" + d.getDate();
        let year = d.getFullYear();
        if (month < 10) {
          month = "0" + month;
        }

        if (day < 10) {
          day = "0" + day;
        }

        return year + "-" + month + "-" + day;
      },

      addCredit() {
        if (
          this.creditDate == "" ||
          this.creditDesc == "" ||
          this.creditAmount == ""
        ) {
          toastr["error"](this.$t('common.allFieldsRequired'));
        } else {
          let payload = {
            creditDate: this.creditDate,
            creditDesc: this.creditDesc,
            creditAmount: this.creditAmount,
          };
          axios
            .post("../api/credit/insert", payload)
            .then((response) => {
              if (response.data.status == -1) {
                var errormsg = response.data.errors;
                try {
                  if (errormsg.creditDate)
                    toastr["error"](this.$t('credit.invalidDate'));
                } catch (err) { }

                try {
                  if (errormsg.creditDesc)
                    toastr["error"](this.$t('credit.invalidDesc'));
                } catch (err) { }

                try {
                  if (errormsg.creditAmount)
                    toastr["error"](this.$t('credit.invalidAmount'));
                } catch (err) { }
              } else if (response.data.status == 0) {
                toastr["warning"](response.data.message);
              } else if (response.data.status == 1) {
                swal
                  .fire({
                    title: this.$t('common.success'),
                    html: "<h5 style='color:#9C9794'>" + this.$t('credit.added') + "</h5>",
                    icon: "success",
                  })
                  .then((result) => {
                    this.resetFields();
                    this.$emit("refreshDetailsTable");
                  });
              }
            })
            .catch((err) => {
              console.log(err.response.data.message);
              toastr["error"](this.$t('common.somethingWrong'));
            });
        }
      },

      resetFields() {
        this.creditDate = "";
        this.creditDesc = "";
        this.creditAmount = "";
      },
    },
  };
</script>