<template>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 mt-3">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">{{ $t('challan.newTitle') }}</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="form-group row">
                                <div class="col-md-2">
                                    <label class="text-md col-form-label">{{ $t('common.dateTime') }} <span class="required-mark" style="color: red;">*</span></label>
                                </div>
                                <div class="col-md-3">
                                    <input type="datetime-local" class="form-control text-md" v-model="challanDate" @change="loadNextChallanNo">
                                </div>
                                <div class="col-md-2 ml-auto">
                                    <label class="text-md col-form-label">{{ $t('challan.no') }}</label>
                                </div>
                                <div class="col-md-3 mr-5">
                                    <input type="text" class="text-md form-control" v-model="challanNo" disabled>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-2">
                                    <label class="text-md col-form-label">{{ $t('common.customer') }} <span class="required-mark" style="color: red;">*</span></label>
                                </div>
                                <div class="col-md-3">
                                    <model-select :options="companyNames" v-model="selectedCompanyName" @blur="getFromSelectedCompany" :placeholder="$t('common.selectCustomer')" />
                                </div>
                                <div class="col-md-2 ml-auto">
                                    <label class="text-md col-form-label">{{ $t('broker.name') }} <span class="required-mark" style="color: red;">*</span></label>
                                </div>
                                <div class="col-md-3 mr-5">
                                    <model-select :options="brokerNames" v-model="selectedBrokerName" :placeholder="$t('common.selectBrokerName')" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-2">
                                    <label class="text-md col-form-label">{{ $t('common.companyContact') }}</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="text-md form-control" v-model="companyContactNo" disabled>
                                </div>
                                <div class="col-md-2 ml-auto">
                                    <label class="text-md col-form-label">{{ $t('common.companyGst') }}</label>
                                </div>
                                <div class="col-md-3 mr-5">
                                    <input type="text" class="text-md form-control" v-model="companyGSTNo" disabled>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                                <h6 class="mb-0">{{ $t('invoice.products') }}</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" @click="addItem">
                                    <i class="fas fa-plus"></i> {{ $t('invoice.addLine') }}
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm challan-items-table">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 8rem;">{{ $t('common.category') }}</th>
                                            <th style="min-width: 9rem;">{{ $t('common.quality') }}</th>
                                            <th class="text-right" style="width: 5.5rem;">{{ $t('common.weightPerPiece') }}</th>
                                            <th class="text-right" style="width: 4.5rem;">{{ $t('invoice.qtyPieces') }}</th>
                                            <th style="width: 4.5rem;">{{ $t('common.unit') }}</th>
                                            <th class="text-right" style="width: 5.5rem;">{{ $t('common.totalWeightG') }}</th>
                                            <th style="width: 3rem;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in items" :key="item._key">
                                            <td>
                                                <model-select
                                                    :options="productCategories"
                                                    v-model="item.categoryId"
                                                    :placeholder="$t('common.selectCategory')"
                                                    @input="onItemCategoryChange(index)"
                                                />
                                            </td>
                                            <td>
                                                <model-select
                                                    :options="item.qualityOptions"
                                                    v-model="item.qualityId"
                                                    :placeholder="$t('common.selectQuality')"
                                                    @input="onItemQualityChange(index)"
                                                />
                                                <small v-if="item.stock" class="text-muted d-block mt-1">
                                                    {{ item.stock.weight_grams }}g · {{ item.stock.pieces }} pcs
                                                </small>
                                            </td>
                                            <td>
                                                <input type="number" step="0.001" class="form-control form-control-sm text-right" v-model="item.weightGrams">
                                            </td>
                                            <td>
                                                <input type="number" step="1" min="1" class="form-control form-control-sm text-right" v-model="item.qty">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" v-model="item.unit">
                                            </td>
                                            <td class="text-right align-middle">{{ lineWeight(item) }}</td>
                                            <td class="text-center align-middle">
                                                <button type="button" class="btn btn-sm btn-outline-danger" :disabled="items.length === 1" @click="removeItem(index)" :title="$t('invoice.removeLine')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="form-group row mt-3">
                                <div class="col-md-2">
                                    <label class="text-md col-form-label">{{ $t('common.totalWeightG') }}</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control text-right" :value="totalWeightGrams.toFixed(3)" disabled>
                                </div>
                                <div class="col-md-2">
                                    <label class="text-md col-form-label">{{ $t('common.totalPieces') }}</label>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control text-right" :value="totalPieces" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="button" class="btn btn-primary text-md" @click="addChallan">{{ $t('common.add') }}</button>
                            <button type="button" class="btn btn-primary ml-3 text-md" @click="resetFields">{{ $t('common.reset') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import toastr from 'toastr';
import swal from 'sweetalert2';
import { ModelSelect } from 'vue-search-select';
import { getNowDateTime, toDateOnly } from '../../currency';

toastr.options = {
    closeButton: true,
    closeDuration: 200,
    progressBar: true,
    newestOnTop: true,
    positionClass: 'toast-top-center',
};

let itemKey = 1;

function emptyItem() {
    return {
        _key: itemKey++,
        categoryId: '',
        qualityId: '',
        qty: '1',
        weightGrams: '',
        unit: 'pcs',
        qualityOptions: [],
        stock: null,
    };
}

export default {
    name: 'NewChallan',
    components: { ModelSelect },
    data() {
        return {
            challanDate: '',
            challanNo: '',
            companyNames: [],
            selectedCompanyName: '',
            brokerNames: [],
            selectedBrokerName: '',
            companyContactNo: '',
            companyGSTNo: '',
            productCategories: [],
            items: [emptyItem()],
        };
    },
    mounted() {
        this.challanDate = getNowDateTime();
        this.loadCompanyName();
        this.loadBrokerName();
        this.loadQualityCategories();
        this.loadNextChallanNo();
    },
    computed: {
        totalWeightGrams() {
            return this.items.reduce((sum, item) => sum + this.lineWeightNumber(item), 0);
        },
        totalPieces() {
            return this.items.reduce((sum, item) => sum + (parseFloat(item.qty) || 0), 0);
        },
    },
    methods: {
        lineWeightNumber(item) {
            const perPiece = parseFloat(item.weightGrams || 0);
            const qty = parseFloat(item.qty || 0);
            if (!perPiece || !qty) {
                return 0;
            }
            return perPiece * qty;
        },
        lineWeight(item) {
            return this.lineWeightNumber(item).toFixed(3);
        },
        addItem() {
            this.items.push(emptyItem());
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        loadCompanyName() {
            axios.get('../api/customerlist').then((response) => {
                this.companyNames = response.data.map((company) => ({
                    value: company.customer_id,
                    text: company.customer_company_name + ' - ' + company.customer_contact_no,
                }));
            }).catch(() => toastr.error(this.$t('common.somethingWrong')));
        },
        loadBrokerName() {
            axios.get('../api/brokerslist').then((response) => {
                this.brokerNames = response.data.map((broker) => ({
                    value: broker.broker_id,
                    text: broker.broker_name + ' - ' + broker.broker_contact_no,
                }));
            }).catch(() => toastr.error(this.$t('common.somethingWrong')));
        },
        loadQualityCategories() {
            axios.get('../api/sellqualitycategories').then((response) => {
                this.productCategories = response.data.qualityCategories.map((c) => this.$categoryOption(c));
            }).catch(() => toastr.error(this.$t('common.somethingWrong')));
        },
        getFromSelectedCompany() {
            if (!this.selectedCompanyName) {
                this.companyContactNo = '';
                this.companyGSTNo = '';
                return;
            }
            axios.get('../api/selectedcustomerdata/' + this.selectedCompanyName).then((response) => {
                this.companyContactNo = response.data.customer_contact_no;
                this.companyGSTNo = response.data.customer_gst_no;
            }).catch(() => toastr.error(this.$t('common.somethingWrong')));
        },
        onItemCategoryChange(index) {
            const item = this.items[index];
            item.qualityId = '';
            item.stock = null;
            item.unit = 'pcs';
            item.qualityOptions = [];
            if (!item.categoryId) {
                return;
            }
            axios.get('../api/sellqualityofcategory/' + item.categoryId).then((response) => {
                item.qualityOptions = response.data.map((quality) => ({
                    value: quality.sell_quality_id,
                    text: quality.quality_name,
                }));
            }).catch(() => toastr.error(this.$t('common.somethingWrong')));
        },
        onItemQualityChange(index) {
            const item = this.items[index];
            item.stock = null;
            if (!item.qualityId) {
                return;
            }
            axios.get('/api/stock/quality/' + item.qualityId).then((res) => {
                item.stock = res.data;
            }).catch(() => {
                item.stock = null;
            });
        },
        loadNextChallanNo() {
            if (!this.challanDate) {
                return;
            }
            axios.get('../api/challan/next-no/' + toDateOnly(this.challanDate)).then((response) => {
                this.challanNo = response.data.nextChallanNo;
            }).catch(() => toastr.error(this.$t('challan.loadNextNoFail')));
        },
        validateForm() {
            if (!this.challanDate) {
                toastr.info(this.$t('challan.dateRequired') || this.$t('invoice.dateRequired'));
                return false;
            }
            if (!this.selectedCompanyName) {
                toastr.info(this.$t('invoice.customerRequired'));
                return false;
            }
            if (!this.selectedBrokerName) {
                toastr.info(this.$t('invoice.brokerRequired'));
                return false;
            }
            for (let i = 0; i < this.items.length; i++) {
                const item = this.items[i];
                const label = this.$t('invoice.lineN', { n: i + 1 });
                if (!item.categoryId) {
                    toastr.info(`${label}: ${this.$t('invoice.categoryRequired')}`);
                    return false;
                }
                if (!item.qualityId) {
                    toastr.info(`${label}: ${this.$t('invoice.qualityRequired')}`);
                    return false;
                }
                if (!item.qty || parseFloat(item.qty) <= 0) {
                    toastr.info(`${label}: ${this.$t('invoice.qtyRequired')}`);
                    return false;
                }
                if (!item.unit) {
                    toastr.info(`${label}: ${this.$t('invoice.unitRequired')}`);
                    return false;
                }
                if (!item.weightGrams || parseFloat(item.weightGrams) <= 0) {
                    toastr.info(`${label}: ${this.$t('invoice.weightRequired')}`);
                    return false;
                }
                if (item.stock && item.stock.available_piece_weights && item.stock.available_piece_weights.length) {
                    const perPiece = parseFloat(item.weightGrams);
                    const qty = parseInt(item.qty, 10);
                    const matchingCount = item.stock.available_piece_weights.filter(
                        (weight) => Math.abs(parseFloat(weight) - perPiece) <= 0.0005
                    ).length;
                    if (matchingCount < qty) {
                        toastr.error(this.$t('challan.cannotSell', {
                            n: qty,
                            weight: perPiece.toFixed(3),
                            quality: item.stock.quality_name,
                            list: item.stock.available_piece_weights_label,
                        }));
                        return false;
                    }
                }
            }
            return true;
        },
        addChallan() {
            if (!this.validateForm()) {
                return;
            }
            const payload = {
                challanDate: this.challanDate,
                customerId: this.selectedCompanyName,
                brokerId: this.selectedBrokerName,
                items: this.items.map((item) => ({
                    categoryId: item.categoryId,
                    qualityId: item.qualityId,
                    qty: item.qty,
                    unit: item.unit,
                    weightGrams: item.weightGrams,
                })),
            };
            axios.post('../api/challan/insert', payload).then((response) => {
                if (response.data.status == 1) {
                    swal.fire({
                        title: this.$t('common.success'),
                        html: "<h5 style='color:#9C9794'>" + (response.data.message || this.$t('challan.created')) + '</h5>',
                        icon: 'success',
                        allowOutsideClick: false,
                    }).then(() => this.resetFields());
                } else {
                    toastr.error(response.data.message || this.$t('common.somethingWrong'));
                }
            }).catch((err) => {
                toastr.error(err.response?.data?.message || this.$t('common.somethingWrong'));
            });
        },
        resetFields() {
            this.challanDate = getNowDateTime();
            this.selectedCompanyName = '';
            this.selectedBrokerName = '';
            this.companyContactNo = '';
            this.companyGSTNo = '';
            this.items = [emptyItem()];
            this.loadNextChallanNo();
        },
    },
};
</script>

<style scoped>
.challan-items-table .form-control-sm {
    min-width: 0;
}
</style>
