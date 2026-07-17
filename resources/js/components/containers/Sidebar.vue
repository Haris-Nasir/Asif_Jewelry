<template>
    <aside class="main-sidebar sidebar-dark-primary elevation-4 jewelry-sidebar">
        <a href="/" class="brand-link">
            <img
                src="/images/logo-gold-jewelry.png"
                alt="Ayub Jewelers Logo"
                class="brand-image elevation-3"
            />
            <span class="brand-text font-weight-light">Ayub Jewelers</span>
        </a>

        <div ref="sidebarMenu" class="sidebar">
            <nav>
                <ul
                    id="sidebar-nav"
                    class="nav nav-pills nav-sidebar flex-column"
                    role="menu"
                >
                    <template v-if="isInvestor">
                        <li class="nav-item">
                            <router-link to="/investor" class="nav-link">
                                <i class="nav-icon fas fa-chart-line"></i>
                                <p class="text-md">Investor Portal</p>
                            </router-link>
                        </li>
                    </template>

                    <template v-else>
                        <!-- Home -->
                        <li class="nav-section" v-if="can('dashboard')">
                            <button
                                type="button"
                                class="nav-section__toggle"
                                @click="toggleSection('home')"
                            >
                                <span>Home</span>
                                <i
                                    class="fas"
                                    :class="isSectionOpen('home') ? 'fa-chevron-up' : 'fa-chevron-down'"
                                ></i>
                            </button>
                            <ul class="nav nav-section__items" v-show="isSectionOpen('home')">
                                <li class="nav-item">
                                    <router-link to="/dashboard" class="nav-link">
                                        <i class="nav-icon fas fa-tachometer-alt"></i>
                                        <p class="text-md">Dashboard</p>
                                    </router-link>
                                </li>
                            </ul>
                        </li>

                        <!-- Setup -->
                        <li class="nav-section" v-if="can('masters')">
                            <button
                                type="button"
                                class="nav-section__toggle"
                                @click="toggleSection('setup')"
                            >
                                <span>Setup</span>
                                <i
                                    class="fas"
                                    :class="isSectionOpen('setup') ? 'fa-chevron-up' : 'fa-chevron-down'"
                                ></i>
                            </button>
                            <ul class="nav nav-section__items" v-show="isSectionOpen('setup')">
                                <li class="nav-item">
                                    <router-link to="/sellquality" class="nav-link">
                                        <i class="nav-icon far bi bi-gem"></i>
                                        <p class="text-md">Item Types</p>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/vendor" class="nav-link">
                                        <i class="nav-icon far bi bi-person-circle"></i>
                                        <p class="text-md">Supplier</p>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/customer" class="nav-link">
                                        <i class="nav-icon far bi bi-person-circle"></i>
                                        <p class="text-md">Customer</p>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/broker" class="nav-link">
                                        <i class="nav-icon fas fa-handshake"></i>
                                        <p class="text-md">Broker</p>
                                    </router-link>
                                </li>
                            </ul>
                        </li>

                        <!-- Stock -->
                        <li class="nav-section" v-if="can('stock') || can('purchases') || can('karigar')">
                            <button
                                type="button"
                                class="nav-section__toggle"
                                @click="toggleSection('stock')"
                            >
                                <span>Stock</span>
                                <i
                                    class="fas"
                                    :class="isSectionOpen('stock') ? 'fa-chevron-up' : 'fa-chevron-down'"
                                ></i>
                            </button>
                            <ul class="nav nav-section__items" v-show="isSectionOpen('stock')">
                                <li class="nav-item" v-if="can('stock')">
                                    <router-link to="/stock" class="nav-link">
                                        <i class="nav-icon fas fa-boxes"></i>
                                        <p class="text-md">Stock Ledger</p>
                                    </router-link>
                                </li>
                                <li class="nav-item" v-if="can('purchases')" data-submenu="purchase">
                                    <a href="#" class="nav-link" @click.prevent.stop="toggleSubmenu">
                                        <i class="nav-icon far bi bi-box-arrow-in-right"></i>
                                        <p class="text-md">
                                            Purchase
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <router-link to="/newinward" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p class="text-md">New Purchase</p>
                                            </router-link>
                                        </li>
                                        <li class="nav-item">
                                            <router-link to="/manageinward" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p class="text-md">Manage Purchase</p>
                                            </router-link>
                                        </li>
                                    </ul>
                                </li>
                                <li class="nav-item" v-if="can('karigar')">
                                    <router-link to="/karigar" class="nav-link">
                                        <i class="nav-icon fas fa-hammer"></i>
                                        <p class="text-md">Karigar</p>
                                    </router-link>
                                </li>
                            </ul>
                        </li>

                        <!-- Sales -->
                        <li class="nav-section" v-if="can('sales') || can('invoices')">
                            <button
                                type="button"
                                class="nav-section__toggle"
                                @click="toggleSection('sales')"
                            >
                                <span>Sales</span>
                                <i
                                    class="fas"
                                    :class="isSectionOpen('sales') ? 'fa-chevron-up' : 'fa-chevron-down'"
                                ></i>
                            </button>
                            <ul class="nav nav-section__items" v-show="isSectionOpen('sales')">
                                <li class="nav-item" v-if="can('sales')" data-submenu="salesbill">
                                    <a href="#" class="nav-link" @click.prevent.stop="toggleSubmenu">
                                        <i class="nav-icon far bi bi-receipt"></i>
                                        <p class="text-md">
                                            Sales Bill
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <router-link to="/newchallan" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p class="text-md">New Sales Bill</p>
                                            </router-link>
                                        </li>
                                        <li class="nav-item">
                                            <router-link to="/smchallan" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p class="text-md">Manage Sales Bill</p>
                                            </router-link>
                                        </li>
                                    </ul>
                                </li>
                                <li class="nav-item" v-if="can('invoices')" data-submenu="invoice">
                                    <a href="#" class="nav-link" @click.prevent.stop="toggleSubmenu">
                                        <i class="nav-icon far bi bi-receipt-cutoff"></i>
                                        <p class="text-md">
                                            Invoice
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <router-link to="/newdirectinvoice" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p class="text-md">Direct Invoice</p>
                                            </router-link>
                                        </li>
                                        <li class="nav-item">
                                            <router-link to="/managedirectinvoice" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p class="text-md">Manage Direct Invoice</p>
                                            </router-link>
                                        </li>
                                        <li class="nav-item">
                                            <router-link to="/invoicefromchallan" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p class="text-md">From Sales Bill</p>
                                            </router-link>
                                        </li>
                                        <li class="nav-item">
                                            <router-link to="/managechallaninvoice" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p class="text-md">Manage Invoices</p>
                                            </router-link>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>

                        <!-- Money -->
                        <li class="nav-section" v-if="can('expenses') || isAdmin">
                            <button
                                type="button"
                                class="nav-section__toggle"
                                @click="toggleSection('money')"
                            >
                                <span>Money</span>
                                <i
                                    class="fas"
                                    :class="isSectionOpen('money') ? 'fa-chevron-up' : 'fa-chevron-down'"
                                ></i>
                            </button>
                            <ul class="nav nav-section__items" v-show="isSectionOpen('money')">
                                <li class="nav-item" v-if="can('expenses')" data-submenu="expense">
                                    <a href="#" class="nav-link" @click.prevent.stop="toggleSubmenu">
                                        <i class="nav-icon far bi bi-dash-circle"></i>
                                        <p class="text-md">
                                            Expense
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item" v-if="isAdmin">
                                            <router-link to="/expensecategory" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>Expense Category</p>
                                            </router-link>
                                        </li>
                                        <li class="nav-item">
                                            <router-link to="/expensemanagement" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>Expense Management</p>
                                            </router-link>
                                        </li>
                                    </ul>
                                </li>
                                <li class="nav-item" v-if="isAdmin">
                                    <router-link to="/credit" class="nav-link">
                                        <i class="nav-icon fas fa-money-bill-wave"></i>
                                        <p class="text-md">Credit</p>
                                    </router-link>
                                </li>
                                <li class="nav-item" v-if="isAdmin">
                                    <router-link to="/manageinvestors" class="nav-link">
                                        <i class="nav-icon fas fa-hand-holding-usd"></i>
                                        <p class="text-md">Investors</p>
                                    </router-link>
                                </li>
                                <li class="nav-item" v-if="isAdmin">
                                    <router-link to="/distributeexpenses" class="nav-link">
                                        <i class="nav-icon fas fa-divide"></i>
                                        <p class="text-md">Distribute Expenses</p>
                                    </router-link>
                                </li>
                            </ul>
                        </li>

                        <!-- Lab -->
                        <li class="nav-section" v-if="can('laboratory')">
                            <button
                                type="button"
                                class="nav-section__toggle"
                                @click="toggleSection('lab')"
                            >
                                <span>Lab</span>
                                <i
                                    class="fas"
                                    :class="isSectionOpen('lab') ? 'fa-chevron-up' : 'fa-chevron-down'"
                                ></i>
                            </button>
                            <ul class="nav nav-section__items" v-show="isSectionOpen('lab')">
                                <li class="nav-item">
                                    <router-link to="/laboratory" class="nav-link">
                                        <i class="nav-icon fas fa-flask"></i>
                                        <p class="text-md">Laboratory</p>
                                    </router-link>
                                </li>
                            </ul>
                        </li>

                        <!-- Admin -->
                        <li class="nav-section" v-if="isAdmin">
                            <button
                                type="button"
                                class="nav-section__toggle"
                                @click="toggleSection('admin')"
                            >
                                <span>Admin</span>
                                <i
                                    class="fas"
                                    :class="isSectionOpen('admin') ? 'fa-chevron-up' : 'fa-chevron-down'"
                                ></i>
                            </button>
                            <ul class="nav nav-section__items" v-show="isSectionOpen('admin')">
                                <li class="nav-item">
                                    <router-link to="/workers" class="nav-link">
                                        <i class="nav-icon fas fa-users-cog"></i>
                                        <p class="text-md">Workers</p>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/bankdetails" class="nav-link">
                                        <i class="nav-icon far bi bi-bank"></i>
                                        <p class="text-md">Bank Details</p>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/auditlogs" class="nav-link">
                                        <i class="nav-icon fas fa-clipboard-list"></i>
                                        <p class="text-md">Audit Logs</p>
                                    </router-link>
                                </li>
                            </ul>
                        </li>
                    </template>
                </ul>
            </nav>
        </div>
    </aside>
