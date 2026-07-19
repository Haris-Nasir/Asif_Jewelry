<template>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary mt-3">
                        <div class="card-header card-title d-flex">
                            <span class="p-2 flex-grow-1 bd-highlight">{{ $t('invoice.directTitle') }}</span>
                            <span class="p-2 bd-highlight">
                                <button type="button" class="btn btn-tool text-md" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2"><label class="text-md mt-1">{{ $t('common.invoiceDate') }}</label></div>
                                <div class="col-md-2">
                                    <input type="datetime-local" class="form-control" v-model="invoice.invoiceDate" @change="loadNextInvoiceNo">
                                </div>
                                <div class="col-md-2"></div>
                                <div class="col-md-2"><label class="text-md mt-1">{{ $t('common.invoiceNo') }}</label></div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control text-right" v-model="invoice.invoiceNo" disabled>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-2"><label class="text-md mt-1">{{ $t('common.customer') }}</label></div>
                                <div class="col-md-4">
                                    <model-select :options="options.customers" v-model="invoice.customerId" :placeholder="$t('common.selectCustomer')" />
                                </div>
                                <div class="col-md-2"><label class="text-md mt-1">{{ $t('common.broker') }}</label></div>
                                <div class="col-md-4">
                                    <model-select :options="options.brokers" v-model="invoice.brokerId" :placeholder="$t('common.selectBroker')" />
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                                <h6 class="mb-0">{{ $t('invoice.products') }}</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" @click="addItem">
                                    <i class="fas fa-plus"></i> {{ $t('invoice.addLine') }}
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm invoice-items-table">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 8rem;">{{ $t('common.category') }}</th>
                                            <th style="min-width: 9rem;">{{ $t('common.quality') }}</th>
                                            <th class="text-right" style="width: 5.5rem;">{{ $t('common.weightPerPiece') }}</th>
                                            <th class="text-right" style="width: 4.5rem;">{{ $t('invoice.qtyPieces') }}</th>
                                            <th style="width: 4.5rem;">{{ $t('common.unit') }}</th>
                                            <th class="text-right" style="width: 5.5rem;">{{ $t('common.totalWeightG') }}</th>
                                            <th class="text-right" style="width: 5.5rem;">{{ $t('common.ratePerG') }}</th>
                                            <th class="text-right" style="width: 6rem;">{{ $t('invoice.lineAmount') }}</th>
                                            <th style="width: 3rem;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in items" :key="item._key">
                                            <td>
                                                <model-select
                                                    :options="options.categories"
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
                                                <input type="number" step="0.001" class="form-control form-control-sm text-right" v-model="item.weightGrams" @input="recalculateTotals">
                                            </td>
                                            <td>
                                                <input type="number" step="1" min="1" class="form-control form-control-sm text-right" v-model="item.qty" @input="recalculateTotals">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" v-model="item.unit">
                                            </td>
                                            <td class="text-right align-middle">{{ lineWeight(item) }}</td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control form-control-sm text-right" v-model="item.rate" @input="recalculateTotals">
                                            </td>
                                            <td class="text-right align-middle">{{ lineBaseAmount(item) }}</td>
                                            <td class="text-center align-middle">
                                                <button type="button" class="btn btn-sm btn-outline-danger" :disabled="items.length === 1" @click="removeItem(index)" :title="$t('invoice.removeLine')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="row mt-3" v-if="pendingKarigarJobs.length">
                                <div class="col-md-2"><label class="text-md mt-1">{{ $t('common.karigarJob') }}</label></div>
                                <div class="col-md-4">
                                    <model-select :options="karigarJobOptions" v-model="invoice.karigarJobId" :placeholder="$t('common.linkKarigarOptional')" />
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted small mt-2 mb-0">{{ $t('invoice.helperKarigar') }}</p>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-2"><label class="text-md mt-1">{{ $t('common.gstPercent') }}</label></div>
                                <div class="col-md-2">
                                    <select class="form-control text-right" v-model="invoice.gstPercentage" @change="recalculateTotals">
                                        <option value="0">0</option>
                                        <option value="5">5</option>
                                        <option value="12">12</option>
                                        <option value="18">18</option>
                                        <option value="28">28</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-3" v-if="hasGoldItem">
                                <div class="col-md-2"><label class="text-md mt-1">{{ $t('common.polishPerG') }}</label></div>
                                <div class="col-md-2">
                                    <input type="number" step="0.01" min="0" class="form-control text-right" v-model="invoice.polishRatePerGram" @input="recalculateTotals">
                                </div>
                                <div class="col-md-2"><label class="text-md mt-1">{{ $t('common.polishTotal') }}</label></div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control text-right" :value="polishCostTotal" disabled>
                                </div>
                            </div>

                            <div class="row mt-3" v-if="showMazduriField">
                                <div class="col-md-2"><label class="text-md mt-1">{{ $t('common.mazduri') }}</label></div>
                                <div class="col-md-2">
                                    <input type="number" step="0.01" min="0" class="form-control text-right" v-model="invoice.mazduriCost" :disabled="!!invoice.karigarJobId">
                                </div>
                                <div class="col-md-8">
                                    <p class="text-muted small mt-2 mb-0">{{ mazduriHelpText }}</p>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-2"><label class="text-md mt-1">{{ $t('common.totalAmount') }}</label></div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control text-right" :value="invoice.totalAmount" disabled>
                                </div>
                                <div class="col-md-2"><label class="text-md mt-1">{{ $t('common.gstAmount') }}</label></div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control text-right" :value="invoice.gstAmount" disabled>
                                </div>
                                <div class="col-md-2"><label class="text-md mt-1">{{ $t('common.netAmount') }}</label></div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control text-right" :value="invoice.netAmount" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-primary" @click="generateInvoice">{{ $t('invoice.generate') }}</button>
                            <button type="button" class="btn btn-secondary" @click="resetInvoiceForm">{{ $t('common.reset') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import toastr from "toastr";
import swal from "sweetalert2";
import { ModelSelect } from "vue-search-select";
import { getNowDateTime, toDateOnly } from "../../currency";

toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-center",
};

let itemKey = 1;

function emptyItem() {
    return {
        _key: itemKey++,
        categoryId: "",
        qualityId: "",
        qty: "1",
        weightGrams: "",
        rate: "",
        unit: "pcs",
        qualityOptions: [],
        stock: null,
    };
}

export default {
    name: "GenerateDirectInvoice",
    components: { ModelSelect },
    data() {
        return {
            invoice: {
                invoiceDate: "",
                invoiceNo: "",
                customerId: "",
                brokerId: "",
                gstPercentage: "0",
                totalAmount: (0).toFixed(2),
                gstAmount: (0).toFixed(2),
                netAmount: (0).toFixed(2),
                polishRatePerGram: "",
                mazduriCost: "",
                karigarJobId: "",
            },
            items: [emptyItem()],
            options: {
                customers: [],
                brokers: [],
                categories: [],
            },
            pendingKarigarJobs: [],
        };
    },
    mounted() {
        this.invoice.invoiceDate = getNowDateTime();
        this.loadCustomers();
        this.loadBrokers();
        this.loadCategories();
        this.loadNextInvoiceNo();
    },
    computed: {
        hasGoldItem() {
            return this.items.some((item) => this.itemMetalType(item) === "gold");
        },
        totalWeightGrams() {
            return this.items.reduce((sum, item) => sum + this.lineWeightNumber(item), 0);
        },
        polishCostTotal() {
            if (!this.hasGoldItem) {
                return "0.00";
            }
            const total = parseFloat(this.invoice.polishRatePerGram || 0) * this.totalWeightGrams;
            return Number.isNaN(total) ? "0.00" : total.toFixed(2);
        },
        appliedMazduriAmount() {
            if (this.showMazduriField || this.invoice.karigarJobId) {
                return parseFloat(this.invoice.mazduriCost || 0) || 0;
            }
            return 0;
        },
        showMazduriField() {
            return this.items.some((item) => this.itemMetalType(item) === "silver");
        },
        mazduriHelpText() {
            if (this.invoice.karigarJobId) {
                return this.$t("invoice.helperMazduriKarigar");
            }
            return this.$t("invoice.helperMazduriSilver");
        },
        karigarJobOptions() {
            return this.pendingKarigarJobs.map((job) => ({
                value: job.karigar_job_id,
                text: `${job.karigar_name} — ${job.returned_weight_grams}g (Rs. ${job.mazduri_cost} mazduri)`,
            }));
        },
    },
    watch: {
        "invoice.karigarJobId"(val) {
            if (!val) {
                if (!this.showMazduriField) {
                    this.invoice.mazduriCost = "";
                }
                return;
            }
            const job = this.pendingKarigarJobs.find((j) => String(j.karigar_job_id) === String(val));
            if (job) {
                this.invoice.mazduriCost = job.mazduri_cost;
            }
        },
        "invoice.gstPercentage"() {
            this.recalculateTotals();
        },
    },
    methods: {
        itemMetalType(item) {
            const category = this.options.categories.find((c) => String(c.value) === String(item.categoryId));
            return category?.metalType || "gold";
        },
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
        lineBaseAmount(item) {
            return (this.lineWeightNumber(item) * parseFloat(item.rate || 0)).toFixed(2);
        },
        addItem() {
            this.items.push(emptyItem());
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
                this.recalculateTotals();
                this.loadPendingKarigarJobs();
            }
        },
        recalculateTotals() {
            const totalAmount = this.items.reduce(
                (sum, item) => sum + this.lineWeightNumber(item) * parseFloat(item.rate || 0),
                0
            );
            const gstAmount = totalAmount * parseFloat(this.invoice.gstPercentage || 0) * 0.01;
            this.invoice.totalAmount = totalAmount.toFixed(2);
            this.invoice.gstAmount = gstAmount.toFixed(2);
            this.invoice.netAmount = (totalAmount + gstAmount).toFixed(2);
        },
        loadCustomers() {
            axios.get("../api/customerlist")
                .then((response) => {
                    this.options.customers = response.data.map((company) => ({
                        value: company.customer_id,
                        text: `${company.customer_company_name} - ${company.customer_contact_no}`,
                    }));
                })
                .catch(() => toastr.error(this.$t("common.somethingWrong")));
        },
        loadBrokers() {
            axios.get("../api/brokerslist")
                .then((response) => {
                    this.options.brokers = response.data.map((broker) => ({
                        value: broker.broker_id,
                        text: `${broker.broker_name} - ${broker.broker_contact_no}`,
                    }));
                })
                .catch(() => toastr.error(this.$t("common.somethingWrong")));
        },
        loadCategories() {
            axios.get("../api/sellqualitycategories")
                .then((response) => {
                    this.options.categories = response.data.qualityCategories.map((category) => ({
                        ...this.$categoryOption(category),
                        metalType: category.metalType || "gold",
                    }));
                })
                .catch(() => toastr.error(this.$t("common.somethingWrong")));
        },
        onItemCategoryChange(index) {
            const item = this.items[index];
            item.qualityId = "";
            item.stock = null;
            item.unit = "pcs";
            item.qualityOptions = [];
            if (!item.categoryId) {
                return;
            }
            axios.get("../api/sellqualityofcategory/" + item.categoryId)
                .then((response) => {
                    item.qualityOptions = response.data.map((quality) => ({
                        value: quality.sell_quality_id,
                        text: quality.quality_name,
                    }));
                })
                .catch(() => toastr.error(this.$t("common.somethingWrong")));
        },
        onItemQualityChange(index) {
            const item = this.items[index];
            item.stock = null;
            if (!item.qualityId) {
                this.loadPendingKarigarJobs();
                return;
            }
            axios.get("/api/stock/quality/" + item.qualityId)
                .then((res) => {
                    item.stock = res.data;
                })
                .catch(() => {
                    item.stock = null;
                });
            this.loadPendingKarigarJobs();
        },
        loadPendingKarigarJobs() {
            this.invoice.karigarJobId = "";
            this.pendingKarigarJobs = [];
            const qualityId = this.items.map((i) => i.qualityId).find((id) => id);
            if (!qualityId) {
                return;
            }
            axios.get("/api/karigar/jobs/pending-for-sale?sell_quality_id=" + qualityId)
                .then((res) => {
                    this.pendingKarigarJobs = res.data || [];
                })
                .catch(() => {
                    this.pendingKarigarJobs = [];
                });
        },
        loadNextInvoiceNo() {
            if (!this.invoice.invoiceDate) {
                return;
            }
            axios.get("../api/challan/next-no/" + toDateOnly(this.invoice.invoiceDate))
                .then((response) => {
                    this.invoice.invoiceNo = response.data.nextChallanNo;
                })
                .catch(() => toastr.error(this.$t("invoice.loadNextFail")));
        },
        validateForm() {
            if (!this.invoice.invoiceDate) {
                toastr.info(this.$t("invoice.dateRequired"));
                return false;
            }
            if (!this.invoice.invoiceNo || this.invoice.invoiceNo <= 0) {
                toastr.info(this.$t("invoice.notReady"));
                return false;
            }
            if (!this.invoice.customerId) {
                toastr.info(this.$t("invoice.customerRequired"));
                return false;
            }
            if (!this.invoice.brokerId) {
                toastr.info(this.$t("invoice.brokerRequired"));
                return false;
            }
            if (this.invoice.gstPercentage === "" || this.invoice.gstPercentage === null) {
                toastr.info(this.$t("invoice.gstRequired"));
                return false;
            }
            for (let i = 0; i < this.items.length; i++) {
                const item = this.items[i];
                const label = this.$t("invoice.lineN", { n: i + 1 });
                if (!item.categoryId) {
                    toastr.info(`${label}: ${this.$t("invoice.categoryRequired")}`);
                    return false;
                }
                if (!item.qualityId) {
                    toastr.info(`${label}: ${this.$t("invoice.qualityRequired")}`);
                    return false;
                }
                if (!item.qty || parseFloat(item.qty) <= 0) {
                    toastr.info(`${label}: ${this.$t("invoice.qtyRequired")}`);
                    return false;
                }
                if (!item.unit) {
                    toastr.info(`${label}: ${this.$t("invoice.unitRequired")}`);
                    return false;
                }
                if (item.rate === "" || parseFloat(item.rate) < 0) {
                    toastr.info(`${label}: ${this.$t("invoice.rateRequired")}`);
                    return false;
                }
                if (!item.weightGrams || parseFloat(item.weightGrams) <= 0) {
                    toastr.info(`${label}: ${this.$t("invoice.weightRequired")}`);
                    return false;
                }
                if (
                    item.stock &&
                    item.stock.available_piece_weights &&
                    item.stock.available_piece_weights.length
                ) {
                    const perPiece = parseFloat(item.weightGrams);
                    const qty = parseInt(item.qty, 10);
                    const matchingCount = item.stock.available_piece_weights.filter(
                        (weight) => Math.abs(parseFloat(weight) - perPiece) <= 0.0005
                    ).length;
                    if (matchingCount < qty) {
                        toastr.error(
                            this.$t("challan.cannotSell", {
                                n: qty,
                                weight: perPiece.toFixed(3),
                                quality: item.stock.quality_name,
                                list: item.stock.available_piece_weights_label,
                            })
                        );
                        return false;
                    }
                }
            }
            return true;
        },
        generateInvoice() {
            if (!this.validateForm()) {
                return;
            }
            const payload = {
                invoiceDate: this.invoice.invoiceDate,
                invoiceNo: this.invoice.invoiceNo,
                customerId: this.invoice.customerId,
                brokerId: this.invoice.brokerId,
                gstPercentage: this.invoice.gstPercentage,
                polishRatePerGram: this.invoice.polishRatePerGram,
                mazduriCost: this.invoice.mazduriCost,
                karigarJobId: this.invoice.karigarJobId || null,
                items: this.items.map((item) => ({
                    categoryId: item.categoryId,
                    qualityId: item.qualityId,
                    qty: item.qty,
                    unit: item.unit,
                    weightGrams: item.weightGrams,
                    rate: item.rate,
                })),
            };
            axios.post("/api/directinvoice", payload)
                .then((response) => {
                    if (response.data.status == 1) {
                        swal.fire({
                            title: this.$t("common.success"),
                            html: "<h5 style='color:#9C9794'>" + this.$t("invoice.created", { amount: response.data.profit || 0 }) + "</h5>",
                            icon: "success",
                            allowOutsideClick: false,
                        }).then(() => this.resetInvoiceForm());
                    } else if (response.data.status == -1) {
                        toastr.error(response.data.message || this.$t("invoice.createFail"));
                    } else if (response.data.status == 0) {
                        toastr.error(response.data.message);
                    } else {
                        toastr.error(response.data.message || this.$t("invoice.createFail"));
                    }
                })
                .catch((err) => {
                    toastr.error(err.response?.data?.message || this.$t("invoice.createFailDetailed"));
                });
        },
        resetInvoiceForm() {
            this.invoice = {
                invoiceDate: getNowDateTime(),
                invoiceNo: "",
                customerId: "",
                brokerId: "",
                gstPercentage: "0",
                totalAmount: (0).toFixed(2),
                gstAmount: (0).toFixed(2),
                netAmount: (0).toFixed(2),
                polishRatePerGram: "",
                mazduriCost: "",
                karigarJobId: "",
            };
            this.items = [emptyItem()];
            this.pendingKarigarJobs = [];
            this.loadNextInvoiceNo();
        },
    },
};
</script>

<style scoped>
.invoice-items-table .form-control-sm {
    min-width: 0;
}
</style>
