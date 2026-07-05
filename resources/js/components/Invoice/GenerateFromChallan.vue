<template>
    <div>
        <aside></aside>

        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12 mt-3">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Generate Invoice From Sales Bill</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                                class="fas fa-minus"></i></button>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="salesBillNo" class="text-md col-form-label">Sales Bill No. <span
                                                    class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-4">
                                            <model-select
                                                :options="availableSalesBills"
                                                v-model="salesBillNo"
                                                @input="onSalesBillSelected"
                                                placeholder="Select Sales Bill No...">
                                            </model-select>
                                            <small class="text-muted">Sales bills not yet invoiced.</small>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="financialYear" class="text-md col-form-label">Financial Year
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <model-select :options="financialYear" v-model="selectedFinancialYear"
                                                @input="loadSalesBillData"
                                                placeholder="Select a Financial Year">
                                            </model-select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="challanNo" class="text-md col-form-label">Sales Bill No
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="text-md text-right form-control"
                                                v-model="challanNo" disabled>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="challanDate" class="text-md col-form-label">Sales Bill Date
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="text-md form-control" v-model="challanDate"
                                                disabled>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="brokerName" class="text-md col-form-label">Broker Name
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="text-md form-control" v-model="brokerName"
                                                disabled>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="customerName" class="text-md col-form-label">Customer Name
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="text-md form-control" v-model="customerName"
                                                disabled>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="gstNo" class="text-md col-form-label">GST Number</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="text-md text-right form-control" v-model="gstNo"
                                                disabled>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="code" class="text-md col-form-label">Code</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="text-md text-right form-control" v-model="code"
                                                disabled>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="address" class="text-md col-form-label">Address
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea class="text-md form-control" v-model="address"
                                                disabled></textarea>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="productQuality" class="text-md col-form-label">Product Quality
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="text-md form-control" v-model="productQuality"
                                                disabled>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="totalPieces" class="text-md col-form-label">Total Pieces
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="text-md text-right form-control"
                                                v-model="totalPieces" disabled>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="weightGrams" class="text-md col-form-label">Weight (g)
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="text-md text-right form-control"
                                                v-model="weightGrams" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group row" v-if="pendingKarigarJobs.length">
                                        <div class="col-md-2">
                                            <label class="text-md col-form-label">Karigar Job</label>
                                        </div>
                                        <div class="col-md-4">
                                            <model-select :options="karigarJobOptions" v-model="karigarJobId"
                                                placeholder="Link returned karigar job (optional)">
                                            </model-select>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">If this sale is from karigar work, select the job — mazduri is deducted from profit</small>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="invoiceDate" class="text-md col-form-label">Invoice Date <span
                                                    class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="datetime-local" class="text-md form-control" v-model="invoiceDate">
                                        </div>

                                        <div class="col-md-2">
                                            <label for="dueDate" class="text-md col-form-label">Due Date <span
                                                    class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="date" class="text-md form-control" v-model="dueDate">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="rate" class="text-md col-form-label">Rate / gram (Rs.)
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" step="0.01" class="text-md text-right form-control"
                                                v-model="rate" placeholder="Rate per gram..."
                                                @change="recalculateTotals">
                                            <small class="text-muted">Amount = weight (g) × rate/g</small>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="amount" class="text-md col-form-label">Base Amount (Rs.)
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="text-md text-right form-control"
                                                v-model="amount" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="gstPercentage" class="text-md col-form-label">GST</label>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select form-control" v-model="gstPercentage"
                                                @change="recalculateTotals">
                                                <option value="0" selected>0%</option>
                                                <option value="5">5%</option>
                                                <option value="12">12%</option>
                                                <option value="18">18%</option>
                                                <option value="28">28%</option>
                                            </select>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="gstAmount" class="text-md col-form-label">GST Amount</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="text-md text-right form-control"
                                                v-model="gstAmount" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label class="text-md col-form-label">Refinery Cost (Rs.)</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" step="0.01" min="0" class="text-md text-right form-control"
                                                v-model="refineryCost">
                                        </div>
                                        <div class="col-md-8">
                                            <small class="text-muted">Applied to gold and silver sales</small>
                                        </div>
                                    </div>

                                    <div class="form-group row" v-if="metalType === 'gold'">
                                        <div class="col-md-2">
                                            <label class="text-md col-form-label">Polish / g (Rs.)</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" step="0.01" min="0" class="text-md text-right form-control"
                                                v-model="polishRatePerGram">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="text-md col-form-label">Polish Total</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="text-md text-right form-control" :value="polishCostTotal" disabled>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">= polish/g × weight (gold only)</small>
                                        </div>
                                    </div>

                                    <div class="form-group row" v-if="showMazduriField">
                                        <div class="col-md-2">
                                            <label class="text-md col-form-label">Mazduri (Rs.)</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" step="0.01" min="0" class="text-md text-right form-control"
                                                v-model="mazduriCost" :disabled="!!karigarJobId">
                                        </div>
                                        <div class="col-md-8">
                                            <small class="text-muted">{{ mazduriHelpText }}</small>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label class="text-md col-form-label">Processing Cost</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="text-md text-right form-control" :value="totalProcessingCost" disabled>
                                        </div>
                                        <div class="col-md-8">
                                            <small class="text-muted">Deducted from profit (refinery + polish or mazduri)</small>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="grandTotal" class="text-md col-form-label">Grand Total <span
                                                    class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="text-md text-right form-control"
                                                v-model="grandTotal" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="bank" class="text-md col-form-label">Bank <span
                                                    class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-3">
                                            <model-select :options="bank" v-model="selectedBank"
                                                placeholder="Select a Bank" @blur="getBranchName">
                                            </model-select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="branch" class="text-md col-form-label">Branch <span
                                                    class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" class="text-md form-control" v-model="branch" disabled>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary text-md"
                                        @click="addInvoice">Add</button>
                                    <button type="reset" class="btn btn-primary ml-3 text-md"
                                        @click="resetFields">Reset</button>
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
    //Here we have imported toastr and sweetalert2 for the alerts and Model Select for dynamic searchable options
    import toastr from 'toastr';
    import swal from 'sweetalert2';
    import { ModelSelect } from "vue-search-select";
    import { getNowDateTime, formatDate } from "../../currency";

    //toastr options contains properties of the alerts so on firing it will display as per the below options
    toastr.options = {
        closeButton: true,
        closeDuration: 200,
        progressBar: true,
        newestOnTop: true,
        positionClass: "toast-top-center",
    };

    export default {
        name: 'GenerateFromChallan',
        components: {
            ModelSelect,
        },
        mounted() {
            this.invoiceDate = getNowDateTime();
            this.dueDate = this.getDueDate();
            this.getBank();
            this.loadAvailableSalesBills();
        },
        watch: {
            salesBillNo: function () {
                this.resetDisplayDataFields();
                this.financialYear = [];
                this.selectedFinancialYear = '';
                this.challanMstId = '';
            },
            karigarJobId(val) {
                if (!val) {
                    if (this.metalType !== 'silver') {
                        this.mazduriCost = '';
                    }
                    return;
                }
                const job = this.pendingKarigarJobs.find(j => String(j.karigar_job_id) === String(val));
                if (job) {
                    this.mazduriCost = job.mazduri_cost;
                }
            },
        },
        data() {
            return {
                salesBillNo: '',
                availableSalesBills: [],
                challanMstId: '',
                financialYear: [],
                selectedFinancialYear: '',

                challanNo: '',
                challanDate: '',
                brokerName: '',

                customerName: '',
                gstNo: '',
                code: '',

                address: '',

                productQuality: '',
                totalPieces: '',
                weightGrams: '',

                invoiceDate: '',
                dueDate: '',

                rate: '',
                amount: '',

                gstPercentage: 0,
                gstAmount: '0.00',

                grandTotal: '',

                bank: [],
                selectedBank: '',

                branch: '',

                metalType: 'gold',
                refineryCost: '',
                polishRatePerGram: '',
                mazduriCost: '',
                sellQualityId: '',
                karigarJobId: '',
                pendingKarigarJobs: [],
            }
        },
        computed: {
            polishCostTotal() {
                if (this.metalType !== 'gold') {
                    return '0.00';
                }
                const total = parseFloat(this.polishRatePerGram || 0) * parseFloat(this.weightGrams || 0);
                return Number.isNaN(total) ? '0.00' : total.toFixed(2);
            },
            totalProcessingCost() {
                const refinery = parseFloat(this.refineryCost || 0) || 0;
                const polish = parseFloat(this.polishCostTotal || 0) || 0;
                const mazduri = this.showMazduriField ? (parseFloat(this.mazduriCost || 0) || 0) : 0;
                return (refinery + polish + mazduri).toFixed(2);
            },
            showMazduriField() {
                return this.metalType === 'silver' || !!this.karigarJobId;
            },
            mazduriHelpText() {
                if (this.karigarJobId) {
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
        methods: {
            //this function will take todays date and format it in the form "yyyy-mm-dd"
            getTodaysDate: function () {
                let d = new Date();
                let month = '' + (d.getMonth() + 1);
                let day = '' + d.getDate();
                let year = d.getFullYear();
                if (month < 10) {
                    month = '0' + month;
                }

                if (day < 10) {
                    day = '0' + day;
                }

                return (year + "-" + month + "-" + day);
            },

            getDueDate: function () {
                let d = new Date();
                let month = '' + (d.getMonth() + 1);
                let day = '' + d.getDate();
                let year = d.getFullYear() + 1;
                if (month < 10) {
                    month = '0' + month;
                }

                if (day < 10) {
                    day = '0' + day;
                }

                return (year + "-" + month + "-" + day);
            },

            loadAvailableSalesBills() {
                axios.get('../api/invoice/available-challans').then(response => {
                    this.availableSalesBills = (response.data || []).map(challan => ({
                        value: challan.challan_no,
                        text: challan.challan_no + ' - ' + challan.customer_company_name + ' (' + formatDate(challan.challan_date) + ')',
                    }));
                }).catch(err => {
                    console.log(err);
                    toastr["error"]('Unable to load sales bills.');
                });
            },

            onSalesBillSelected() {
                this.loadFinancialYears();
            },

            loadFinancialYears() {
                if (this.salesBillNo == '') {
                    this.financialYear = [];
                    this.selectedFinancialYear = '';
                    this.resetDisplayDataFields();
                    return;
                }

                axios.get('../api/invoice/getfinancialyear/' + this.salesBillNo).then(response => {
                    if (!response.data || response.data.length === 0) {
                        this.financialYear = [];
                        this.selectedFinancialYear = '';
                        this.resetDisplayDataFields();
                        toastr["warning"]('No sales bill found with this number. Create a sales bill first, or check the number in Manage Sales Bill.');
                        return;
                    }

                    this.financialYear = response.data.map(year => {
                        return {
                            value: year.replace("-", " - "),
                            text: year,
                        }
                    });

                    if (this.financialYear.length === 1) {
                        this.selectedFinancialYear = this.financialYear[0].value;
                        this.loadSalesBillData();
                    }
                }).catch(err => {
                    console.log(err);
                    toastr["error"]('Something went Wrong.');
                })
            },

            loadSalesBillData() {
                if (this.salesBillNo == '' || this.selectedFinancialYear == '' || this.selectedFinancialYear == undefined) {
                    this.resetDisplayDataFields();
                    return;
                }

                let splitYear = this.selectedFinancialYear.split(" - ");
                let fromDateYear = splitYear[0];
                let toDateYear = splitYear[1];

                let fromDate = fromDateYear + '-04-01';
                let toDate = toDateYear + '-03-31';

                axios.get('../api/invoice/challandata/' + this.salesBillNo + '/' + fromDate + '/' + toDate).then(response => {
                    if (response.data.status == -1) {
                        this.resetDisplayDataFields();
                        var errormsg = response.data.errors;
                        toastr["error"](errormsg);
                    } else {
                        this.challanMstId = response.data[1]["challan_mst_id"];
                        this.challanNo = response.data[1]["challan_no"];
                        this.challanDate = formatDate(response.data[1]["challan_date"]);
                        this.brokerName = response.data[1]["broker_name"];
                        this.customerName = response.data[1]["customer_company_name"];
                        this.gstNo = response.data[1]["customer_gst_no"] || '';
                        this.code = response.data[1]["customer_gst_code"] || '';
                        this.address = response.data[1]["customer_address"];
                        this.productQuality = response.data[1]["quality_name"];
                        this.totalPieces = response.data[1]["total_qty"];
                        this.weightGrams = response.data[1]["weight_grams"] || '';
                        this.metalType = response.data[1]["metal_type"] || 'gold';
                        this.sellQualityId = response.data[1]["sell_quality_id"] || '';
                        this.loadPendingKarigarJobs();
                    }

                }).catch(err => {
                    console.log(err);
                    toastr['error']("Something Went Wrong!");
                })
            },

            resetDisplayDataFields() {
                this.challanMstId = '';
                this.challanNo = '';
                this.challanDate = '';
                this.brokerName = '';

                this.customerName = '';
                this.gstNo = '';
                this.code = '';

                this.address = '';

                this.productQuality = '';
                this.totalPieces = '';
                this.weightGrams = '';

                this.rate = '';
                this.amount = '';

                this.gstPercentage = 0;
                this.gstAmount = '0.00';

                this.grandTotal = '';

                this.metalType = 'gold';
                this.refineryCost = '';
                this.polishRatePerGram = '';
                this.mazduriCost = '';
                this.sellQualityId = '';
                this.karigarJobId = '';
                this.pendingKarigarJobs = [];
            },

            loadPendingKarigarJobs() {
                this.karigarJobId = '';
                this.pendingKarigarJobs = [];
                if (!this.sellQualityId) {
                    return;
                }
                axios.get('../api/karigar/jobs/pending-for-sale?sell_quality_id=' + this.sellQualityId)
                    .then(res => {
                        this.pendingKarigarJobs = res.data || [];
                    })
                    .catch(() => {
                        this.pendingKarigarJobs = [];
                    });
            },

            recalculateTotals() {
                if (this.rate === '' || this.weightGrams === '') {
                    this.amount = '';
                    this.gstAmount = '0.00';
                    this.grandTotal = '';
                    return;
                }

                const base = parseFloat(this.rate) * parseFloat(this.weightGrams);
                const gstPct = parseFloat(this.gstPercentage) || 0;
                const gst = base * gstPct * 0.01;

                this.amount = base.toFixed(2);
                this.gstAmount = gst.toFixed(2);
                this.grandTotal = (base + gst).toFixed(2);
            },

            getBank() {
                axios.get('../api/bankinfo').then(response => {
                    this.bank = response.data.map(bank => {
                        return {
                            value: bank.bank_details_id,
                            text: bank.bank_name + ' - ' + bank.account_no
                        }
                    })
                }).catch(err => {
                    console.log(err);
                    toastr["error"]('Something went Wrong.');
                });
            },

            getBranchName() {
                if (this.selectedBank == '' || this.selectedBank == undefined) {
                    this.branch = '';
                    return;
                }
                axios.get('../api/bankbranch/' + this.selectedBank).then(response => {
                    this.branch = response.data.branch_name;
                }).catch(err => {
                    console.log(err);
                    toastr["error"]('Something went Wrong!');
                })
            },

            addInvoice() {
                var addData = {};
                addData["invoiceId"] = this.challanMstId;
                addData["invoiceDate"] = this.invoiceDate;
                addData["rate"] = this.rate;
                addData["gstPercentage"] = this.gstPercentage;
                addData["bankId"] = this.selectedBank;
                addData["dueDate"] = this.dueDate;

                let splitYear = this.selectedFinancialYear.split(" - ");
                let fromDateYear = splitYear[0];
                let toDateYear = splitYear[1];

                let fromDate = fromDateYear + '-04-01';
                let toDate = toDateYear + '-03-31';

                addData["fromDate"] = fromDate;
                addData["toDate"] = toDate;
                addData["refineryCost"] = this.refineryCost || 0;
                addData["polishRatePerGram"] = this.polishRatePerGram || 0;
                addData["mazduriCost"] = this.mazduriCost || 0;
                if (this.karigarJobId) {
                    addData["karigarJobId"] = this.karigarJobId;
                }

                if (this.salesBillNo == '' || this.challanMstId == '' || this.invoiceDate == '' || this.dueDate == '' || this.rate == '' || this.selectedBank == '' || this.selectedBank == undefined || this.selectedFinancialYear == '' || this.selectedFinancialYear == undefined) {
                    toastr["error"]("All Fields are Required!");
                } else {
                    axios.get('../api/verifyinvoicedate/' + this.invoiceDate + '/' + fromDate + '/' + toDate).then(response => {
                        if (response.data.status == 1) {
                            axios.post('../api/invoice/insert', addData).then(response => {
                                if (response.data.status == -1) {
                                    toastr.error(response.data.message || 'Unable to create invoice.');
                                    var errormsg = response.data.errors;

                                    try {
                                        if (errormsg.invoiceId)
                                            toastr["error"](errormsg.invoiceId)
                                    } catch (err) { }

                                    try {
                                        if (errormsg.invoiceDate)
                                            toastr["error"](errormsg.invoiceDate)
                                    } catch (err) { }

                                    try {
                                        if (errormsg.rate)
                                            toastr["error"](errormsg.rate)
                                    } catch (err) { }

                                    try {
                                        if (errormsg.gstPercentage)
                                            toastr["error"](errormsg.gstPercentage)
                                    } catch (err) { }

                                    try {
                                        if (errormsg.bankId)
                                            toastr["error"](errormsg.bankId)
                                    } catch (err) { }

                                    try {
                                        if (errormsg.dueDate)
                                            toastr["error"](errormsg.dueDate)
                                    } catch (err) { }

                                } else if (response.data.status == 0) {
                                    toastr["warning"](response.data.message);
                                } else if (response.data.status == 1) {
                                    const profit = response.data.profit != null ? parseFloat(response.data.profit).toFixed(2) : '0.00';
                                    swal.fire({
                                        title: 'Success',
                                        html: "<h5 style='color:#9C9794'>Invoice created successfully.</h5><p>Profit recorded: Rs. " + profit + "</p>",
                                        icon: 'success'
                                    }).then((result) => {
                                        this.resetFields();
                                    });
                                }

                            }).catch(err => {
                                console.log(err);
                                toastr.error(err.response?.data?.message || 'Unable to create invoice. Check item type stock and weight.');
                            })
                        }else if(response.data.status == 0){
                            toastr["error"](response.data.message);
                        }
                    })
                }
            },

            resetFields() {
                this.salesBillNo = '';
                this.challanMstId = '';
                this.financialYear = [];
                this.selectedFinancialYear = '';
                this.resetDisplayDataFields();
                this.invoiceDate = getNowDateTime();
                this.dueDate = this.getDueDate();
                this.selectedBank = '';
                this.branch = '';
                this.loadAvailableSalesBills();
            }
        }
    };
</script>

<!-- This style will be applicable for this page only which will hide the buttons in the number field-->
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

    input:disabled,
    textarea:disabled {
        cursor: not-allowed;
        opacity: .6;
        background: red;
    }
</style>