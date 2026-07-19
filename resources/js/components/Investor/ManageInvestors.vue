<template>
    <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12 mt-3">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">{{ $t('investor.profitSettingsTitle') }}</h3>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">{{ $t('investor.profitSettingsHelper') }}</p>
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>{{ $t('investor.shopProfitPct') }}</label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="settings.shop_profit_percentage"
                                                min="0"
                                                max="100"
                                                step="0.01"
                                            >
                                            <small class="text-muted">{{ $t('investor.shopProfitHelper') }}</small>
                                        </div>
                                        <div class="form-group col-md-4 d-flex align-items-end">
                                            <button type="button" class="btn btn-primary" @click="saveSettings">
                                                {{ $t('investor.saveProfitSettings') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5 mt-3">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">{{ $t('investor.addTitle') }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>{{ $t('common.name') }} *</label>
                                        <input type="text" class="form-control" v-model="investorForm.investor_name">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ $t('common.email') }}</label>
                                        <input type="email" class="form-control" v-model="investorForm.email">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ $t('common.contact') }}</label>
                                        <input type="text" class="form-control" v-model="investorForm.contact_no" maxlength="11">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ $t('investor.profitSetting') }} *</label>
                                        <select class="form-control" v-model="investorForm.profit_split_mode">
                                            <option value="investment">{{ $t('lab.splitByInvestment') }}</option>
                                            <option value="custom">{{ $t('lab.splitByCustom') }}</option>
                                        </select>
                                        <small class="text-muted">{{ $t('investor.perInvestorSplitHelper') }}</small>
                                    </div>
                                    <div class="form-group" v-if="investorForm.profit_split_mode === 'custom'">
                                        <label>{{ $t('investor.profitPct') }} *</label>
                                        <input type="number" class="form-control" v-model="investorForm.profit_share_percentage" min="0" max="100" step="0.01">
                                        <small class="text-muted">{{ $t('investor.helperLabSplit') }}</small>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="createLogin" v-model="investorForm.create_login">
                                        <label class="form-check-label" for="createLogin">{{ $t('investor.createLogin') }}</label>
                                    </div>
                                    <div class="form-group" v-if="investorForm.create_login">
                                        <label>{{ $t('common.password') }}</label>
                                        <input type="password" class="form-control" v-model="investorForm.password" :placeholder="$t('common.defaultPassword')">
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-primary" @click="saveInvestor">{{ $t('investor.addTitle') }}</button>
                                    <button class="btn btn-secondary" @click="resetInvestorForm">{{ $t('common.reset') }}</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7 mt-3">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">{{ $t('investor.recordTxn') }}</h3>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3" v-html="$t('investor.helperPayout')"></p>
                                    <div class="form-group">
                                        <label>{{ $t('lab.investor') }} *</label>
                                        <select class="form-control" v-model="txnForm.investor_id">
                                            <option value="">{{ $t('common.selectInvestor') }}</option>
                                            <option v-for="inv in investors" :key="inv.investor_id" :value="inv.investor_id">
                                                {{ formatInvestorOption(inv) }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>{{ $t('common.dateTime') }} *</label>
                                            <input type="datetime-local" class="form-control" v-model="txnForm.transaction_date">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>{{ $t('common.type') }} *</label>
                                            <select class="form-control" v-model="txnForm.transaction_type" @change="onTxnTypeChange">
                                                <option value="deposit">{{ $t('investor.cashDeposit') }}</option>
                                                <option value="withdrawal">{{ $t('investor.payout') }}</option>
                                                <option value="gold_buy">{{ $t('investor.goldBuy') }}</option>
                                                <option value="gold_sell">{{ $t('investor.goldSell') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row" v-if="isGoldTxn">
                                        <div class="form-group col-md-4">
                                            <label>{{ $t('common.metal') }} *</label>
                                            <select class="form-control" v-model="txnForm.metal_type">
                                                <option value="gold">{{ $t('common.gold') }}</option>
                                                <option value="silver">{{ $t('common.silver') }}</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>{{ $t('common.weightG') }} *</label>
                                            <input type="number" class="form-control" v-model="txnForm.weight_grams" min="0" step="0.001">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>{{ $t('common.rateG') }} *</label>
                                            <input type="number" class="form-control" v-model="txnForm.rate_per_gram" min="0" step="0.01">
                                        </div>
                                    </div>
                                    <div class="form-group" v-if="!isGoldTxn">
                                        <label>{{ $t('investor.amountRs') }} *</label>
                                        <input type="number" class="form-control" v-model="txnForm.amount" min="0" step="0.01">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ $t('common.notes') }}</label>
                                        <textarea class="form-control" v-model="txnForm.notes" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-primary" @click="saveTransaction">{{ $t('investor.recordTxn') }}</button>
                                    <button class="btn btn-secondary" @click="resetTxnForm">{{ $t('common.reset') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mt-3">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">{{ $t('investor.listTitle') }}</h3>
                                </div>
                                <div class="card-body table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>{{ $t('common.name') }}</th>
                                                <th>{{ $t('common.email') }}</th>
                                                <th>{{ $t('common.contact') }}</th>
                                                <th class="text-right">{{ $t('investor.investment') }}</th>
                                                <th class="text-right">{{ $t('investor.profitSetting') }}</th>
                                                <th class="text-center" style="width: 6.5rem;">{{ $t('common.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="inv in investors" :key="inv.investor_id">
                                                <td>
                                                    <input v-if="editingId === inv.investor_id" type="text" class="form-control form-control-sm" v-model="editForm.investor_name">
                                                    <span v-else>{{ inv.investor_name }}</span>
                                                </td>
                                                <td>
                                                    <input v-if="editingId === inv.investor_id" type="email" class="form-control form-control-sm" v-model="editForm.email">
                                                    <span v-else>{{ inv.email || '-' }}</span>
                                                </td>
                                                <td>
                                                    <input v-if="editingId === inv.investor_id" type="text" class="form-control form-control-sm" v-model="editForm.contact_no" maxlength="11">
                                                    <span v-else>{{ inv.contact_no || '-' }}</span>
                                                </td>
                                                <td class="text-right">
                                                    {{ formatAmount(inv.total_invested) }}
                                                </td>
                                                <td class="text-right">
                                                    <template v-if="editingId === inv.investor_id">
                                                        <select class="form-control form-control-sm mb-1" v-model="editForm.profit_split_mode">
                                                            <option value="investment">{{ $t('lab.splitByInvestment') }}</option>
                                                            <option value="custom">{{ $t('lab.splitByCustom') }}</option>
                                                        </select>
                                                        <input
                                                            v-if="editForm.profit_split_mode === 'custom'"
                                                            type="number"
                                                            class="form-control form-control-sm text-right"
                                                            v-model="editForm.profit_share_percentage"
                                                            min="0"
                                                            max="100"
                                                            step="0.01"
                                                        >
                                                    </template>
                                                    <span v-else>{{ formatProfitSetting(inv) }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="table-actions">
                                                        <template v-if="editingId === inv.investor_id">
                                                            <button type="button" class="btn btn-success btn-sm" @click="updateInvestor(inv.investor_id)" :title="$t('common.save')">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-secondary btn-sm" @click="cancelEdit" :title="$t('common.cancel')">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </template>
                                                        <template v-else>
                                                            <button type="button" class="btn btn-primary btn-sm" @click="startEdit(inv)" :title="$t('common.edit')">
                                                                <i class="fas fa-pen"></i>
                                                            </button>
                                                            <a :href="pdfUrl(inv.investor_id)" target="_blank" class="btn btn-danger btn-sm" :title="$t('investor.monthlyPdf')">
                                                                <i class="fas fa-file-pdf"></i>
                                                            </a>
                                                        </template>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="!investors.length">
                                                <td colspan="6" class="text-center text-muted">{{ $t('investor.noFound') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
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
import { pdfUrl } from '../../auth';
import { formatAmount as formatAmountValue } from '../../currency';

toastr.options = {
    closeButton: true,
    closeDuration: 200,
    progressBar: true,
    newestOnTop: true,
    positionClass: 'toast-top-center',
};

export default {
    name: 'ManageInvestors',
    data() {
        return {
            investors: [],
            editingId: null,
            editForm: {},
            settings: {
                shop_profit_percentage: 0,
            },
            investorForm: {
                investor_name: '',
                email: '',
                contact_no: '',
                profit_split_mode: 'investment',
                profit_share_percentage: 25,
                create_login: false,
                password: '',
            },
            txnForm: {
                investor_id: '',
                transaction_date: '',
                transaction_type: 'deposit',
                metal_type: 'gold',
                weight_grams: '',
                rate_per_gram: '',
                amount: '',
                notes: '',
            },
        };
    },
    computed: {
        isGoldTxn() {
            return ['gold_buy', 'gold_sell'].includes(this.txnForm.transaction_type);
        },
    },
    mounted() {
        this.txnForm.transaction_date = this.getNowDateTime();
        this.loadSettings();
        this.loadInvestors();
    },
    methods: {
        formatAmount(value) {
            return formatAmountValue(value);
        },
        isCustomInvestor(inv) {
            return (inv.profit_split_mode || 'investment') === 'custom';
        },
        formatProfitSetting(inv) {
            if (this.isCustomInvestor(inv)) {
                return `${inv.profit_share_percentage}% · ${this.$t('lab.splitByCustom')}`;
            }
            return this.$t('lab.splitByInvestment');
        },
        formatInvestorOption(inv) {
            const investment = `${this.$t('investor.investment')} ${this.formatAmount(inv.total_invested)}`;
            if (this.isCustomInvestor(inv)) {
                return `${inv.investor_name} (${inv.profit_share_percentage}% · ${investment})`;
            }
            return `${inv.investor_name} (${this.$t('lab.splitByInvestment')} · ${investment})`;
        },
        getNowDateTime() {
            const d = new Date();
            const pad = (value) => String(value).padStart(2, '0');

            return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
        },
        loadSettings() {
            axios.get('/api/settings')
                .then((res) => {
                    this.settings = {
                        shop_profit_percentage: res.data.shop_profit_percentage,
                    };
                })
                .catch((err) => {
                    console.log(err);
                    toastr['error'](this.$t('investor.settingsLoadFail'));
                });
        },
        saveSettings() {
            if (this.settings.shop_profit_percentage === '' || this.settings.shop_profit_percentage === null) {
                toastr['error'](this.$t('investor.shopProfitRequired'));
                return;
            }
            axios.put('/api/settings', this.settings)
                .then((res) => {
                    if (res.data.status === 1) {
                        this.settings = {
                            shop_profit_percentage: res.data.data.shop_profit_percentage,
                        };
                        swal.fire({
                            title: this.$t('common.success'),
                            html: "<h5 style='color:#9C9794'>" + this.$t('investor.settingsSaved') + "</h5>",
                            icon: 'success',
                        });
                    } else {
                        toastr['error'](res.data.message || this.$t('investor.settingsSaveFail'));
                    }
                })
                .catch((err) => {
                    toastr['error'](err.response?.data?.message || this.$t('investor.settingsSaveFail'));
                });
        },
        loadInvestors() {
            axios.get('/api/investor/list')
                .then((res) => {
                    this.investors = res.data.data || [];
                })
                .catch((err) => {
                    console.log(err);
                    toastr['error'](this.$t('investor.loadFail'));
                });
        },
        saveInvestor() {
            if (!this.investorForm.investor_name) {
                toastr['error'](this.$t('investor.nameRequired'));
                return;
            }
            if (this.investorForm.profit_split_mode === 'custom' && this.investorForm.profit_share_percentage === '') {
                toastr['error'](this.$t('investor.nameProfitRequired'));
                return;
            }
            const payload = { ...this.investorForm };
            if (payload.profit_split_mode !== 'custom') {
                payload.profit_share_percentage = 0;
            }
            axios.post('/api/investor/create', payload)
                .then((res) => {
                    if (res.data.status === 1) {
                        swal.fire({
                            title: this.$t('common.success'),
                            html: "<h5 style='color:#9C9794'>" + this.$t('investor.added') + "</h5>",
                            icon: 'success',
                        }).then(() => {
                            this.resetInvestorForm();
                            this.loadInvestors();
                        });
                    } else {
                        toastr['error'](res.data.message || this.$t('investor.createFail'));
                    }
                })
                .catch((err) => {
                    toastr['error'](err.response?.data?.message || this.$t('investor.createFail'));
                });
        },
        resetInvestorForm() {
            this.investorForm = {
                investor_name: '',
                email: '',
                contact_no: '',
                profit_split_mode: 'investment',
                profit_share_percentage: 25,
                create_login: false,
                password: '',
            };
        },
        saveTransaction() {
            if (!this.txnForm.investor_id || !this.txnForm.transaction_date || !this.txnForm.transaction_type) {
                toastr['error'](this.$t('investor.txnRequired'));
                return;
            }
            axios.post('/api/investor/transaction', this.txnForm)
                .then((res) => {
                    if (res.data.status === 1) {
                        swal.fire({
                            title: this.$t('common.success'),
                            html: "<h5 style='color:#9C9794'>" + this.$t('investor.txnRecorded') + "</h5>",
                            icon: 'success',
                        }).then(() => {
                            this.resetTxnForm();
                            this.loadInvestors();
                        });
                    } else {
                        toastr['error'](res.data.message || this.$t('investor.txnFail'));
                    }
                })
                .catch((err) => {
                    toastr['error'](err.response?.data?.message || this.$t('investor.txnFail'));
                });
        },
        resetTxnForm() {
            this.txnForm = {
                investor_id: this.txnForm.investor_id,
                transaction_date: this.getNowDateTime(),
                transaction_type: 'deposit',
                metal_type: 'gold',
                weight_grams: '',
                rate_per_gram: '',
                amount: '',
                notes: '',
            };
        },
        onTxnTypeChange() {
            this.txnForm.amount = '';
            this.txnForm.weight_grams = '';
            this.txnForm.rate_per_gram = '';
        },
        startEdit(inv) {
            this.editingId = inv.investor_id;
            this.editForm = {
                investor_name: inv.investor_name,
                email: inv.email || '',
                contact_no: inv.contact_no || '',
                profit_split_mode: inv.profit_split_mode || 'investment',
                profit_share_percentage: inv.profit_share_percentage,
            };
        },
        cancelEdit() {
            this.editingId = null;
            this.editForm = {};
        },
        updateInvestor(investorId) {
            const payload = { ...this.editForm };
            if (payload.profit_split_mode !== 'custom') {
                payload.profit_share_percentage = 0;
            } else if (payload.profit_share_percentage === '' || payload.profit_share_percentage === null) {
                toastr['error'](this.$t('investor.nameProfitRequired'));
                return;
            }
            axios.put(`/api/investor/update/${investorId}`, payload)
                .then((res) => {
                    if (res.data.status === 1) {
                        swal.fire({
                            title: this.$t('common.success'),
                            html: "<h5 style='color:#9C9794'>" + this.$t('investor.updated') + "</h5>",
                            icon: 'success',
                        }).then(() => {
                            this.cancelEdit();
                            this.loadInvestors();
                        });
                    } else {
                        toastr['error'](res.data.message || this.$t('investor.updateFail'));
                    }
                })
                .catch((err) => {
                    toastr['error'](err.response?.data?.message || this.$t('investor.updateFail'));
                });
        },
        pdfUrl(investorId) {
            const d = new Date();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const today = `${d.getFullYear()}-${month}-${day}`;
            return pdfUrl(`/investor/pdf/${investorId}/monthly?date=${today}`);
        },
    },
};
</script>
