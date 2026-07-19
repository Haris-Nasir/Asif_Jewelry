<template>
    <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12 mt-3">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">{{ $t('invoice.fromChallanTitle') }}</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                                class="fas fa-minus"></i></button>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="salesBillNo" class="text-md col-form-label">{{ $t('invoice.salesBillNo') }} <span
                                                    class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-4">
                                            <model-select
                                                :options="availableSalesBills"
                                                v-model="salesBillNo"
                                                @input="onSalesBillSelected"
                                                :placeholder="$t('invoice.selectSalesBill')">
                                            </model-select>
                                            <small class="text-muted">{{ $t('invoice.helperNotInvoiced') }}</small>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="financialYear" class="text-md col-form-label">{{ $t('invoice.financialYear') }}
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <model-select :options="financialYear" v-model="selectedFinancialYear"
                                                @input="loadSalesBillData"
                                                :placeholder="$t('invoice.selectFy')">
                                            </model-select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="challanNo" class="text-md col-form-label">{{ $t('challan.no') }}
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="text-md text-right form-control"
                                                v-model="challanNo" disabled>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="challanDate" class="text-md col-form-label">{{ $t('challan.date') }}
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="text-md form-control" v-model="challanDate"
                                                disabled>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="brokerName" class="text-md col-form-label">{{ $t('invoice.brokerName') }}
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="text-md form-control" v-model="brokerName"
                                                disabled>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="customerName" class="text-md col-form-label">{{ $t('invoice.customerName') }}
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="text-md form-control" v-model="customerName"
                                                disabled>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="gstNo" class="text-md col-form-label">{{ $t('invoice.gstNumber') }}</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="text-md text-right form-control" v-model="gstNo"
                                                disabled>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="code" class="text-md col-form-label">{{ $t('invoice.code') }}</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="text-md text-right form-control" v-model="code"
                                                disabled>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="address" class="text-md col-form-label">{{ $t('common.address') }}
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea class="text-md form-control" v-model="address"
                                                disabled></textarea>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label class="text-md col-form-label">{{ $t('invoice.products') }}</label>
                                        </div>
                                        <div class="col-md-10">
                                            <div class="table-responsive" v-if="lineItems.length">
                                                <table class="table table-bordered table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ $t('common.quality') }}</th>
                                                            <th class="text-right">{{ $t('common.weightG') }}</th>
                                                            <th class="text-right">{{ $t('common.totalPieces') }}</th>
                                                            <th class="text-right">{{ $t('common.ratePerG') }}</th>
                                                            <th class="text-right">{{ $t('invoice.lineAmount') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(line, index) in lineItems" :key="index">
                                                            <td>{{ $label(line.qualityName) }}</td>
                                                            <td class="text-right">{{ Number(line.weightGrams || 0).toFixed(3) }}</td>
                                                            <td class="text-right">{{ line.qty }}</td>
                                                            <td>
                                                                <input type="number" step="0.01" class="form-control form-control-sm text-right"
                                                                    v-model="line.rate" @input="recalculateTotals">
                                                            </td>
                                                            <td class="text-right">{{ lineAmount(line) }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div v-else class="form-group row mb-0">
                                                <div class="col-md-4">
                                                    <input type="text" class="text-md form-control" v-model="productQuality" disabled>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="text-md text-right form-control" v-model="totalPieces" disabled>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="text-md text-right form-control" v-model="weightGrams" disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row" v-if="pendingKarigarJobs.length">
                                        <div class="col-md-2">
                                            <label class="text-md col-form-label">{{ $t('common.karigarJob') }}</label>
                                        </div>
                                        <div class="col-md-4">
                                            <model-select :options="karigarJobOptions" v-model="karigarJobId"
                                                :placeholder="$t('common.linkKarigarOptional')">
                                            </model-select>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">{{ $t('invoice.helperKarigarFrom') }}</small>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="invoiceDate" class="text-md col-form-label">{{ $t('common.invoiceDate') }} <span
                                                    class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="datetime-local" class="text-md form-control" v-model="invoiceDate">
                                        </div>

                                        <div class="col-md-2">
                                            <label for="dueDate" class="text-md col-form-label">{{ $t('common.dueDate') }} <span
                                                    class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="date" class="text-md form-control" v-model="dueDate">
                                        </div>
                                    </div>

                                    <div class="form-group row" v-if="lineItems.length <= 1">
                                        <div class="col-md-2">
                                            <label for="rate" class="text-md col-form-label">{{ $t('common.ratePerG') }}
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" step="0.01" class="text-md text-right form-control"
                                                v-model="rate" :placeholder="$t('invoice.phRate')"
                                                @change="onSingleRateChange">
                                            <small class="text-muted">{{ $t('invoice.helperAmountWeight') }}</small>
                                        </div>

                                        <div class="col-md-2">
                                            <label for="amount" class="text-md col-form-label">{{ $t('common.baseAmount') }}
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="text-md text-right form-control"
                                                v-model="amount" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group row" v-else>
                                        <div class="col-md-2">
                                            <label class="text-md col-form-label">{{ $t('common.baseAmount') }}</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="text-md text-right form-control" v-model="amount" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="gstPercentage" class="text-md col-form-label">{{ $t('common.gst') }}</label>
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
                                            <label for="gstAmount" class="text-md col-form-label">{{ $t('common.gstAmount') }}</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="text-md text-right form-control"
                                                v-model="gstAmount" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group row" v-if="hasGoldItem">
                                        <div class="col-md-2">
                                            <label class="text-md col-form-label">{{ $t('common.polishPerG') }}</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" step="0.01" min="0" class="text-md text-right form-control"
                                                v-model="polishRatePerGram">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="text-md col-form-label">{{ $t('common.polishTotal') }}</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="text-md text-right form-control" :value="polishCostTotal" disabled>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">{{ $t('invoice.helperPolish') }}</small>
                                        </div>
                                    </div>

                                    <div class="form-group row" v-if="showMazduriField">
                                        <div class="col-md-2">
                                            <label class="text-md col-form-label">{{ $t('common.mazduri') }}</label>
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
                                            <label class="text-md col-form-label">{{ $t('common.processingCost') }}</label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="text-md text-right form-control" :value="totalProcessingCost" disabled>
                                        </div>
                                        <div class="col-md-8">
                                            <small class="text-muted">{{ $t('invoice.helperProcessing') }}</small>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="grandTotal" class="text-md col-form-label">{{ $t('invoice.grandTotal') }} <span
                                                    class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="text-md text-right form-control"
                                                v-model="grandTotal" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="bank" class="text-md col-form-label">{{ $t('common.bank') }} <span
                                                    class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-3">
                                            <model-select :options="bank" v-model="selectedBank"
                                                :placeholder="$t('common.selectBank')" @blur="getBranchName">
                                            </model-select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="branch" class="text-md col-form-label">{{ $t('invoice.branch') }} <span
                                                    class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" class="text-md form-control" v-model="branch" disabled>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary text-md"
                                        @click="addInvoice">{{ $t('common.add') }}</button>
                                    <button type="reset" class="btn btn-primary ml-3 text-md"
                                        @click="resetFields">{{ $t('common.reset') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
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
                lineItems: [],

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
                polishRatePerGram: '',
                mazduriCost: '',
                sellQualityId: '',
                karigarJobId: '',
                pendingKarigarJobs: [],
            }
        },
        computed: {
            polishCostTotal() {
                if (!this.hasGoldItem) {
                    return '0.00';
                }
                const total = parseFloat(this.polishRatePerGram || 0) * parseFloat(this.weightGrams || 0);
                return Number.isNaN(total) ? '0.00' : total.toFixed(2);
            },
            hasGoldItem() {
                if (this.lineItems.length) {
                    return this.lineItems.some((line) => (line.metalType || 'gold') === 'gold');
                }
                return this.metalType === 'gold';
            },
            hasSilverItem() {
                if (this.lineItems.length) {
                    return this.lineItems.some((line) => (line.metalType || '') === 'silver');
                }
                return this.metalType === 'silver';
            },
            totalProcessingCost() {
                const polish = this.hasGoldItem
                    ? (parseFloat(this.polishCostTotal || 0) || 0)
                    : 0;
                return (polish + this.appliedMazduriAmount).toFixed(2);
            },
            appliedMazduriAmount() {
                if (this.hasSilverItem) {
                    return parseFloat(this.mazduriCost || 0) || 0;
                }
                if (this.karigarJobId) {
                    return parseFloat(this.mazduriCost || 0) || 0;
                }
                return 0;
            },
            showMazduriField() {
                return this.hasSilverItem;
            },
            mazduriHelpText() {
                if (this.karigarJobId) {
                    return this.$t('invoice.helperMazduriKarigar');
                }
                return this.$t('invoice.helperMazduriSilver');
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
                    toastr["error"](this.$t('invoice.loadBillsFail'));
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
                        toastr["warning"](this.$t('invoice.noBillFoundDetailed'));
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
                    toastr["error"](this.$t('common.somethingWrong'));
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
                        this.lineItems = (response.data[1]["items"] || []).map((item) => ({
                            challanDetailsId: item.challanDetailsId,
                            categoryId: item.categoryId,
                            qualityId: item.qualityId,
                            qualityName: item.qualityName,
                            metalType: item.metalType || 'gold',
                            qty: item.qty,
                            weightGrams: item.weightGrams,
                            unit: item.unit || 'pcs',
                            rate: '',
                        }));
                        if (this.lineItems.length) {
                            this.weightGrams = this.lineItems.reduce((sum, line) => sum + parseFloat(line.weightGrams || 0), 0).toFixed(3);
                            this.totalPieces = this.lineItems.reduce((sum, line) => sum + parseFloat(line.qty || 0), 0);
                            this.metalType = this.lineItems[0].metalType || 'gold';
                            this.sellQualityId = this.lineItems[0].qualityId || this.sellQualityId;
                        }
                        this.loadPendingKarigarJobs();
                        this.recalculateTotals();
                    }

                }).catch(err => {
                    console.log(err);
                    toastr['error'](this.$t('common.somethingWrong'));
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
                this.lineItems = [];

                this.rate = '';
                this.amount = '';

                this.gstPercentage = 0;
                this.gstAmount = '0.00';

                this.grandTotal = '';

                this.metalType = 'gold';
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

            lineAmount(line) {
                const amount = parseFloat(line.weightGrams || 0) * parseFloat(line.rate || 0);
                return Number.isNaN(amount) ? '0.00' : amount.toFixed(2);
            },
            onSingleRateChange() {
                if (this.lineItems.length === 1) {
                    this.lineItems[0].rate = this.rate;
                }
                this.recalculateTotals();
            },
            recalculateTotals() {
                if (this.lineItems.length) {
                    const totalAmount = this.lineItems.reduce(
                        (sum, line) => sum + parseFloat(line.weightGrams || 0) * parseFloat(line.rate || 0),
                        0
                    );
                    const gstAmount = totalAmount * parseFloat(this.gstPercentage || 0) * 0.01;
                    this.amount = totalAmount.toFixed(2);
                    this.gstAmount = gstAmount.toFixed(2);
                    this.grandTotal = (totalAmount + gstAmount).toFixed(2);
                    if (this.lineItems.length === 1) {
                        this.rate = this.lineItems[0].rate;
                    }
                    return;
                }

                if (this.rate === '' || this.weightGrams === '') {
                    this.amount = '';
                    this.gstAmount = '0.00';
                    this.grandTotal = '';
                    return;
                }

                const totalAmount = parseFloat(this.weightGrams || 0) * parseFloat(this.rate || 0);
                const gstAmount = totalAmount * parseFloat(this.gstPercentage || 0) * 0.01;
                this.amount = totalAmount.toFixed(2);
                this.gstAmount = gstAmount.toFixed(2);
                this.grandTotal = (totalAmount + gstAmount).toFixed(2);
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
                    toastr["error"](this.$t('common.somethingWrong'));
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
                    toastr["error"](this.$t('common.somethingWrong'));
                })
            },

            addInvoice() {
                if (!this.salesBillNo || !this.challanMstId || !this.invoiceDate || !this.dueDate || !this.selectedFinancialYear) {
                    toastr.error(this.$t('common.allFieldsRequired'));
                    return;
                }

                if (this.lineItems.length) {
                    for (let i = 0; i < this.lineItems.length; i++) {
                        if (this.lineItems[i].rate === '' || parseFloat(this.lineItems[i].rate) < 0) {
                            toastr.info(this.$t('invoice.lineN', { n: i + 1 }) + ': ' + this.$t('invoice.rateRequired'));
                            return;
                        }
                    }
                } else if (this.rate === '') {
                    toastr.error(this.$t('common.allFieldsRequired'));
                    return;
                }

                var addData = {};
                addData["invoiceId"] = this.challanMstId;
                addData["invoiceDate"] = this.invoiceDate;
                addData["gstPercentage"] = this.gstPercentage;
                addData["bankId"] = this.selectedBank || null;
                addData["dueDate"] = this.dueDate;

                let splitYear = this.selectedFinancialYear.split(" - ");
                let fromDateYear = splitYear[0];
                let toDateYear = splitYear[1];

                let fromDate = fromDateYear + '-04-01';
                let toDate = toDateYear + '-03-31';

                addData["fromDate"] = fromDate;
                addData["toDate"] = toDate;
                addData["polishRatePerGram"] = this.polishRatePerGram || 0;
                addData["mazduriCost"] = this.mazduriCost || 0;
                if (this.karigarJobId) {
                    addData["karigarJobId"] = this.karigarJobId;
                }

                if (this.lineItems.length) {
                    addData["items"] = this.lineItems.map((line) => ({
                        challanDetailsId: line.challanDetailsId,
                        categoryId: line.categoryId,
                        qualityId: line.qualityId,
                        qty: line.qty,
                        weightGrams: line.weightGrams,
                        unit: line.unit,
                        rate: line.rate,
                    }));
                } else {
                    addData["rate"] = this.rate;
                }

                axios.get('../api/verifyinvoicedate/' + this.invoiceDate + '/' + fromDate + '/' + toDate).then(response => {
                    if (response.data.status == 1) {
                        axios.post('../api/invoice/insert', addData).then(response => {
                            if (response.data.status == -1) {
                                toastr.error(response.data.message || this.$t('invoice.createInvoiceFail'));
                            } else if (response.data.status == 0) {
                                toastr.warning(response.data.message);
                            } else if (response.data.status == 1) {
                                const profit = response.data.profit != null ? parseFloat(response.data.profit).toFixed(2) : '0.00';
                                swal.fire({
                                    title: this.$t('common.success'),
                                    html: "<h5 style='color:#9C9794'>" + this.$t('invoice.fromCreated') + "</h5><p>" + this.$t('invoice.profitRecorded', { profit }) + "</p>",
                                    icon: 'success'
                                }).then(() => {
                                    this.resetFields();
                                });
                            }
                        }).catch(err => {
                            toastr.error(err.response?.data?.message || this.$t('invoice.createInvoiceFailDetailed'));
                        });
                    } else if (response.data.status == 0) {
                        toastr.error(response.data.message);
                    }
                }).catch(() => toastr.error(this.$t('common.somethingWrong')));
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