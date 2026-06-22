<template>
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="/" class="brand-link">
            <img
                src="dist/img/SahajanandLogo.png"
                alt="Asif Jewelry Logo"
                class="brand-image img-circle elevation-3"
                style="opacity: 0.8"
            />
            <span class="brand-text font-weight-light">Asif Jewelry</span>
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                <ul
                    class="nav nav-pills nav-sidebar flex-column"
                    data-widget="treeview"
                    role="menu"
                    data-accordion="false"
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
                        <li class="nav-item">
                            <router-link to="/dashboard" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p class="text-md">Dashboard</p>
                            </router-link>
                        </li>

                        <li class="nav-item">
                            <router-link to="/sellquality" class="nav-link">
                                <i class="nav-icon far bi bi-gem"></i>
                                <p class="text-md">Item Types</p>
                            </router-link>
                        </li>

                        <li class="nav-item">
                            <router-link to="/stock" class="nav-link">
                                <i class="nav-icon fas fa-boxes"></i>
                                <p class="text-md">Stock Ledger</p>
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
                            <a href="#" class="nav-link">
                                <i class="nav-icon nav-icon far bi bi-box-arrow-in-right"></i>
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

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon nav-icon far bi bi-receipt"></i>
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

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon nav-icon far bi bi-receipt-cutoff"></i>
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

                        <li class="nav-item" v-if="isAdmin">
                            <router-link to="/bankdetails" class="nav-link">
                                <i class="nav-icon far bi bi-bank"></i>
                                <p>Bank Details</p>
                            </router-link>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon far bi bi-dash-circle"></i>
                                <p class="text-md">
                                    Expense
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item" v-if="isAdmin">
                                    <router-link to="expensecategory" class="nav-link">
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
                                <i class="nav-icon fas fa-rupee-sign"></i>
                                <p>Credit</p>
                            </router-link>
                        </li>

                        <li class="nav-item" v-if="isAdmin">
                            <router-link to="/investor" class="nav-link">
                                <i class="nav-icon fas fa-hand-holding-usd"></i>
                                <p>Investors</p>
                            </router-link>
                        </li>
                    </template>
                </ul>
            </nav>
        </div>
    </aside>
</template>

<script>
import { getUser } from '../../auth';

export default {
    name: "Sidebar",
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
};
</script>
