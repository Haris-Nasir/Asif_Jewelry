<template>
    <div>
        <aside></aside>
        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-primary mt-3">
                                <div class="card-header card-title d-flex">
                                    <span class="p-2 flex-grow-1 bd-highlight">
                                        Generate Sales Bill (Direct)
                                    </span>
                                    <span class="p-2 bd-highlight">
                                        <button type="button" class="btn btn-tool text-md" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool text-md" >
                                            <i class="fas fa-times"></i>
                                        </button>                                    
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label for="invoice-date" class="text-md mt-1">Invoice Date</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="datetime-local" id="invoice-date" class="form-control" v-model="invoice.invoiceDate">
                                        </div>
                                        <div class="col-md-2"></div>
                                        <div class="col-md-2">
                                            <label for="invoice-no" class="text-md mt-1">Invoice No</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" id="invoice-no" class="form-control text-right" v-model="invoice.invoiceNo">
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-2">
                                            <label for="" class="text-md mt-1">Customer</label>
                                        </div>
                                        <div class="col-md-4">
                                            <model-select :options="options.customers" v-model="invoice.customerId"
                                            placeholder="Select Customer">
                                            </model-select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="" class="text-md mt-1">Broker</label>
                                        </div>
                                        <div class="col-md-4">
                                            <model-select :options="options.brokers" v-model="invoice.brokerId"
                                            placeholder="Select Broker">
                                            </model-select>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-2">
                                            <label for="" class="text-md mt-1">Category</label>
                                        </div>
                                        <div class="col-md-4">
                                            <model-select :options="options.categories" v-model="invoice.categoryId"
                                            placeholder="Select Category">
                                            </model-select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="" class="text-md mt-1">Quality</label>
                                        </div>
                                        <div class="col-md-4">
                                            <model-select :options="options.qualities" v-model="invoice.qualityId"
                                            placeholder="Select Quality">
                                            </model-select>
                                        </div>
                                    </div>
                                    <div class="row mt-3" v-if="pendingKarigarJobs.length">
                                        <div class="col-md-2">
                                            <label class="text-md mt-1">Karigar Job</label>
                                        </div>
                                        <div class="col-md-4">
                                            <model-select :options="karigarJobOptions" v-model="invoice.karigarJobId"
                                                placeholder="Link returned karigar job (optional)">
                                            </model-select>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="text-muted small mt-2 mb-0">Select if this sale is from metal returned by a karigar — mazduri will be deducted from profit</p>
                                        </div>
                                    </div>
                                    <div class="row mt-3">  
                                        <div class="col-md-2">
                                            <label for="" class="text-md mt-1">Weight per piece (g)</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" step="0.001" class="form-control text-right" v-model="invoice.weightGrams">
                                        </div>
                                        <div class="col-md-8" v-if="qualityStock">
                                            <p class="text-muted small mt-2 mb-0">
                                                Available for <strong>{{ qualityStock.quality_name }}</strong>:
                                                {{ qualityStock.weight_grams }}g, {{ qualityStock.pieces }} pcs
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row mt-3">  
                                        <div class="col-md-2">
                                            <label for="" class="text-md mt-1">Qty (pieces)</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" step="1" min="1" class="form-control text-right" v-model="invoice.qty">
                                        </div>
                                        <div class="col-md-2"></div>
                                        <div class="col-md-2">
                                            <label for="" class="text-md mt-1">Unit</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" v-model="invoice.unit">
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-2">
                                            <label class="text-md mt-1">Total Weight (g)</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control text-right" :value="totalWeightGrams" disabled>
                                        </div>
                                        <div class="col-md-8">
                                            <p class="text-muted small mt-2 mb-0">= weight per piece × qty</p>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-2">
                                            <label for="" class="text-md mt-1">Rate / gram (Rs.)</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" step="0.01" class="form-control text-right" v-model="invoice.rate">
                                        </div>
                                        <div class="col-md-8">
                                            <p class="text-muted small mt-2 mb-0">Amount = total weight (g) × rate/g</p>
                                        </div>
                                        <div class="col-md-2"></div>
                                        <div class="col-md-2">
                                            <label for="" class="text-md mt-1">GST Percentage</label>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-control text-right" v-model="invoice.gstPercentage">
                                                <option value='0'>0</option>
                                                <option value='5'>5</option>
                                                <option value='12'>12</option>
                                                <option value='18'>18</option>
                                                <option value='28'>28</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-2">
                                            <label for="" class="text-md mt-1">Refinery Cost (Rs.)</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" step="0.01" min="0" class="form-control text-right" v-model="invoice.refineryCost">
                                        </div>
                                        <div class="col-md-8">
                                            <p class="text-muted small mt-2 mb-0">Applied to gold and silver sales</p>
                                        </div>
                                    </div>
                                    <div class="row mt-3" v-if="selectedMetalType === 'gold'">
                                        <div class="col-md-2">
                                            <label for="" class="text-md mt-1">Polish / g (Rs.)</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" step="0.01" min="0" class="form-control text-right" v-model="invoice.polishRatePerGram">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="text-md mt-1">Polish Total</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control text-right" :value="polishCostTotal" disabled>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="text-muted small mt-2 mb-0">= polish/g × total weight (gold only)</p>
                                        </div>
                                    </div>
                                    <div class="row mt-3" v-if="showMazduriField">
                                        <div class="col-md-2">
                                            <label for="" class="text-md mt-1">Mazduri (Rs.)</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" step="0.01" min="0" class="form-control text-right" v-model="invoice.mazduriCost"
                                                :disabled="!!invoice.karigarJobId">
                                        </div>
                                        <div class="col-md-8">
                                            <p class="text-muted small mt-2 mb-0">{{ mazduriHelpText }}</p>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-2">
                                            <label class="text-md mt-1">Processing Cost</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control text-right" :value="totalProcessingCost" disabled>
                                        </div>
                                        <div class="col-md-8">
                                            <p class="text-muted small mt-2 mb-0">Deducted from profit (refinery + polish or mazduri)</p>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-2">
                                            <label for="" class="text-md mt-1">Total Amount</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="form-control text-right" disabled v-model="invoice.totalAmount">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="" class="text-md mt-1">GST Amount</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="form-control text-right" disabled v-model="invoice.gstAmount">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="" class="text-md mt-1">Net Amount</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="form-control text-right" disabled v-model="invoice.netAmount">
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-primary" @click="generateInvoice">Generate Invoice</button>
                                    <button class="btn btn-primary" @click="resetInvoiceForm">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script>

