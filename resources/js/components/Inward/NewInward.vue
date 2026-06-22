<!-- Template is to design New Inward UI -->
<template>
  <div>
    <aside></aside>
    <div class="content-wrapper">
      <section class="content">
        <div class="container-fluid">
          <div>
            <div class="row">
              <div class="col-md-12 mt-3">
                <div class="card card-primary">
                  <!-- Add Inward Card header -->
                  <div class="card-header">
                    <h3 class="card-title">Add New Purchase (Gold / Silver)</h3>
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
                  <!-- Add Inward Card header end -->

                  <!-- Add Inward Card Body -->
                  <div class="card-body">
                    <div
                      class="form-group"
                      style="display: flex; flex-direction: row"
                    >
                      <div class="col-md-2">
                        <label for="date" class="col-form-label text-md"
                          >Inward Date
                          <span class="required-mark" style="color: red"
                            >*</span
                          ></label
                        >
                      </div>
                      <div class="col-md-3">
                        <input
                          type="date"
                          class="form-control"
                          v-model="date"
                          placeholder="Enter Inward Date..."
                        />
                      </div>
                      <div class="col-md-1"></div>
                      <div class="col-md-2">
                        <label for="companyName" class="col-form-label text-md"
                          >Company Name
                          <span class="required-mark" style="color: red"
                            >*</span
                          >
                        </label>
                      </div>
                      <div class="col-md-3">
                        <model-select
                          :options="companyNames"
                          @blur="getFromSelectedCompany"
                          v-model="selectedCompanyName"
                          placeholder="Select Company Name..."
                        >
                        </model-select>
                      </div>
                    </div>
                    <div
                      class="form-group"
                      style="display: flex; flex-direction: row"
                    >
                      <div class="col-md-2">
                        <label for="gstNo" class="col-form-label text-md"
                          >GST No.
                          <span class="required-mark" style="color: red"
                            >*</span
                          ></label
                        >
                      </div>
                      <div class="col-md-3">
                        <input
                          type="text"
                          class="form-control"
                          v-model="gstNo"
                          placeholder="GST No..."
                          disabled
                        />
                      </div>
                      <div class="col-md-1"></div>
                      <div class="col-md-2">
                        <label for="mobileNo" class="col-form-label text-md"
                          >Mobile No.
                          <span class="required-mark" style="color: red"
                            >*</span
                          ></label
                        >
                      </div>
                      <div class="col-md-3">
                        <input
                          type="text"
                          class="form-control"
                          v-model="mobileNo"
                          placeholder="Mobile No..."
                          disabled
                        />
                      </div>
                    </div>
                    <div
                      class="form-group"
                      style="display: flex; flex-direction: row"
                    >
                      <div class="col-md-2">
                        <label for="invoiceNo" class="col-form-label text-md"
                          >Invoice No.
                          <span class="required-mark" style="color: red"
                            >*</span
                          ></label
                        >
                      </div>
                      <div class="col-md-3">
                        <input
                          type="text"
                          class="form-control"
                          v-model="invoiceNo"
                          placeholder="Enter Invoice No..."
                        />
                      </div>
                      <div class="col-md-1"></div>
                      <div class="col-md-2">
                        <label for="broker" class="col-form-label text-md"
                          >Broker Name<span
                            class="required-mark"
                            style="color: red"
                            >*</span
                          >
                        </label>
                      </div>
                      <div class="col-md-3">
                        <model-select
                          :options="brokers"
                          v-model="selectedBroker"
                          placeholder="Select Broker Name.."
                        >
                        </model-select>
                      </div>
                    </div>
                    <div
                      class="form-group"
                      style="display: flex; flex-direction: row"
                    >
                      <div class="col-md-2">
                        <label for="" class="col-form-label text-md"
                          >Metal Type
                          <span class="required-mark" style="color: red"
                            >*</span
                          >
                        </label>
                      </div>
                      <div class="col-md-3">
                        <model-select
                          :options="productQualityCategories"
                          @blur="loadFromSelectedCategory"
                          v-model="selectedProductQualityCategory"
                          placeholder="Select Product Category..."
                        >
                        </model-select>
                      </div>
                      <div class="col-md-1"></div>
                      <div class="col-md-2">
                        <label for="unit" class="col-form-label text-md"
                          >Pieces
                          <span class="required-mark" style="color: red"
                            >*</span
                          ></label
                        >
                      </div>
                      <div class="col-md-3">
                        <input
                          type="text"
                          class="form-control"
                          v-model="unit"
                          placeholder="pcs"
                          value="pcs"
                          disabled
                        />
                      </div>
                    </div>
                    <div
                      class="form-group"
                      style="display: flex; flex-direction: row"
                    >
                      <div class="col-md-2">
                        <label
                          for="productQuality"
                          class="col-form-label text-md"
                          >Item Type
                          <span class="required-mark" style="color: red"
                            >*</span
                          >
                        </label>
                      </div>
                      <div class="col-md-3">
                        <model-select
                          :options="productQualities"
                          v-model="selectedProductQuality"
                          placeholder="Select Product Quality..."
                        >
                        </model-select>
                      </div>
                      <div class="col-md-1"></div>
                      <div class="col-md-2">
                        <label for="weightGrams" class="col-form-label text-md"
                          >Weight (grams)
                          <span class="required-mark" style="color: red"
                            >*</span
                          ></label
                        >
                      </div>
                      <div class="col-md-3">
                        <input
                          type="number"
                          step="0.001"
                          class="form-control"
                          v-model="weightGrams"
                          placeholder="Enter weight in grams..."
                        />
                      </div>
                      <div class="col-md-1"></div>
                      <div class="col-md-2">
                        <label for="quantity" class="col-form-label text-md"
                          >Pieces
                          <span class="required-mark" style="color: red"
                            >*</span
                          ></label
                        >
                      </div>
                      <div class="col-md-3">
                        <input
                          type="text"
                          id="quantity"
                          class="form-control"
                          v-model="quantity"
                          placeholder="Enter Quantity..."
                        />
                      </div>
                    </div>
                    <div
                      class="form-group"
                      style="display: flex; flex-direction: row"
                    >
                      <div class="col-md-2">
                        <label
                          for="rate"
                          id="rate"
                          class="col-form-label text-md"
                          >Rate / gram (₹)
                          <span class="required-mark" style="color: red"
                            >*</span
                          ></label
                        >
                      </div>
                      <div class="col-md-3">
                        <input
                          type="text"
                          id="quantity"
                          class="form-control"
                          v-model="rate"
                          placeholder="Enter Rate..."
                        />
                      </div>
                      <div class="col-md-1"></div>
                      <div class="col-md-2">
                        <label for="gstPercentage" class="col-form-label text-md">GST
                          <span class="required-mark" style="color: red">*</span></label>
                      </div>
                      <div class="col-md-3">
                        <select class="form-select form-control" id="gstPercentage"
                          v-model="gstPercentage">
                          <option value="0" selected>0%</option>
                          <option value="5">5%</option>
                          <option value="12">12%</option>
                          <option value="18">18%</option>
                          <option value="28">28%</option>
                        </select>
                      </div>
                    </div>
                    <div
                      class="form-group"
                      style="display: flex; flex-direction: row"
                    >
                      <div class="col-md-2">
                        <label for="totalAmount" class="col-form-label text-md"
                          >Total Amount
                          <span class="required-mark" style="color: red"
                            >*</span
                          ></label
                        >
                      </div>
                      <div class="col-md-3">
                        <input
                          type="text"
                          class="form-control"
                          v-model="totalAmount"
                          placeholder="Total Amount..."
                          disabled
                        />
                      </div>
                      <div class="col-md-1"></div>
                      <div class="col-md-2">
                        <label for="gstAmount" class="col-form-label text-md"
                          >GST Amount
                          <span class="required-mark" style="color: red"
                            >*</span
                          ></label
                        >
                      </div>
                      <div class="col-md-3">
                        <input
                          type="text"
                          class="form-control"
                          v-model="gstAmount"
                          placeholder="GST Amount..."
                          disabled
                        />
                      </div>
                    </div>
                    <div
                      class="form-group"
                      style="display: flex; flex-direction: row"
                    >
                      <div class="col-md-2">
                        <label for="netAmount" class="col-form-label text-md"
                          >Net Amount
                          <span class="required-mark" style="color: red"
                            >*</span
                          ></label
                        >
                      </div>
                      <div class="col-md-3">
                        <input
                          type="text"
                          class="form-control"
                          v-model="netAmount"
                          placeholder="Net Amount..."
                          disabled
                        />
                      </div>
                    </div>
                  </div>
                  <!-- Add Inward Card Body end -->

                  <!-- Add Inward Card Buttons Footer that call respective methods once we click on button -->
                  <div class="card-footer">
                    <button
                      type="submit"
                      v-on:click="addInward"
                      class="btn btn-primary"
                    >
                      Add
                    </button>
                    <button
                      type="reset"
                      v-on:click="resetInwardDetails"
                      class="btn btn-primary"
                    >
                      Reset
                    </button>
                  </div>
                  <!-- Add Inward Card Buttons Footer end -->
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
<!-- Template is to design New Inward UI end -->

