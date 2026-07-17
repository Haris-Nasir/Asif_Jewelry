<template>
    <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-5 mt-3">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">{{ $t('dist.title') }}</h3>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">
                                        {{ $t('dist.helperOverhead') }}
                                    </p>
                                    <div class="form-group">
                                        <label>{{ $t('common.date') }} *</label>
                                        <input type="date" class="form-control" v-model="form.allocation_date" @change="loadShopExpenses">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ $t('dist.linkShop') }}</label>
                                        <select class="form-control" v-model="form.expense_id" @change="onExpenseSelect">
                                            <option value="">{{ $t('dist.noneManual') }}</option>
                                            <option
                                                v-for="exp in shopExpenses"
                                                :key="exp.expense_id"
                                                :value="exp.expense_id"
                                            >
                                                {{ exp.expense_date }} — {{ exp.expense_description }}
                                                (Rs. {{ formatAmount(exp.expense_amount) }}, remaining Rs. {{ formatAmount(exp.remaining_amount) }})
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ $t('common.description') }} *</label>
                                        <input type="text" class="form-control" v-model="form.description" :placeholder="$t('dist.phDesc')">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ $t('common.notes') }}</label>
                                        <textarea class="form-control" rows="2" v-model="form.notes"></textarea>
                                    </div>

                                    <h6 class="mt-3">{{ $t('dist.assign') }}</h6>
                                    <div class="form-group">
                                        <label class="d-block">{{ $t('dist.method') }}</label>
                                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                            <label
                                                class="btn btn-outline-primary"
                                                :class="{ active: form.allocation_mode === 'amount' }"
                                            >
                                                <input
                                                    type="radio"
                                                    value="amount"
                                                    v-model="form.allocation_mode"
                                                    @change="onAllocationModeChange"
                                                >
                                                {{ $t('dist.byAmount') }}
                                            </label>
                                            <label
                                                class="btn btn-outline-primary"
                                                :class="{ active: form.allocation_mode === 'percentage' }"
                                            >
                                                <input
                                                    type="radio"
                                                    value="percentage"
                                                    v-model="form.allocation_mode"
                                                    @change="onAllocationModeChange"
                                                >
                                                {{ $t('dist.byPercentage') }}
                                            </label>
                                        </div>
                                        <small v-if="form.allocation_mode === 'percentage'" class="text-muted d-block mt-1">
                                            {{ $t('dist.helperPct') }}
                                        </small>
                                    </div>
                                    <div
                                        class="form-group"
                                        v-if="form.allocation_mode === 'percentage' && !form.expense_id"
                                    >
                                        <label>{{ $t('dist.totalExpense') }} *</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            v-model="form.manual_total"
                                            min="0"
                                            step="0.01"
                                            :placeholder="$t('dist.phTotal')"
                                        >
                                    </div>
                                    <div
                                        class="alert alert-info py-2"
                                        v-if="form.allocation_mode === 'percentage' && expenseBaseTotal > 0"
                                    >
                                        {{ $t('dist.baseTotal', { amount: formatAmount(expenseBaseTotal) }) }}
                                        <span v-if="selectedExpense">
                                            {{ $t('dist.linkedExpense') }}
                                        </span>
                                    </div>
                                    <div
                                        class="form-row allocation-row mb-2"
                                        v-for="(row, index) in form.allocations"
                                        :key="'row-' + index"
                                    >
                                        <div class="form-group col-md-5 mb-0">
                                            <label v-if="index === 0">{{ $t('lab.investor') }}</label>
                                            <label v-else class="allocation-label-spacer">&nbsp;</label>
                                            <select class="form-control" v-model="row.investor_id">
                                                <option value="">{{ $t('common.selectInvestor') }}</option>
                                                <option
                                                    v-for="inv in investors"
                                                    :key="inv.investor_id"
                                                    :value="inv.investor_id"
                                                >
                                                    {{ inv.investor_name }} ({{ inv.profit_share_percentage }}%)
                                                </option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6 mb-0">
                                            <label v-if="index === 0">
                                                {{ form.allocation_mode === 'percentage' ? $t('dist.percentageLabel') : $t('investor.amountRs') }}
                                            </label>
                                            <label v-else class="allocation-label-spacer">&nbsp;</label>
                                            <input
                                                type="number"
                                                class="form-control"
                                                v-model="row.value"
                                                min="0"
                                                :max="form.allocation_mode === 'percentage' ? 100 : undefined"
                                                step="0.01"
                                                :placeholder="form.allocation_mode === 'percentage' ? $t('dist.phPct') : $t('dist.phAmt')"
                                            >
                                            <small class="text-muted d-block allocation-hint">
                                                <template v-if="form.allocation_mode === 'percentage' && row.value !== ''">
                                                    {{ $t('dist.rowEquals', { amount: formatAmount(rowAmount(row)) }) }}
                                                </template>
                                            </small>
                                        </div>
                                        <div class="form-group col-md-1 mb-0">
                                            <label v-if="index === 0">&nbsp;</label>
                                            <label v-else class="allocation-label-spacer">&nbsp;</label>
                                            <button
                                                type="button"
                                                class="btn btn-outline-danger btn-block"
                                                @click="removeRow(index)"
                                                :disabled="form.allocations.length === 1"
                                            >
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mb-3" @click="addRow">
                                        <i class="fas fa-plus"></i> {{ $t('dist.addInvestorRow') }}
                                    </button>

                                    <div class="alert alert-light border mb-0">
                                        {{ $t('dist.totalDistributing', { amount: formatAmount(distributionTotal) }) }}
                                        <span v-if="selectedExpense">
                                            &nbsp;|&nbsp; {{ $t('dist.remainingOnExpense', { amount: formatAmount(remainingAfterDistribution) }) }}
                                        </span>
                                        <span v-if="form.allocation_mode === 'percentage' && percentageTotal > 0">
                                            &nbsp;|&nbsp; {{ $t('dist.percentageAssigned', { pct: percentageTotal.toFixed(2) }) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-primary" @click="saveDistribution">{{ $t('dist.distribute') }}</button>
                                    <button class="btn btn-secondary" @click="resetForm">{{ $t('common.reset') }}</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7 mt-3">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">{{ $t('dist.history') }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-row mb-3">
                                        <div class="form-group col-md-4">
                                            <label>{{ $t('common.from') }}</label>
                                            <input type="date" class="form-control" v-model="filters.fromdate" @change="loadAllocations">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>{{ $t('common.to') }}</label>
                                            <input type="date" class="form-control" v-model="filters.todate" @change="loadAllocations">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>{{ $t('lab.investor') }}</label>
                                            <select class="form-control" v-model="filters.investor_id" @change="loadAllocations">
                                                <option value="">{{ $t('common.allInvestors') }}</option>
                                                <option
                                                    v-for="inv in investors"
                                                    :key="'filter-' + inv.investor_id"
                                                    :value="inv.investor_id"
                                                >
                                                    {{ inv.investor_name }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th>{{ $t('common.date') }}</th>
                                                    <th>{{ $t('lab.investor') }}</th>
                                                    <th>{{ $t('common.description') }}</th>
                                                    <th class="text-right">{{ $t('common.amount') }}</th>
                                                    <th width="90">{{ $t('common.actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="item in allocations.data" :key="item.investor_expense_allocation_id">
                                                    <td>{{ item.allocation_date }}</td>
                                                    <td>{{ item.investor_name }}</td>
                                                    <td>
                                                        {{ item.description }}
                                                        <small v-if="item.expense_description" class="text-muted d-block">
                                                            {{ $t('dist.linked', { name: item.expense_description }) }}
                                                        </small>
                                                    </td>
                                                    <td class="text-right">Rs. {{ formatAmount(item.allocated_amount) }}</td>
                                                    <td class="text-center">
                                                        <button class="btn btn-danger btn-sm" @click="deleteAllocation(item)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr v-if="!allocations.data || !allocations.data.length">
                                                    <td colspan="5" class="text-center text-muted">{{ $t('dist.noHistory') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
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

toastr.options = {
    closeButton: true,
    closeDuration: 200,
    progressBar: true,
};

function today() {
    return new Date().toISOString().slice(0, 10);
}

function daysAgo(n) {
    const d = new Date();
    d.setDate(d.getDate() - n);
    return d.toISOString().slice(0, 10);
}

function daysAhead(n) {
    const d = new Date();
    d.setDate(d.getDate() + n);
    return d.toISOString().slice(0, 10);
}

function maxDate(...dates) {
    return dates.filter(Boolean).sort().pop();
}

export default {
    name: 'DistributeExpenses',
    data() {
        return {
            investors: [],
            shopExpenses: [],
            allocations: { data: [] },
            form: this.emptyForm(),
            filters: {
                fromdate: daysAgo(30),
                todate: today(),
                investor_id: '',
            },
        };
    },
    computed: {
        selectedExpense() {
            if (!this.form.expense_id) {
                return null;
            }
            return this.shopExpenses.find(exp => String(exp.expense_id) === String(this.form.expense_id)) || null;
        },
        expenseBaseTotal() {
            if (this.selectedExpense) {
                return parseFloat(this.selectedExpense.expense_amount) || 0;
            }
            return parseFloat(this.form.manual_total) || 0;
        },
        distributionTotal() {
            return this.form.allocations.reduce((sum, row) => sum + this.rowAmount(row), 0);
        },
        percentageTotal() {
            if (this.form.allocation_mode !== 'percentage') {
                return 0;
            }
            return this.form.allocations.reduce((sum, row) => sum + (parseFloat(row.value) || 0), 0);
        },
        remainingAfterDistribution() {
            if (!this.selectedExpense) {
                return 0;
            }
            const remaining = parseFloat(this.selectedExpense.remaining_amount) - this.distributionTotal;
            return Math.max(remaining, 0);
        },
    },
    mounted() {
        this.loadInvestors();
        this.loadShopExpenses();
        this.loadAllocations();
    },
    methods: {
        emptyForm() {
            return {
                allocation_date: today(),
                expense_id: '',
                description: '',
                notes: '',
                allocation_mode: 'amount',
                manual_total: '',
                allocations: [{ investor_id: '', value: '' }],
            };
        },
        emptyRow() {
            return { investor_id: '', value: '' };
        },
        rowAmount(row) {
            if (this.form.allocation_mode === 'percentage') {
                const pct = parseFloat(row.value) || 0;
                return Math.round(this.expenseBaseTotal * pct / 100 * 100) / 100;
            }
            return parseFloat(row.value) || 0;
        },
        onAllocationModeChange() {
            this.form.allocations = [this.emptyRow()];
            this.form.manual_total = '';
        },
        formatAmount(value) {
            const amount = parseFloat(value || 0);
            return amount.toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        addRow() {
            this.form.allocations.push(this.emptyRow());
        },
        removeRow(index) {
            if (this.form.allocations.length > 1) {
                this.form.allocations.splice(index, 1);
            }
        },
        onExpenseSelect() {
            const expense = this.selectedExpense;
            if (expense && !this.form.description) {
                this.form.description = expense.expense_description;
            }
        },
        resetForm() {
            this.form = this.emptyForm();
        },
        loadInvestors() {
            axios.get('/api/investor/list')
                .then(res => {
                    this.investors = res.data.data || [];
                })
                .catch(() => toastr.error(this.$t('dist.loadInvestorsFail')));
        },
        loadShopExpenses() {
            const toDate = maxDate(today(), this.form.allocation_date, daysAhead(90));

            axios.get('/api/investor/expenses-for-allocation', {
                params: { fromdate: daysAgo(365), todate: toDate },
            })
                .then(res => {
                    this.shopExpenses = res.data.data || [];
                })
                .catch(() => toastr.error(this.$t('dist.loadExpensesFail')));
        },
        loadAllocations() {
            axios.get('/api/investor/expense-allocations', { params: this.filters })
                .then(res => {
                    this.allocations = res.data;
                })
                .catch(() => toastr.error(this.$t('dist.loadDistFail')));
        },
        saveDistribution() {
            if (this.form.allocation_mode === 'percentage' && this.expenseBaseTotal <= 0) {
                toastr.warning(this.$t('dist.linkOrTotal'));
                return;
            }

            const payload = {
                allocation_date: this.form.allocation_date,
                description: this.form.description,
                expense_id: this.form.expense_id || null,
                notes: this.form.notes || null,
                allocations: this.form.allocations
                    .filter(row => row.investor_id && this.rowAmount(row) > 0)
                    .map(row => ({
                        investor_id: row.investor_id,
                        amount: this.rowAmount(row),
                    })),
            };

            if (!payload.allocation_date || !payload.description) {
                toastr.warning(this.$t('dist.dateDescRequired'));
                return;
            }

            if (!payload.allocations.length) {
                toastr.warning(this.$t('dist.assignOne'));
                return;
            }

            if (this.form.allocation_mode === 'percentage' && this.percentageTotal > 100.001) {
                toastr.warning(this.$t('dist.pctOver'));
                return;
            }

            axios.post('/api/investor/expense-allocations', payload)
                .then(res => {
                    if (res.data.status === 1) {
                        toastr.success(res.data.message);
                        this.resetForm();
                        this.loadAllocations();
                        this.loadShopExpenses();
                    } else {
                        toastr.error(res.data.message || this.$t('dist.distributeFail'));
                    }
                })
                .catch(err => {
                    const message = err.response && err.response.data && err.response.data.message
                        ? err.response.data.message
                        : this.$t('dist.distributeFail');
                    toastr.error(message);
                });
        },
        deleteAllocation(item) {
            swal.fire({
                title: this.$t('dist.deleteConfirm'),
                text: `${item.investor_name} — Rs. ${this.formatAmount(item.allocated_amount)}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: this.$t('common.delete'),
            }).then(result => {
                if (!result.isConfirmed) {
                    return;
                }

                axios.delete(`/api/investor/expense-allocations/${item.investor_expense_allocation_id}`)
                    .then(res => {
                        if (res.data.status === 1) {
                            toastr.success(res.data.message);
                            this.loadAllocations();
                            this.loadShopExpenses();
                        } else {
                            toastr.error(res.data.message || this.$t('dist.deleteFail'));
                        }
                    })
                    .catch(() => toastr.error(this.$t('dist.deleteFail')));
            });
        },
    },
};
</script>

<style scoped>
.allocation-row {
    align-items: flex-start;
}

.allocation-label-spacer {
    display: block;
    margin-bottom: 0.5rem;
    visibility: hidden;
}

.allocation-hint {
    min-height: 1.25rem;
    margin-top: 0.25rem;
}
</style>
