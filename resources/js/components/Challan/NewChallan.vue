<!--
DESCRIPTION
    This module generates a Challan where user have to enter info. related to purchased stocks
    and add that information to Databse
NOTES
    Version         : 1.0
    Date            : 01/10/2021
    Author          : Vraj Shah

    Initial Release : v1.0: Initial Release
-->

<template>
    <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12 mt-3">
                            <div class="card card-primary">
                                <!-- card Header of  New Challan -->
                                <div class="card-header">
                                    <h3 class="card-title">{{ $t('challan.newTitle') }}</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                                class="fas fa-minus"></i></button>
                                    </div>
                                </div>

                                <!-- card Body of New Challan -->
                                <div class="card-body">
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="challanDate" class="text-md col-form-label">{{ $t('common.dateTime') }} <span
                                                    class="required-mark" style="color: red;">*</span></label>
                                        </div>

                                        <!-- here I have called a resetChallanNo function which will be called when challan Date changes -->
                                        <div class="col-md-3">
                                            <input type="datetime-local" id="challanDate" v-model="challanDate"
                                                class="form-control text-md" @change="loadNextChallanNo">
                                        </div>

                                        <!-- next sales bill number is assigned automatically per financial year -->
                                        <div class="col-md-2 ml-auto">
                                            <label for="challanNo" class="text-md col-form-label">{{ $t('challan.no') }}</label>
                                        </div>

                                        <div class="col-md-3 mr-5">
                                            <input type="text" class="text-md form-control" v-model="challanNo" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="companyName" class="text-md col-form-label">{{ $t('common.customer') }} <span
                                                    class="required-mark" style="color: red;">*</span></label>
                                        </div>

                                        <!-- here I have called an getFromSelectedCompany function which will be called when i move outside from the company Name field -->
                                        <div class="col-md-3">
                                            <model-select :options="companyNames" v-model="selectedCompanyName"
                                                @blur="getFromSelectedCompany" :placeholder="$t('common.selectCustomer')">
                                            </model-select>
                                        </div>

                                        <div class="col-md-2 ml-auto">
                                            <label for="brokerName" class="text-md col-form-label">{{ $t('broker.name') }} <span
                                                    class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-3 mr-5">
                                            <model-select :options="brokerNames" v-model="selectedBrokerName"
                                                :placeholder="$t('common.selectBrokerName')">
                                            </model-select>
                                        </div>
                                    </div>

                                    <!-- Here contact number and gst number will be populated autommatically when wes select a company -->
                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="companyContactNo" class="text-md col-form-label">{{ $t('common.companyContact') }}
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" class="text-md form-control" v-model="companyContactNo"
                                                disabled>
                                        </div>

                                        <div class="col-md-2 ml-auto">
                                            <label for="companyGSTNo" class="text-md col-form-label">{{ $t('common.companyGst') }}
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-3 mr-5">
                                            <input type="text" class="text-md form-control" v-model="companyGSTNo"
                                                disabled>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="productCategory" class="text-md col-form-label">{{ $t('common.productCategory') }}
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>

                                        <!-- here I have called an loadFromSelectedCategory function which will be called when we move outside the product category field -->
                                        <div class="col-md-3">
                                            <model-select :options="productCategories" v-model="selectedProductCategory"
                                                @blur="loadFromSelectedCategory"
                                                :placeholder="$t('common.selectProductCategory')">
                                            </model-select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="productQuality" class="text-md col-form-label">{{ $t('common.productQuality') }}
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-3">
                                            <model-select :options="productQualities" v-model="selectedProductQuality"
                                                :placeholder="$t('common.selectProductQuality')">
                                            </model-select>
                                        </div>

                                        <div class="col-md-2 ml-auto">
                                            <label for="Unit" class="text-md col-form-label">{{ $t('common.unit') }}
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-3 mr-5">
                                            <input type="text" class="text-md form-control" v-model="unit" disabled>
                                        </div>
                                    </div>

                                    <div class="row overflow-auto" style="max-height: 300px;min-height: 300px;">
                                        <div class="col-md-6 table-responsive">
                                            <table class="table table-bordered">
                                                <thead class="table-secondary text-md text-dark">
                                                    <th>{{ $t('common.srNo') }}</th>
                                                    <th>{{ $t('common.quantity') }}</th>
                                                    <th></th>
                                                </thead>
                                                <tbody>
                                                    <!-- this table body will be called when the index number is an even number and will add two ffields and 1 button in the row  -->
                                                    <tr v-for="(data, index) in allData" :key="index">
                                                        <td v-if="index % 2 ? 0 : 1">
                                                            {{index + 1}}
                                                        </td>
                                                        <td v-if="index % 2 ? 0 : 1">
                                                            <input type="number" class="form-control text-right"
                                                                v-model="data.qty" @blur="sumTotalQuantity"
                                                                @click="selectQuantity(index)"
                                                                @keydown.tab.prevent="tranferCursor(index)"
                                                                @keyup.enter="enterPressed(index)" :ref="'qty'+index">
                                                        </td>
                                                        <td v-if="index % 2 ? 0 : 1">
                                                            <button class="btn btn-danger text-md" @click="deleteRow(index)"><i
                                                                    class="fas fa-trash"></i></button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="col-md-6 table-responsive">
                                            <table class="table table-bordered">
                                                <thead class="table-secondary text-md text-dark">
                                                    <th>{{ $t('common.srNo') }}</th>
                                                    <th>{{ $t('common.quantity') }}</th>
                                                    <th></th>
                                                </thead>
                                                <tbody>
                                                    <!-- this table body will be called when the index number is an odd number and will add two fields and 1 button in the row  -->
                                                    <tr v-for="(data, index) in allData" :key="index">
                                                        <td v-if="index % 2 ? 1 : 0">
                                                            {{index + 1}}
                                                        </td>
                                                        <td v-if="index % 2 ? 1 : 0">
                                                            <input type="number" class="form-control text-right"
                                                                v-model="data.qty" @blur="sumTotalQuantity"
                                                                @click="selectQuantity(index)"
                                                                @keydown.tab.prevent="tranferCursor(index)"
                                                                @keyup.enter="enterPressed(index)" :ref="'qty'+index">
                                                        </td>
                                                        <td v-if="index % 2? 1 : 0">
                                                            <button class="btn btn-danger text-md" @click="deleteRow(index)"><i
                                                                    class="fas fa-trash"></i></button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- when we click on add product button add row function will be called -->
                                    <button class="btn btn-primary text-md mt-2" @click="addRow">{{ $t('common.addProduct') }}</button>

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="weightGrams" class="text-md col-form-label mt-3">{{ $t('common.totalWeightG') }}
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" step="0.001" class="text-md form-control mt-3 text-right"
                                                v-model="weightGrams" :placeholder="$t('challan.phWeight')">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="totalQty" class="text-md col-form-label mt-3">{{ $t('common.totalPieces') }}
                                                <span class="required-mark" style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" class="text-md form-control mt-3 text-right"
                                                v-model="totalQty" disabled>
                                        </div>
                                        <div class="col-md-12" v-if="qualityStock">
                                            <p class="text-muted small mt-2 mb-0">
                                                {{ $t('challan.inStockFor') }} <strong>{{ qualityStock.quality_name }}</strong>:
                                                {{ qualityStock.weight_grams }}g, {{ qualityStock.pieces }} pcs
                                                <span v-if="qualityStock.available_piece_weights_label">
                                                    ({{ $t('common.inStockPieces') }}:
                                                    {{ qualityStock.available_piece_weights_label }})
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer">
                                    <button type="submit" @click="addChallan"
                                        class="btn btn-primary text-md">{{ $t('common.add') }}</button>
                                    <button type="reset" @click="resetFields"
                                        class="btn btn-primary ml-3 text-md">{{ $t('common.reset') }}</button>
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
    import { getNowDateTime, toDateOnly } from "../../currency";

    //toastr options contains properties of the alerts so on firing it will display as per the below options
    toastr.options = {
        closeButton: true,
        closeDuration: 200,
        progressBar: true,
        newestOnTop: true,
        positionClass: "toast-top-center",
    };

    //it contains all the data properties and methods of all the events.
    export default {
        name: 'NewChallan',
        components: {
            ModelSelect,
        },
        data() {
            return {
                /*this are all the data properties which I have used as a v-model and here I have initailized it all*/
                challanDate: '',
                challanNo: '',
                companyNames: [],
                selectedCompanyName: '',
                brokerNames: [],
                selectedBrokerName: '',
                companyContactNo: '',
                companyGSTNo: '',
                productCategories: [],
                selectedProductCategory: '',
                productQualities: [],
                selectedProductQuality: '',
                unit: '',
                allData: [],
                totalQty: (0).toFixed(2),
                weightGrams: '',
                qualityStock: null,
                status: null,
                message: null,
                errors: null
            }
        },

        /*whatever we write in the mounted function will load on page refresh so here we have called some functions 
        like to display todays date, populate options for Company Name, Broker Name and Quality Categories on refreshing 
        the page.*/
        mounted() {
            this.challanDate = getNowDateTime();
            this.loadCompanyName();
            this.loadBrokerName();
            this.loadQualityCategories();
            this.loadNextChallanNo();
        },

        watch: {
            selectedProductQuality() {
                this.loadQualityStock();
            },
        },

        methods: {
            //this function will take todays date and format it in the form "yyyy-mm-dd"
            getTodaysDate: function () {
                let d = new Date()
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

            /* this function will be called when we click on Add Product button so on clicking it one row will be added
            and also have given a limit that no one could enter a row more than 48.*/
            addRow: function () {
                if (this.allData.length < 48) {
                    this.allData.push({
                        qty: (0).toFixed(2)
                    })

                } else {
                    toastr["warning"](this.$t('challan.limit48'));
                }
            },

            //this function is used for entering a row by pressing enter key
            enterPressed: function (index = -1) {
                if (this.allData.length == (index + 1)) {
                    this.addRow();
                }
            },

            //this function will be called when we click on trash icon in the table which will delete that particular row   
            deleteRow: function (index) {
                this.allData.splice(index, 1);
                this.sumTotalQuantity();
            },

            //this function is used when we click the tab key so it will transfer the cursor to next input field 
            tranferCursor: function (index) {
                if (this.allData.length == (index + 1)) {
                    return;
                }
                this.$refs['qty' + (index + 1)][0].focus();
            },

            /*this function will call an api customerlist which will get all the customer informaiation like id, name and
            contact number and will populate in the searchable dropdown menu having value as an id and tet as an 
            mixture of company name and contact number*/
            loadCompanyName() {
                axios.get('../api/customerlist').then((response) => {
                    this.companyNames = response.data.map(company => {
                        return {
                            value: company.customer_id,
                            text: company.customer_company_name + ' - ' + company.customer_contact_no
                        }
                    });
                }).catch(err => {
                    console.log(err);
                    toastr["error"](this.$t('common.somethingWrong'))
                })
            },

            /*this function will call an api brokerslist which will get all the broker informaiation like id, name and
            contact number and will populate in the searchable dropdown menu having value as an id and tet as an 
            mixture of company name and contact number*/
            loadBrokerName() {
                axios.get('../api/brokerslist').then((response) => {
                    this.brokerNames = response.data.map(broker => {
                        return {
                            value: broker.broker_id,
                            text: broker.broker_name + ' - ' + broker.broker_contact_no
                        }
                    })
                }).catch(err => {
                    console.log(err);
                    toastr["error"](this.$t('common.somethingWrong'))
                })
            },

            /*this function will call an api sellqualitycategories which will get all the sell quality category 
            informaiation like id and name and will populate in the searchable dropdown menu having value as an 
            id and tet as a company name*/
            loadQualityCategories() {
                axios.get('../api/sellqualitycategories').then((response) => {
                    this.productCategories = response.data.qualityCategories.map(c => this.$categoryOption(c));
                }).catch(err => {
                    console.log(err);
                    toastr["error"](this.$t('common.somethingWrong'))
                })
            },

            /*this function will call when we select company name and we will call an api selected customer data with
            selected company id so that it will get all the information of the customer like contactNo and GST No
            of that particular selected customer*/
            getFromSelectedCompany: function () {
                /*this condition will be true if we have not selected a company name then it will 
                set the null value in contact no and gst no filed*/
                if (this.selectedCompanyName == '' || typeof (this.selectedCompanyName) === 'undefined') {
                    this.companyContactNo = '';
                    this.companyGSTNo = '';
                    return;
                }

                axios.get('../api/selectedcustomerdata/' + this.selectedCompanyName).then(response => {
                    this.companyContactNo = response.data.customer_contact_no;
                    this.companyGSTNo = response.data.customer_gst_no;
                }).catch(err => {
                    console.log(err);
                    toastr["error"](this.$t('common.somethingWrong'))
                })
            },

            /*this function will call when we select product category and we will call an api sellquality 
            category with select product category id s that it will get all quality of that category and populate
            all the value in the options of that particular selected product category and null value to the unit field*/
            loadFromSelectedCategory: function () {
                /*this condition will be true if we have not selected a product category then it will 
                set the quality options to null*/
                if (this.selectedProductCategory == '' || typeof (this.selectedProductCategory) === 'undefined') {
                    this.unit = '';
                    this.productQualities = [];
                    return;
                }

                axios.get('../api/sellqualityofcategory/' + this.selectedProductCategory).then(response => {
                    this.productQualities = response.data.map(quality => {
                        return {
                            value: quality.sell_quality_id,
                            text: quality.quality_name
                        }
                    })

                    this.unit = "pcs";
                }).catch(err => {
                    console.log(err);
                    toastr["error"](this.$t('common.somethingWrong'))
                })
            },

            // this function will sum all the enetered quantities in the table and round off with precision 2
            sumTotalQuantity: function () {
                this.totalQty = (0).toFixed(2);
                for (let i = 0; i < this.allData.length; i++) {
                    this.totalQty = parseFloat(this.totalQty) + parseFloat(this.allData[i].qty);
                }
                if (this.totalQty != 0.00) {
                    this.totalQty = this.totalQty.toFixed(2);
                }
            },

            loadQualityStock: function () {
                if (!this.selectedProductQuality) {
                    this.qualityStock = null;
                    return;
                }

                axios.get('/api/stock/quality/' + this.selectedProductQuality)
                    .then((res) => {
                        this.qualityStock = res.data;
                    })
                    .catch((err) => {
                        console.log(err);
                        this.qualityStock = null;
                    });
            },

            loadNextChallanNo() {
                if (!this.challanDate) {
                    return;
                }

                axios.get('../api/challan/next-no/' + toDateOnly(this.challanDate)).then(response => {
                    this.challanNo = response.data.nextChallanNo;
                }).catch(err => {
                    console.log(err);
                    toastr["error"](this.$t('challan.loadNextNoFail'));
                });
            },
            
            /*this function will call an api get financial api by taking the challan date and will get a response of the
            financial year of entered challan date*/
            getFinancialYearOfChallanDate() {
                axios.get('../api/getfinancialyear/' + toDateOnly(this.challanDate)).then(response => {
                    this.verifyChallanNo(response.data.fromDate, response.data.toDate);
                }).catch(err => {
                    console.log(err);
                    toastr["error"](this.$t('common.somethingWrong'))
                })
            },

            //this function will take the challan date and the financial year and check if the entered challan number exists or not 
            verifyChallanNo(fromDate, toDate) {
                if (this.challanNo == '') {
                    return;
                }
                axios.get('../api/verifychallan/' + this.challanNo + '/' + fromDate + '/' + toDate).then(response => {
                    if (response.data.status == 0) {
                        toastr["error"](response.data.message);
                        this.challanNo = '';
                    }
                }).catch(err => {
                    console.log(err);
                    toastr["error"](this.$t('common.somethingWrong'))
                })

            },

            validateSaleWeightRatio() {
                if (
                    !this.qualityStock ||
                    !this.qualityStock.available_piece_weights ||
                    !this.qualityStock.available_piece_weights.length
                ) {
                    return true;
                }

                const pieces = parseInt(this.totalQty || 0, 10);
                const totalWeight = parseFloat(this.weightGrams || 0);

                if (pieces <= 0 || totalWeight <= 0) {
                    return true;
                }

                const weightPerPiece = Number((totalWeight / pieces).toFixed(3));
                const matchingCount = this.qualityStock.available_piece_weights.filter(
                    weight => Math.abs(parseFloat(weight) - weightPerPiece) <= 0.0005
                ).length;

                if (matchingCount < pieces) {
                    toastr.error(this.$t('challan.cannotSell', {
                        n: pieces,
                        weight: weightPerPiece.toFixed(3),
                        quality: this.qualityStock.quality_name,
                        list: this.qualityStock.available_piece_weights_label,
                    }));
                    return false;
                }

                return true;
            },

            //this function will be called when we click on add challan button and take all the field values and enter it into the database by calling api
            addChallan() {
                //below addData objects conatins all field values information
                var addData = {};
                addData["challanNo"] = this.challanNo;
                addData["challanDate"] = this.challanDate;
                addData["customerId"] = this.selectedCompanyName;
                addData["sellCategoryId"] = this.selectedProductCategory;
                addData["sellQualityId"] = this.selectedProductQuality;
                addData["qtyUnit"] = this.unit;
                addData["totalQty"] = this.totalQty;
                addData["weightGrams"] = this.weightGrams;
                addData["brokerId"] = this.selectedBrokerName;
                addData["allData"] = this.allData.map((row, index) => ({
                    no: index + 1,
                    qty: row.qty,
                }));

                axios.get('../api/getfinancialyear/' + toDateOnly(this.challanDate)).then(response => {
                    addData["fromDate"] = response.data.fromDate;
                    addData["toDate"] = response.data.toDate;
                    //here it will check if any of the field is empty or not
                    if (this.challanDate == '' || this.selectedCompanyName == '' || this.selectedProductQuality == '' || this.unit == '' || this.totalQty === '' || this.weightGrams === '' || parseFloat(this.weightGrams) <= 0 || this.selectedBrokerName == '') {
                        toastr["error"](this.$t('challan.allFieldsRequired'));
                    } else if (this.allData.length == 0) {//here it will check whether we have entered 1 product or not
                        toastr["error"](this.$t('challan.insertProduct'));
                    } else {
                        for (let i = 0; i < this.allData.length; i++) {
                            if (this.allData[i].qty === '' || parseFloat(this.allData[i].qty) <= 0) {
                                toastr["error"](this.$t('challan.rowQtyInvalid', { n: i + 1 }));
                                return;
                            }
                        }

                        if (!this.validateSaleWeightRatio()) {
                            return;
                        }

                        axios.post('../api/challan/insert', addData)
                            .then((res) => {
                                //status -1 indiactes an error 0 indictaes an warning and 1 indictaes an success message 
                                if (res.data.status == -1) {
                                    if (res.data.message && (!res.data.errors || !Object.keys(res.data.errors).length)) {
                                        toastr["error"](res.data.message);
                                        return;
                                    }

                                    var errormsg = res.data.errors;

                                    try {
                                        if (errormsg.challanNo)
                                            toastr["error"](errormsg.challanNo);
                                    } catch (err) { }

                                    try {
                                        if (errormsg.challanDate)
                                            toastr["error"](errormsg.challanNo);
                                    } catch (err) { }

                                    try {
                                        if (errormsg.customerId)
                                            toastr["error"](errormsg.customerId);
                                    } catch (err) { }

                                    try {
                                        if (errormsg.sellCategoryId)
                                            toastr["error"](errormsg.sellCategoryId);
                                    } catch (err) { }

                                    try {
                                        if (errormsg.sellQualityId)
                                            toastr["error"](errormsg.sellQualityId);
                                    } catch (err) { }

                                    try {
                                        if (errormsg.qtyUnit)
                                            toastr["error"](errormsg.qtyUnit);
                                    } catch (err) { }

                                    try {
                                        if (errormsg.totalQty)
                                            toastr["error"](errormsg.totalQty);
                                    } catch (err) { }

                                    try {
                                        if (errormsg.brokerId)
                                            toastr["error"](errormsg.brokerId);
                                    } catch (err) { }

                                } else if (res.data.status == 0) {
                                    toastr["warning"](res.data.message);
                                } else if (res.data.status == 1) {
                                    swal.fire({
                                        title: this.$t('common.success'),
                                        html: "<h5 style='color:#9C9794'>" + this.$t('challan.created') + "</h5>",
                                        icon: 'success'
                                    }).then((result) => {
                                        this.resetFields();
                                    });
                                }
                            }).catch((err) => {
                                console.log(err);
                                toastr["error"](this.$t('common.somethingWrong'));
                            })
                    }

                }).catch(err => {
                    console.log(err);
                    toastr["error"](this.$t('common.somethingWrong'))
                });


            },

            //this function will reset all fields of the form
            resetFields() {
                this.challanDate = getNowDateTime();
                this.companyContactNo = '',
                this.companyGSTNo = '',
                this.selectedCompanyName = '',
                this.selectedBrokerName = '',
                this.unit = '',
                this.selectedProductQuality = '',
                this.selectedProductCategory = '',
                this.allData = [],
                this.totalQty = (0).toFixed(2)
                this.weightGrams = ''
                this.qualityStock = null
                this.loadNextChallanNo();
            },

            //this function is used when we click an input field of the quantity in the table and select all data of that field
            selectQuantity(index) {
                this.$refs['qty' + (index)][0].select();
            }
        }

    }
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
</style>