import toastr from "toastr";
import swal from "sweetalert2";
import { ModelSelect } from "vue-search-select";
import { getNowDateTime } from "../../currency";


export default {
    name: "GenerateDirectInvoice",
    components: {
        ModelSelect
    },
    data() {
        return{
            invoice: {
                invoiceDate: "",
                invoiceNo: "",
                customerId: "",
                brokerId: "",
                categoryId: "",
                qualityId: "",
                qty: "",
                weightGrams: "",
                rate: "",
                gstPercentage: "0",
                totalAmount: (0).toFixed(2),
                gstAmount: (0).toFixed(2),
                netAmount: (0).toFixed(2),
                unit:"",
                refineryCost: "",
                polishRatePerGram: "",
                mazduriCost: "",
                karigarJobId: "",
            },

            options: {
                customers: [],
                brokers: [],
                categories: [],
                qualities: []
            },
            qualityStock: null,
            pendingKarigarJobs: [],
        }
    },
    mounted() {
        this.invoice.invoiceDate = getNowDateTime();
        this.loadCustomers();
        this.loadBrokers();
        this.loadCategories();
    },
    computed: {
        selectedMetalType() {
            const category = this.options.categories.find((item) => String(item.value) === String(this.invoice.categoryId));
            return category?.metalType || 'gold';
        },
        totalWeightGrams() {
            const perPiece = parseFloat(this.invoice.weightGrams || 0);
            const qty = parseFloat(this.invoice.qty || 0);
            if (!perPiece || !qty) {
                return '0.000';
            }
            return (perPiece * qty).toFixed(3);
        },
        polishCostTotal() {
            if (this.selectedMetalType !== 'gold') {
                return '0.00';
            }
            const total = parseFloat(this.invoice.polishRatePerGram || 0) * parseFloat(this.totalWeightGrams || 0);
            return Number.isNaN(total) ? '0.00' : total.toFixed(2);
        },
        totalProcessingCost() {
            const refinery = parseFloat(this.invoice.refineryCost || 0) || 0;
            const polish = parseFloat(this.polishCostTotal || 0) || 0;
            const mazduri = this.showMazduriField ? (parseFloat(this.invoice.mazduriCost || 0) || 0) : 0;
            return (refinery + polish + mazduri).toFixed(2);
        },
        showMazduriField() {
            return this.selectedMetalType === 'silver' || !!this.invoice.karigarJobId;
        },
        mazduriHelpText() {
            if (this.invoice.karigarJobId) {
                return 'Karigar labour — deducted from profit when this item is sold';
            }
            return 'Overall labour charge for this silver sale';
        },
        karigarJobOptions() {
            return this.pendingKarigarJobs.map(job => ({
                value: job.karigar_job_id,
                text: job.karigar_name + ' — ' + job.returned_weight_grams + 'g (Rs. ' + job.mazduri_cost + ' mazduri)',
            }));
        },
    },
    watch: {
        'invoice.categoryId' : function(){
            this.loadQualitiesOfSelectedCategory();
        },

        'invoice.qualityId' : function(){
            this.loadQualityStock();
            this.loadPendingKarigarJobs();
        },

        'invoice.karigarJobId' : function(val){
            if (!val) {
                if (this.selectedMetalType !== 'silver') {
                    this.invoice.mazduriCost = '';
                }
                return;
            }
            const job = this.pendingKarigarJobs.find(j => String(j.karigar_job_id) === String(val));
            if (job) {
                this.invoice.mazduriCost = job.mazduri_cost;
            }
        },

        'invoice.qty' : function(){
            this.calculateAmounts();
        },

        'invoice.weightGrams' : function(){
            this.calculateAmounts();
        },

        'invoice.rate' : function(){
            this.calculateAmounts();
        },

        'invoice.gstPercentage' : function(){
            this.calculateAmounts();
        }
    },
    methods: {
        




        // Load Options In Select Menu

        loadCustomers: function(){
            axios
                .get('../api/customerlist').then((response) => {
                    this.options.customers = response.data.map(company => {
                        return {
                            value: company.customer_id,
                            text: company.customer_company_name + ' - ' + company.customer_contact_no
                        }
                    });
                })
                .catch(err => {
                    console.log(err);
                    toastr["error"]("Something went Wrong.")
                })
        },

        loadBrokers: function(){
            axios.get('../api/brokerslist').then((response) => {
                    this.options.brokers = response.data.map(broker => {
                        return {
                            value: broker.broker_id,
                            text: broker.broker_name + ' - ' + broker.broker_contact_no
                        }
                    })
                }).catch(err => {
                    console.log(err);
                    toastr["error"]("Something went Wrong.")
                })
        },

        loadCategories: function(){
            axios
            .get('../api/sellqualitycategories').then((response) => {
                this.options.categories = response.data.qualityCategories.map(category => {
                    return {
                        value: category.qualityCategoryId,
                        text: category.qualityCategoryName,
                        metalType: category.metalType || 'gold',
                    }
                });
            })
            .catch(err => {
                console.log(err);
                toastr["error"]("Something went Wrong");
            })
        },

        loadQualitiesOfSelectedCategory: function(){
            if (this.invoice.categoryId == '' || typeof (this.invoice.categoryId) === 'undefined' || this.invoice.categoryId == null) {
                this.invoice.unit = '';
                this.options.qualities = [];
                this.invoice.qualityId = "";
                this.qualityStock = null;
                return;
            }

            axios
                .get('../api/sellqualityofcategory/' + this.invoice.categoryId)
                .then(response => {
                    this.options.qualities = response.data.map(quality => {
                        return {
                            value: quality.sell_quality_id,
                            text: quality.quality_name
                        }
                    });

                    if (this.invoice.categoryId == '1') {
                        this.invoice.unit = "pcs";
                    }else if (this.invoice.categoryId == '2' || this.invoice.categoryId == '3') {
                        this.invoice.unit = "pcs";
                    }
                })
                .catch(err => {
                    console.log(err);
                    toastr["error"]("Something went Wrong.")
                })
        },

        loadQualityStock: function () {
            if (!this.invoice.qualityId) {
                this.qualityStock = null;
                return;
            }

            axios.get('/api/stock/quality/' + this.invoice.qualityId)
                .then((res) => {
                    this.qualityStock = res.data;
                })
                .catch((err) => {
                    console.log(err);
                    this.qualityStock = null;
                });
        },

        loadPendingKarigarJobs: function () {
            this.invoice.karigarJobId = '';
            this.pendingKarigarJobs = [];
            if (!this.invoice.qualityId) {
                return;
            }
            axios.get('/api/karigar/jobs/pending-for-sale?sell_quality_id=' + this.invoice.qualityId)
                .then(res => {
                    this.pendingKarigarJobs = res.data || [];
                })
                .catch(() => {
                    this.pendingKarigarJobs = [];
                });
        },


        // validate Method
        isInvoiceDateValid: function(){
            if(this.invoice.invoiceDate == "" || typeof this.invoice.invoiceDate === 'undefined' || this.invoice.invoiceDate == null){
                toastr.info("Please Selecte Invoice Date");
                return false;
            }
            return true;
        },

        isInvoiceNoValid: function () {
            if(this.invoice.invoiceNo == ""){
                toastr.info("Please Insert Invoice No");
                return false;
            }

            if(this.invoice.invoiceNo <= 0){
                toastr.info("Invoice No Is In-Valid");
                return false;
            }

            return true;
        },

        isCustomerValid: function(){
            if(typeof this.invoice.customerId === 'undefined' || this.invoice.customerId == ""){
                toastr.info("Please Select Customer");
                return false;
            }
            return true;
        },

        isBrokerValid: function(){
            if(typeof this.invoice.brokerId === 'undefined' || this.invoice.brokerId == ""){
                toastr.info("Please Select Broker");
                return false;
            }
            return true;
        },

        isCategoryValid: function(){
            if(typeof this.invoice.categoryId === 'undefined' || this.invoice.categoryId == ""){
                toastr.info("Please Select Category");
                return false;
            }
            return true;
        },

        isQualityValid: function(){
            if(typeof this.invoice.qualityId === 'undefined' || this.invoice.qualityId == ""){
                toastr.info("Please Select Quality");
                return false;
            }
            return true;
        },

        isQtyValid: function(){
            if(this.invoice.qty == "" || typeof this.invoice.qty === 'undefined'){
                toastr.info("Please Enter Qty (pieces)");
                return false;
            }
            if(parseFloat(this.invoice.qty) <= 0){
                toastr.info("Qty must be at least 1 piece");
                return false;
            }

            return true;
        },

        isUnitValid: function(){
            if(typeof this.invoice.unit === 'undefined' || this.invoice.unit == ""){
                toastr.info("Please Select Unit");
                return false;
            }
            return true;
        },

        isRateValid: function(){
            if(this.invoice.rate == "" || typeof this.invoice.rate === 'undefined'){
                toastr.info("Please Enter Rate per gram");
                return false;
            }
            if(this.invoice.rate < 0){
                toastr.info("Rate Is Invalid");
                return false;
            }

            return true;
        },

        isGSTPercentageValid: function(){
            if(this.invoice.gstPercentage == "" || typeof this.invoice.gstPercentage === 'undefined'){
                toastr.info("Please Select GST Pecentage");
                return false;
            }
            return true;
        },

        isWeightGramsValid: function(){
            if(this.invoice.weightGrams == "" || parseFloat(this.invoice.weightGrams) <= 0){
                toastr.info("Please enter weight per piece in grams");
                return false;
            }
            return true;
        },




        // Generate Invoice Btn
        generateInvoice: function() {
            console.log(this.invoice);
            if(this.isInvoiceDateValid() && this.isInvoiceNoValid() && this.isCustomerValid() && this.isBrokerValid() && this.isCategoryValid() && this.isQualityValid() && this.isQtyValid() && this.isUnitValid() && this.isRateValid() && this.isGSTPercentageValid() && this.isWeightGramsValid()){
                axios
                    .post('/api/directinvoice', this.invoice)
                    .then(response => {
                        if(response.data.status == 1){
                            swal
                                .fire({
                                    title: "Success",
                                    html:  "<h5 style='color:#9C9794'>Sale bill created. Profit: Rs. " + (response.data.profit || 0) + "</h5>",
                                    icon: "success",
                                    allowOutsideClick: false
                                })
                                .then(() => {
                                    this.resetInvoiceForm();
                                });
                        }
                        else if(response.data.status == -1){
                            const errors = response.data.errors;
                            if (errors && typeof errors === 'object' && Object.keys(errors).length) {
                                let errorString = "";
                                for (const field in errors) {
                                    for (let i = 0; i < errors[field].length; i++) {
                                        errorString += "<li>" + errors[field][i] + "</li>";
                                    }
                                }
                                toastr.error(errorString, response.data.message || "Validation failed", { timeOut: 20000, closeButton: true });
                            } else {
                                toastr.error(response.data.message || "Unable to create sale bill.");
                            }
                        }
                        else if(response.data.status == 0){
                            toastr.error(response.data.message);
                        }
                        else{
                            console.log("Other Then Expected Response Recieved In Generate Direct Invoice");
                            toastr.error(response.data.message || "Unable to create sale bill.");
                        }

                        
                    })  
                    .catch(err=>{
                        console.log("Err In Generatin Invoice", err);
                        toastr.error(err.response?.data?.message || "Unable to create sale bill. Check item type stock and weight.");
                    });
            }
        },

        // Reset Invoice Form Btn
        resetInvoiceForm: function() {
            this.invoice.invoiceDate = getNowDateTime();
            this.invoice.invoiceNo = "";
            this.invoice.customerId = "";
            this.invoice.brokerId = "";
            this.invoice.categoryId = "";
            this.invoice.qualityId ="";
            this.invoice.qty = "";
            this.invoice.weightGrams = "";
            this.invoice.rate = "";
            this.invoice.gstPercentage = "0";
            this.invoice.totalAmount = (0).toFixed(2);
            this.invoice.gstAmount = (0).toFixed(2);
            this.invoice.netAmount = (0).toFixed(2);
            this.invoice.unit = "";
            this.invoice.refineryCost = "";
            this.invoice.polishRatePerGram = "";
            this.invoice.mazduriCost = "";
            this.invoice.karigarJobId = "";
            this.pendingKarigarJobs = [];
            this.qualityStock = null;
        },


        // Date Management Methods
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

        getDateBeforeDays: function () {
            let date = new Date();
            let last = new Date(
                date.getTime() - this.days * 24 * 60 * 60 * 1000
            );
            let day = "" + last.getDate();
            let month = "" + (last.getMonth() + 1);
            let year = "" + last.getFullYear();
            if (day < 10) {
                day = "0" + day;
            }
            if (month < 10) {
                month = "0" + month;
            }
            return year + "-" + month + "-" + day;
        },

        getStdDate: function(date){
            date = date.split("-");
            return (date[2]+"-"+date[1]+"-"+date[0]);
        },


        // Calculate Amount
        calculateAmounts: function (){
            const totalWeight = parseFloat(this.invoice.weightGrams || 0) * parseFloat(this.invoice.qty || 0);
            const totalAmount = totalWeight * parseFloat(this.invoice.rate || 0);
            const gstAmount = totalAmount * this.invoice.gstPercentage * 0.01;
            const netAmount = totalAmount + gstAmount;
            
            this.invoice.totalAmount = totalAmount.toFixed(2);
            this.invoice.gstAmount = gstAmount.toFixed(2);
            this.invoice.netAmount = netAmount.toFixed(2);
        }
    }
}
</script>

<style scoped>
    /* Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Firefox */
    input[type=number] {
        -moz-appearance: textfield;
    }
</style>