</template>

<script>
import { getUser, hasPermission } from '../../auth';

const SECTION_KEY = 'ayub_jewelry_sidebar_sections';

export default {
    name: "Sidebar",
    data() {
        return {
            openSections: {
                home: true,
                setup: true,
                stock: true,
                sales: true,
                money: true,
                lab: true,
                admin: true,
            },
        };
    },
    computed: {
        user() {
            return getUser();
        },
        isAdmin() {
            return this.user && this.user.role === 'admin';
        },
        isInvestor() {
            return this.user && this.user.role === 'investor';
        },
    },
    mounted() {
        this.$el.addEventListener('wheel', this.onSidebarWheel, { passive: true, capture: true });
        this.restoreSections();
        this.openSectionForRoute(this.$route.path);
        window.addEventListener('keydown', this.onGlobalKeydown);
    },
    beforeDestroy() {
        this.$el.removeEventListener('wheel', this.onSidebarWheel, { capture: true });
        window.removeEventListener('keydown', this.onGlobalKeydown);
    },
    watch: {
        '$route.path'(path) {
            this.openSectionForRoute(path);
        },
    },
    methods: {
        can(permission) {
            return hasPermission(permission);
        },
        isSectionOpen(name) {
            return this.openSections[name] !== false;
        },
        toggleSection(name) {
            this.$set(this.openSections, name, !this.isSectionOpen(name));
            this.persistSections();
        },
        persistSections() {
            try {
                localStorage.setItem(SECTION_KEY, JSON.stringify(this.openSections));
            } catch (e) {
                // ignore
            }
        },
        restoreSections() {
            try {
                const raw = localStorage.getItem(SECTION_KEY);
                if (!raw) return;
                const saved = JSON.parse(raw);
                Object.keys(this.openSections).forEach((key) => {
                    if (typeof saved[key] === 'boolean') {
                        this.$set(this.openSections, key, saved[key]);
                    }
                });
            } catch (e) {
                // ignore
            }
        },
        openSectionForRoute(path) {
            const map = {
                '/dashboard': 'home',
                '/sellquality': 'setup',
                '/vendor': 'setup',
                '/customer': 'setup',
                '/broker': 'setup',
                '/stock': 'stock',
                '/newinward': 'stock',
                '/manageinward': 'stock',
                '/karigar': 'stock',
                '/newchallan': 'sales',
                '/smchallan': 'sales',
                '/newdirectinvoice': 'sales',
                '/managedirectinvoice': 'sales',
                '/invoicefromchallan': 'sales',
                '/managechallaninvoice': 'sales',
                '/expensecategory': 'money',
                '/expensemanagement': 'money',
                '/credit': 'money',
                '/manageinvestors': 'money',
                '/distributeexpenses': 'money',
                '/laboratory': 'lab',
                '/workers': 'admin',
                '/bankdetails': 'admin',
                '/auditlogs': 'admin',
            };
            const section = map[path];
            if (section) {
                this.$set(this.openSections, section, true);
            }
            this.openSubmenuForRoute(path);
        },
        collapseSectionsExcept(exceptSection) {
            Object.keys(this.openSections).forEach((key) => {
                this.$set(this.openSections, key, key === exceptSection);
            });
            this.persistSections();
        },
        setSubmenuOpen(name) {
            if (!this.$el) {
                return;
            }
            this.$nextTick(() => {
                this.$el.querySelectorAll('[data-submenu]').forEach((item) => {
                    const itemName = item.getAttribute('data-submenu');
                    const menu = item.querySelector(':scope > .nav-treeview');
                    const isTarget = !!name && itemName === name;
                    item.classList.toggle('menu-open', isTarget);
                    if (menu) {
                        menu.style.display = isTarget ? 'block' : '';
                    }
                });
            });
        },
        openSubmenu(name) {
            this.setSubmenuOpen(name);
        },
        openSubmenuForRoute(path) {
            const submenuMap = {
                '/newinward': 'purchase',
                '/manageinward': 'purchase',
                '/newchallan': 'salesbill',
                '/smchallan': 'salesbill',
                '/newdirectinvoice': 'invoice',
                '/managedirectinvoice': 'invoice',
                '/invoicefromchallan': 'invoice',
                '/managechallaninvoice': 'invoice',
                '/expensecategory': 'expense',
                '/expensemanagement': 'expense',
            };
            const submenu = submenuMap[path];
            if (submenu) {
                this.openSubmenu(submenu);
            }
        },
        toggleSubmenu(event) {
            const item = event.currentTarget.closest('.nav-item');
            if (!item) {
                return;
            }

            const menu = item.querySelector(':scope > .nav-treeview');
            const isOpen = item.classList.toggle('menu-open');

            if (menu) {
                menu.style.display = isOpen ? 'block' : '';
            }
        },
        onSidebarWheel(event) {
            const sidebar = this.$refs.sidebarMenu;
            if (!sidebar || sidebar.scrollHeight <= sidebar.clientHeight) {
                return;
            }

            event.stopPropagation();
        },
        isTypingTarget(target) {
            if (!target || !(target instanceof Element)) {
                return false;
            }
            const tag = target.tagName;
            if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') {
                return true;
            }
            if (target.isContentEditable) {
                return true;
            }
            return !!target.closest('.ui.dropdown, .ui.search, [contenteditable="true"]');
        },
        onGlobalKeydown(event) {
            if (this.isInvestor) {
                return;
            }
            if (event.metaKey || event.ctrlKey || event.altKey) {
                return;
            }
            if (this.isTypingTarget(event.target)) {
                return;
            }

            const key = String(event.key || '').toLowerCase();
            const shortcuts = {
                d: { path: '/dashboard', permission: 'dashboard', section: 'home' },
                p: { path: '/newinward', permission: 'purchases', section: 'stock', submenu: 'purchase' },
                m: { path: '/manageinward', permission: 'purchases', section: 'stock', submenu: 'purchase' },
                k: { path: '/karigar', permission: 'karigar', section: 'stock' },
                g: { path: '/stock', permission: 'stock', section: 'stock' },
                b: { path: '/newchallan', permission: 'sales', section: 'sales', submenu: 'salesbill' },
                i: { path: '/newdirectinvoice', permission: 'invoices', section: 'sales', submenu: 'invoice' },
                e: { path: '/expensemanagement', permission: 'expenses', section: 'money', submenu: 'expense' },
                l: { path: '/laboratory', permission: 'laboratory', section: 'lab' },
                c: { path: '/customer', permission: 'masters', section: 'setup' },
                v: { path: '/vendor', permission: 'masters', section: 'setup' },
                t: { path: '/sellquality', permission: 'masters', section: 'setup' },
                r: { path: '/broker', permission: 'masters', section: 'setup' },
            };

            const action = shortcuts[key];
            if (!action) {
                return;
            }
            if (action.permission && !this.can(action.permission) && !this.isAdmin) {
                return;
            }

            event.preventDefault();
            this.collapseSectionsExcept(action.section);
            this.setSubmenuOpen(action.submenu || null);
            if (this.$route.path !== action.path) {
                this.$router.push(action.path);
            }
        },
    },
};
</script>
