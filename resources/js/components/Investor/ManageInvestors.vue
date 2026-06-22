<template>
    <div>
        <aside></aside>
        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-5 mt-3">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Add Investor</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Name *</label>
                                        <input type="text" class="form-control" v-model="investorForm.investor_name">
                                    </div>
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" class="form-control" v-model="investorForm.email">
                                    </div>
                                    <div class="form-group">
                                        <label>Contact</label>
                                        <input type="text" class="form-control" v-model="investorForm.contact_no">
                                    </div>
                                    <div class="form-group">
                                        <label>Profit Share % *</label>
                                        <input type="number" class="form-control" v-model="investorForm.profit_share_percentage" min="0" max="100" step="0.01">
                                    </div>
                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="createLogin" v-model="investorForm.create_login">
                                        <label class="form-check-label" for="createLogin">Create login account</label>
                                    </div>
                                    <div class="form-group" v-if="investorForm.create_login">
                                        <label>Password</label>
                                        <input type="password" class="form-control" v-model="investorForm.password" placeholder="Default: password">
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-primary" @click="saveInvestor">Add Investor</button>
                                    <button class="btn btn-secondary" @click="resetInvestorForm">Reset</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7 mt-3">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Record Transaction</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Investor *</label>
                                        <select class="form-control" v-model="txnForm.investor_id">
                                            <option value="">Select investor</option>
                                            <option v-for="inv in investors" :key="inv.investor_id" :value="inv.investor_id">
                                                {{ inv.investor_name }} ({{ inv.profit_share_percentage }}%)
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Date *</label>
                                            <input type="date" class="form-control" v-model="txnForm.transaction_date">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Type *</label>
                                            <select class="form-control" v-model="txnForm.transaction_type" @change="onTxnTypeChange">
                                                <option value="deposit">Cash Deposit (₹)</option>
                                                <option value="withdrawal">Cash Withdrawal (₹)</option>
                                                <option value="gold_buy">Gold Buy</option>
                                                <option value="gold_sell">Gold Sell</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row" v-if="isGoldTxn">
                                        <div class="form-group col-md-4">
                                            <label>Metal *</label>
                                            <select class="form-control" v-model="txnForm.metal_type">
                                                <option value="gold">Gold</option>
                                                <option value="silver">Silver</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Weight (g) *</label>
                                            <input type="number" class="form-control" v-model="txnForm.weight_grams" min="0" step="0.001">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Rate/g *</label>
                                            <input type="number" class="form-control" v-model="txnForm.rate_per_gram" min="0" step="0.01">
                                        </div>
                                    </div>
                                    <div class="form-group" v-if="!isGoldTxn">
                                        <label>Amount (₹) *</label>
                                        <input type="number" class="form-control" v-model="txnForm.amount" min="0" step="0.01">
                                    </div>
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <textarea class="form-control" v-model="txnForm.notes" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-primary" @click="saveTransaction">Record Transaction</button>
                                    <button class="btn btn-secondary" @click="resetTxnForm">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mt-3">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Investors</h3>
                                </div>
                                <div class="card-body table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Contact</th>
                                                <th class="text-right">Profit %</th>
                                                <th width="20%">Actions</th>
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
                                                    <input v-if="editingId === inv.investor_id" type="text" class="form-control form-control-sm" v-model="editForm.contact_no">
                                                    <span v-else>{{ inv.contact_no || '-' }}</span>
                                                </td>
                                                <td class="text-right">
                                                    <input v-if="editingId === inv.investor_id" type="number" class="form-control form-control-sm text-right" v-model="editForm.profit_share_percentage" min="0" max="100" step="0.01">
                                                    <span v-else>{{ inv.profit_share_percentage }}%</span>
                                                </td>
                                                <td class="text-center">
                                                    <template v-if="editingId === inv.investor_id">
                                                        <button class="btn btn-success btn-sm" @click="updateInvestor(inv.investor_id)"><i class="fas fa-check"></i></button>
                                                        <button class="btn btn-secondary btn-sm" @click="cancelEdit"><i class="fas fa-times"></i></button>
                                                    </template>
                                                    <template v-else>
                                                        <button class="btn btn-primary btn-sm" @click="startEdit(inv)"><i class="fas fa-pen"></i></button>
                                                        <a :href="pdfUrl(inv.investor_id, 'monthly')" target="_blank" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i></a>
                                                    </template>
                                                </td>
                                            </tr>
                                            <tr v-if="!investors.length">
                                                <td colspan="5" class="text-center text-muted">No investors found.</td>
                                            </tr>
                                        </tbody>
                                    </table>
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
import toastr from 'toastr';
import swal from 'sweetalert2';
import { pdfUrl } from '../../auth';

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
            investorForm: {
                investor_name: '',
                email: '',
                contact_no: '',
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
        this.txnForm.transaction_date = this.getTodaysDate();
        this.loadInvestors();
    },
    methods: {
        getTodaysDate() {
            const d = new Date();
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        },
        loadInvestors() {
            axios.get('/api/investor/list')
                .then((res) => {
                    this.investors = res.data.data || [];
                })
                .catch((err) => {
                    console.log(err);
                    toastr['error']('Unable to load investors.');
                });
        },
        saveInvestor() {
            if (!this.investorForm.investor_name || this.investorForm.profit_share_percentage === '') {
                toastr['error']('Name and profit share are required.');
                return;
            }
            axios.post('/api/investor/create', this.investorForm)
                .then((res) => {
                    if (res.data.status === 1) {
                        swal.fire({
                            title: 'Success',
                            html: "<h5 style='color:#9C9794'>Investor Added Successfully!</h5>",
                            icon: 'success',
                        }).then(() => {
                            this.resetInvestorForm();
                            this.loadInvestors();
                        });
                    } else {
                        toastr['error'](res.data.message || 'Failed to create investor.');
                    }
                })
                .catch((err) => {
                    toastr['error'](err.response?.data?.message || 'Failed to create investor.');
                });
        },
        resetInvestorForm() {
            this.investorForm = {
                investor_name: '',
                email: '',
                contact_no: '',
                profit_share_percentage: 25,
                create_login: false,
                password: '',
            };
        },
        saveTransaction() {
            if (!this.txnForm.investor_id || !this.txnForm.transaction_date || !this.txnForm.transaction_type) {
                toastr['error']('Investor, date, and type are required.');
                return;
            }
            axios.post('/api/investor/transaction', this.txnForm)
                .then((res) => {
                    if (res.data.status === 1) {
                        swal.fire({
                            title: 'Success',
                            html: "<h5 style='color:#9C9794'>Transaction Recorded Successfully!</h5>",
                            icon: 'success',
                        }).then(() => {
                            this.resetTxnForm();
                        });
                    } else {
                        toastr['error'](res.data.message || 'Failed to record transaction.');
                    }
                })
                .catch((err) => {
                    toastr['error'](err.response?.data?.message || 'Failed to record transaction.');
                });
        },
        resetTxnForm() {
            this.txnForm = {
                investor_id: this.txnForm.investor_id,
                transaction_date: this.getTodaysDate(),
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
                profit_share_percentage: inv.profit_share_percentage,
            };
        },
        cancelEdit() {
            this.editingId = null;
            this.editForm = {};
        },
        updateInvestor(investorId) {
            axios.put(`/api/investor/update/${investorId}`, this.editForm)
                .then((res) => {
                    if (res.data.status === 1) {
                        swal.fire({
                            title: 'Success',
                            html: "<h5 style='color:#9C9794'>Investor Updated Successfully!</h5>",
                            icon: 'success',
                        }).then(() => {
                            this.cancelEdit();
                            this.loadInvestors();
                        });
                    } else {
                        toastr['error'](res.data.message || 'Update failed.');
                    }
                })
                .catch((err) => {
                    toastr['error'](err.response?.data?.message || 'Update failed.');
                });
        },
        pdfUrl(investorId, period) {
            return pdfUrl(`/investor/pdf/${investorId}/${period}`);
        },
    },
};
</script>
