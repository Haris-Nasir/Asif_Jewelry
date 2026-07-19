<template>
    <div class="investor-multi-select" ref="root">
        <div
            class="investor-multi-select__control form-control"
            :class="{ 'is-open': open }"
            @click="toggleOpen"
        >
            <div class="investor-multi-select__value">
                <span v-if="!selectedInvestors.length" class="text-muted">{{ placeholder || $t('lab.phInvestors') }}</span>
                <template v-else>
                    <span
                        v-for="inv in selectedInvestors"
                        :key="inv.investor_id"
                        class="badge badge-primary investor-multi-select__badge"
                    >
                        {{ inv.investor_name }}
                        <button type="button" class="investor-multi-select__remove" @click.stop="removeInvestor(inv.investor_id)">&times;</button>
                    </span>
                </template>
            </div>
            <i class="fas fa-chevron-down investor-multi-select__arrow"></i>
        </div>
        <div v-show="open" class="investor-multi-select__menu" @click.stop>
            <div
                v-for="inv in investors"
                :key="inv.investor_id"
                class="investor-multi-select__option"
                @click="toggleInvestor(inv.investor_id)"
            >
                <input
                    type="checkbox"
                    class="mr-2"
                    :checked="isSelected(inv.investor_id)"
                    @click.stop
                    @change="toggleInvestor(inv.investor_id)"
                >
                <span>
                    {{ inv.investor_name }}
                    <small class="text-muted d-block">
                        <template v-if="isCustomInvestor(inv)">{{ $t('investor.profitShareLabel') }} {{ inv.profit_share_percentage }}% · </template>
                        <template v-else>{{ $t('lab.splitByInvestment') }} · </template>
                        {{ $t('lab.balance') }} {{ formatBalance(inv.total_invested) }}
                    </small>
                </span>
            </div>
            <p v-if="!investors.length" class="text-muted small mb-0 px-2 py-1">{{ $t('lab.noInvestors') }}</p>
        </div>
    </div>
</template>

<script>
import { formatAmount } from '../../currency';

export default {
    name: 'InvestorMultiSelect',
    props: {
        investors: {
            type: Array,
            default: () => [],
        },
        value: {
            type: Array,
            default: () => [],
        },
        placeholder: {
            type: String,
            default: '',
        },
    },
    data() {
        return {
            open: false,
        };
    },
    computed: {
        selectedIds() {
            return this.value.map((id) => parseInt(id, 10)).filter((id) => !Number.isNaN(id));
        },
        selectedInvestors() {
            return this.investors.filter((inv) => this.selectedIds.includes(parseInt(inv.investor_id, 10)));
        },
    },
    mounted() {
        document.addEventListener('click', this.handleOutsideClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleOutsideClick);
    },
    methods: {
        isCustomInvestor(inv) {
            return (inv.profit_split_mode || 'investment') === 'custom';
        },
        formatBalance(value) {
            return formatAmount(value || 0);
        },
        isSelected(investorId) {
            return this.selectedIds.includes(parseInt(investorId, 10));
        },
        toggleOpen() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
        handleOutsideClick(event) {
            if (this.$refs.root && !this.$refs.root.contains(event.target)) {
                this.close();
            }
        },
        emitSelection(ids) {
            this.$emit('input', ids);
            this.$emit('change', ids);
        },
        toggleInvestor(investorId) {
            const id = parseInt(investorId, 10);
            const next = this.isSelected(id)
                ? this.selectedIds.filter((item) => item !== id)
                : [...this.selectedIds, id];
            this.emitSelection(next);
        },
        removeInvestor(investorId) {
            const id = parseInt(investorId, 10);
            this.emitSelection(this.selectedIds.filter((item) => item !== id));
        },
    },
};
</script>

<style scoped>
.investor-multi-select {
    position: relative;
}

.investor-multi-select__control {
    display: flex;
    align-items: center;
    min-height: calc(1.5em + 0.75rem + 2px);
    height: auto;
    cursor: pointer;
    background: #fff;
    padding-right: 2rem;
}

.investor-multi-select__control.is-open {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.investor-multi-select__value {
    flex: 1;
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    min-height: 1.25rem;
}

.investor-multi-select__badge {
    display: inline-flex;
    align-items: center;
    font-size: 0.8rem;
    font-weight: 500;
    padding: 0.25rem 0.4rem;
}

.investor-multi-select__remove {
    border: 0;
    background: transparent;
    color: #fff;
    margin-left: 0.25rem;
    padding: 0;
    line-height: 1;
    font-size: 1rem;
    cursor: pointer;
}

.investor-multi-select__arrow {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    pointer-events: none;
}

.investor-multi-select__menu {
    position: absolute;
    z-index: 1050;
    top: calc(100% + 2px);
    left: 0;
    right: 0;
    max-height: 220px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.investor-multi-select__option {
    display: flex;
    align-items: flex-start;
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    border-bottom: 1px solid #f1f3f5;
}

.investor-multi-select__option:last-child {
    border-bottom: 0;
}

.investor-multi-select__option:hover {
    background: #f8f9fa;
}
</style>