<script>
//Here we have imported toastr and sweetalert2 for the alerts and Model is for select dynamic searchable options
import toastr from "toastr";
import swal from "sweetalert2";
import { ModelSelect } from "vue-search-select";

toastr.options = {
  closeButton: true,
  closeDuration: 200,
  progressBar: true,
  newestOnTop: true,
  positionClass: "toast-top-center",
};

//It contains all the data properties and methods of all the events
export default {
  name: "NewInward",
  components: {
    ModelSelect,
  },
  data() {
    return {
      //Here I have initialized all the Data Properties which i have used in v-model
      companyNames: [],
      productQualityCategories: [],
      productQualities: [],
      brokers: [],
      invoiceNo: "",
      date: "",
      gstNo: "",
      mobileNo: "",
      unit: "pcs",
      quantity: "1",
      weightGrams: "",
      metalType: "gold",
      rate: "",
      gstPercentage: "0",
      totalAmount: "",
      gstAmount: "",
      netAmount: "",
      status: null,
      message: null,
      errors: null,
      selectedCompanyName: "",
      selectedProductQualityCategory: "",
      selectedProductQuality: "",
      selectedBroker: "",
    };
  },

  //Watchers is to Calculate Total Amount, GST Amount and Net Amount at the change of particular field
  watch: {
    quantity: function () {
      this.calcTotalAmount();
    },

    rate: function () {
      this.calcTotalAmount();
    },

    weightGrams: function () {
      this.calcTotalAmount();
    },

    totalAmount: function () {
      this.calcGstAmount();
      this.calcNetAmount();
    },

    gstPercentage: function () {
      this.calcGstAmount();
    },

    gstAmount: function () {
      this.calcNetAmount();
    },
  },

  // This all methods loaded once the page is refreshed
  mounted() {
    this.date = this.getTodaysDate();
    this.loadCompanyName();
    this.loadProductQualityCategory();
    this.loadBroker();
  },

  methods: {
    //Function is to get today's date
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

    //Function is whether the date is Empty or not
    dateValidation: function () {
      if (this.date == "") {
        toastr.info("Please Enter Date");
        return false;
      } else {
        return true;
      }
    },

    //Function is to Validate Company Name 
    companyNameValidation: function () {
      if (
        this.selectedCompanyName == "" ||
        typeof this.selectedCompanyName === "undefined"
      ) {
        toastr.info("Please Enter Company Name");
        return false;
      } else {
        return true;
      }
    },

    //Function is to Validate Invoice No.
    invoiceNoValidation: function () {
      if (this.invoiceNo == "") {
        toastr.info("Please Enter Invoice No");
        return false;
      } else if (this.invoiceNo.length > 20) {
        toastr.warning("Invoice No must be less or equal than 20 characters!");
        return false;
      } else {
        return true;
      }
    },

    //Function is to Validate Broker Name whether the broker field is empty or not
    brokerValidation: function () {
      if (
        this.selectedBroker == "" ||
        typeof this.selectedBroker === "undefined"
      ) {
        toastr.info("Please Enter Broker");
        return false;
      } else {
        return true;
      }
    },

    //Function is to Validate Product Category whether the product category field is empty or not
    productCategoryValidation: function () {
      if (
        this.selectedProductQualityCategory == "" ||
        typeof this.selectedProductQualityCategory === "undefined"
      ) {
        toastr.info("Please Enter Product Category");
        return false;
      } else {
        return true;
      }
    },

    
    //Function is to Validate Product Quality whether the product quality field is empty or not
    productQualityValidation: function () {
      if (
        this.selectedProductQuality == "" ||
        typeof this.selectedProductQuality === "undefined"
      ) {
        toastr.info("Please Enter Product Quality");
        return false;
      } else {
        return true;
      }
    },

    //Function is to check Product Quantity
    quantityValidation: function () {
      if (this.quantity == "") {
        this.quantity = 1;
      } else if (this.quantity < 0) {
        toastr.info("Pieces can't be negative");
        return false;
      } else {
        return true;
      }
    },

    weightGramsValidation: function () {
      if (this.weightGrams == "" || parseFloat(this.weightGrams) <= 0) {
        toastr.info("Please enter weight in grams");
        return false;
      }
      return true;
    },

    //Function is to check Rate per gram
    ratePerQtyValidation: function () {
      if (this.rate == "") {
        toastr.info("Please Enter Rate Per Gram");
        return false;
      } else if (this.rate < 0) {
        toastr.info("Rate Can't Be minus");
        return false;
      } else if (this.rate.length > 18) {
        toastr.warning(
          "Rate Per Quantity must be less or equal than 18 digits"
        );
        return false;
      } else {
        return true;
      }
    },

    //Function is to load Company Names
    loadCompanyName() {
      axios
        .get("../api/vendorcompanies")
        .then((response) => {
          this.companyNames = response.data.map((name) => {
            return {
              value: name.vendor_id,
              text: name.vendor_company_name + "-" + name.vendor_contact_no,
            };
          });
        })
        .catch((err) => {
          console.log(err);
          toastr["error"]("Something went Wrong");
        });
    },

    //Function is to load Product Quality Category
    loadProductQualityCategory() {
      axios
        .get("../api/sellqualitycategories")
        .then((response) => {
          this.productQualityCategories = response.data.qualityCategories.map((category) => {
            return {
              value: category.qualityCategoryId,
              text: category.qualityCategoryName + " (" + category.metalType + ")",
              metalType: category.metalType,
            };
          });
        })
        .catch((err) => {
          console.log(err);
          toastr["error"]("Something Went Wrong");
        });
    },

    //Function is to load Broker Name
    loadBroker() {
      axios
        .get("../api/getBrokers")
        .then((response) => {
          this.brokers = response.data.map((broker) => {
            return {
              value: broker.broker_id,
              text: broker.broker_name + "-" + broker.broker_contact_no,
            };
          });
        })
        .catch((err) => {
          console.log(err);
          toastr["error"]("Something went Wrong");
        });
    },

    //Function is to autofill Vendor contact no. and gst no. once we fill up Company Name
    getFromSelectedCompany: function () {
      if (
        this.selectedCompanyName == "" ||
        typeof this.selectedCompanyName === "undefined"
      ) {
        this.mobileNo = "";
        this.gstNo = "";
        return;
      }

      axios
        .get("../api/selectedvendordata/" + this.selectedCompanyName)
        .then((response) => {
          this.mobileNo = response.data.vendor_contact_no;
          this.gstNo = response.data.vendor_gst_no;
        })
        .catch((err) => {
          console.log(err);
          toastr["info"]("Please Select Company Name.");
        });
    },

    //Function is to autoload Product Quality based on the Quality Category
    loadFromSelectedCategory: function () {
      if (
        this.selectedProductQualityCategory == "" ||
        typeof this.selectedProductQualityCategory === "undefined"
      ) {
        this.unit = "";
        this.productQualities = [];
        return;
      }

      axios
        .get(
          "../api/sellqualityofcategory/" +
            this.selectedProductQualityCategory
        )
        .then((response) => {
          this.productQualities = response.data.map((quality) => {
            return {
              value: quality.sell_quality_id,
              text: quality.quality_name,
            };
          });

          const category = this.productQualityCategories.find(
            (c) => c.value == this.selectedProductQualityCategory
          );
          this.metalType = category ? category.metalType : "gold";
          this.unit = "pcs";
        })
        .catch((err) => {
          console.log(err);
          toastr["info"]("Please Select Product Category.");
        });
    },

    //Function or Watcher is to calculate Total amount based on the Quantity and Rate
    calcTotalAmount: function () {
      if (this.weightGrams == "" || this.rate == "") {
        this.totalAmount = "";
      } else {
        this.totalAmount = (parseFloat(this.weightGrams) * parseFloat(this.rate)).toFixed(2);
      }
    },

    //Function or Watcher is to calculate GST amount based on the Total Amount and and GST Percentage
    calcGstAmount: function () {
      if (this.gstPercentage == "0" || this.totalAmount == "") {
        this.gstAmount = "";
      } else {
        this.gstAmount = this.totalAmount * this.gstPercentage * 0.01;
      }
    },

    //Function or Watcher is to calculate GST amount based on the Total Amount and and GST Percentage
    calcNetAmount: function () {
      if (this.totalAmount == "") {
        this.netAmount = "";
      } else {
        this.netAmount = this.totalAmount + this.gstAmount;
      }
    },

    //Function is to add inward if the Validation is proper
    addInward() {
      if (
        this.dateValidation() &&
        this.companyNameValidation() &&
        this.invoiceNoValidation() &&
        this.brokerValidation() &&
        this.productCategoryValidation() &&
        this.productQualityValidation() &&
        this.weightGramsValidation() &&
        this.quantityValidation() &&
        this.ratePerQtyValidation()
      ) {
        var addData = {};
        addData["date"] = this.date;
        addData["companyName"] = this.selectedCompanyName;
        addData["invoiceNo"] = this.invoiceNo;
        addData["brokerName"] = this.selectedBroker;
        addData["itemTypeId"] = this.selectedProductQuality;
        addData["metalType"] = this.metalType;
        addData["weightGrams"] = this.weightGrams;
        addData["unit"] = this.unit || "pcs";
        addData["quantity"] = this.quantity || 1;
        addData["rate"] = this.rate;
        addData["gstPercentage"] = this.gstPercentage;

        axios
          .post("../api/inward", addData)
          .then((res) => {
            if (res.data.status == -1) {
              var errormsg = res.data.errors;
              try {
                if (errormsg.date) toastr["error"](errormsg.date);
              } catch (err) {}

              try {
                if (errormsg.companyName) toastr["error"](errormsg.companyName);
              } catch (err) {}

              try {
                if (errormsg.gstNo) toastr["error"](errormsg.gstNo);
              } catch (err) {}

              try {
                if (errormsg.mobileNo) toastr["error"](errormsg.mobileNo);
              } catch (err) {}

              try {
                if (errormsg.invoiceNo) toastr["error"](errormsg.invoiceNo);
              } catch (err) {}

              try {
                if (errormsg.broker) toastr["error"](errormsg.broker);
              } catch (err) {}

              try {
                if (errormsg.productQuality)
                  toastr["error"](errormsg.productQuality);
              } catch (err) {}

              try {
                if (errormsg.unit) toastr["error"](errormsg.unit);
              } catch (err) {}

              try {
                if (errormsg.quantity) toastr["error"](errormsg.quantity);
              } catch (err) {}

              try {
                if (errormsg.rate) toastr["error"](errormsg.rate);
              } catch (err) {}

              try {
                if (errormsg.gstPercentage)
                  toastr["error"](errormsg.gstPercentage);
              } catch (err) {}
            } else if (res.data.status == 0) {
              toastr["warning"](res.data.message);
            } else if (res.data.status == 1) {
              swal
                .fire({
                  title: "Success",
                  html: "<h5 style='color:#9C9794'>Purchase added and stock updated</h5>",
                  icon: "success",
                })
                .then((result) => {
                  this.resetInwardDetails();
                });
            }
          })
          .catch((err) => {
            console.log(err.response.data.message);
            toastr["error"]("Something Went Wrong.");
          });
      }
    },

    //Function is to reset the details of Inward
    resetInwardDetails() {
      this.date = this.getTodaysDate();
      (this.invoiceNo = ""),
        (this.mobileNo = ""),
        (this.gstNo = ""),
        (this.selectedCompanyName = ""),
        (this.selectedProductQualityCategory = ""),
        (this.selectedProductQuality = ""),
        (this.selectedBroker = ""),
        (this.unit = "pcs"),
        (this.quantity = "1"),
        (this.weightGrams = ""),
        (this.metalType = "gold"),
        (this.rate = ""),
        (this.gstPercentage = "0"),
        (this.totalAmount = ""),
        (this.gstAmount = ""),
        (this.netAmount = "");
    },
  },
};
</script>