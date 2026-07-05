import Router from "vue-router";
import Vue from "vue";
import { getToken, getUser, clearAuth, getHomeRoute, canAccessRoute } from '../js/auth';

const Login = () => import('../js/components/Login');
const Container = () => import('../js/components/containers/Container');
const Dashboard = () => import('../js/components/Dashboard');
const InvestorPortal = () => import('../js/components/InvestorPortal');
const ManageInvestors = () => import('../js/components/Investor/ManageInvestors');
const DistributeExpenses = () => import('../js/components/Investor/DistributeExpenses');
const LabJobContainer = () => import('../js/components/Laboratory/LabJobContainer');
const KarigarContainer = () => import('../js/components/Karigar/KarigarContainer');
const ManageWorkers = () => import('../js/components/Admin/ManageWorkers');
const AuditLogs = () => import('../js/components/Admin/AuditLogs');
const StockLedger = () => import('../js/components/Stock/StockLedger');
const InwardQuality = () => import('../js/components/InwardQuality/InwardQualityContainer');
const SellQuality = () => import('../js/components/SellQuality/SellQualityContainer');
const Broker = () => import('../js/components/Broker/BrokerContainer');
const Customer = () => import('../js/components/Customer/CustomerContainer');
const Vendor = () => import('../js/components/Vendor/VendorContainer');
const BankDetails = () => import('../js/components/BankDetails/BankDetailsContainer');
const Credit = () => import('../js/components/Credit/CreditContainer');
const ExpenseCategory = () => import('../js/components/Expense/ExpenseCategory/ExpenseCategoryContainer');
const ExpenseManagement = () => import("../js/components/Expense/ExpenseManagement/ExpenseManagementContainer");
const NewInward = () => import("../js/components/Inward/NewInward");
const NewChallan = () => import("../js/components/Challan/NewChallan");
const SMChallan = () => import("../js/components/Challan/SMChallan");
const ManageInward = () => import('../js/components/Inward/SMInward');
const GenerateDirectInvoice = () => import('../js/components/Invoice/GenerateDirectInvoice');
const ManageDirectInvoices = () => import('../js/components/Invoice/ManageDirectInvoice');
const GenerateFromChallan = () => import('../js/components/Invoice/GenerateFromChallan');
const ManageChallanInvoice = () => import('../js/components/Invoice/ManageChallanInvoice');

Vue.use(Router);

const router = new Router({
    mode: 'hash',
    linkActiveClass: 'active',
    scrollBehavior: () => ({ y: 0 }),
    routes: configRoutes()
});

router.beforeEach((to, from, next) => {
    const publicPages = ['/login'];
    const authRequired = !publicPages.includes(to.path);
    const loggedIn = !!getToken();
    const user = getUser();

    if (to.path === '/login' && loggedIn) {
        return next(getHomeRoute(user));
    }

    if (authRequired && !loggedIn) {
        return next('/login');
    }

    if (user && user.role === 'investor' && !to.path.startsWith('/investor') && to.path !== '/login') {
        return next('/investor');
    }

    if (loggedIn && user && user.role !== 'investor') {
        const targetPath = to.path === '/' ? '/dashboard' : to.path;

        if (targetPath === '/dashboard' && !canAccessRoute('/dashboard', user)) {
            return next(getHomeRoute(user));
        }

        if (!canAccessRoute(targetPath, user)) {
            return next(getHomeRoute(user));
        }
    }

    next();
});

export default router;

function configRoutes() {
    return [
        {
            path: '/login',
            name: 'Login',
            component: Login,
        },
        {
            path: '/',
            redirect: "/dashboard",
            name: 'Home',
            component: Container,
            children: [
                {
                    path: "dashboard",
                    name: "Dashboard",
                    component: Dashboard
                },
                {
                    path: "stock",
                    name: "StockLedger",
                    component: StockLedger
                },
                {
                    path: "investor",
                    name: "InvestorPortal",
                    component: InvestorPortal
                },
                {
                    path: "manageinvestors",
                    name: "ManageInvestors",
                    component: ManageInvestors
                },
                {
                    path: "distributeexpenses",
                    name: "DistributeExpenses",
                    component: DistributeExpenses
                },
                {
                    path: "laboratory",
                    name: "Laboratory",
                    component: LabJobContainer
                },
                {
                    path: "karigar",
                    name: "Karigar",
                    component: KarigarContainer
                },
                {
                    path: "workers",
                    name: "ManageWorkers",
                    component: ManageWorkers
                },
                {
                    path: "auditlogs",
                    name: "AuditLogs",
                    component: AuditLogs
                },
                {
                    path: "inwardquality",
                    name: "InwardQuality",
                    component: InwardQuality
                },
                {
                    path: "sellquality",
                    name: "SellQuality",
                    component: SellQuality
                },
                {
                    path: "broker",
                    name: "Broker",
                    component: Broker
                },
                {
                    path: "customer",
                    name: "CustomerContainer",
                    component: Customer
                },
                {
                    path: "vendor",
                    name: "VendorContainer",
                    component: Vendor
                },
                {
                    path: "bankdetails",
                    name: "BankDetails",
                    component: BankDetails
                },
                {
                    path: "credit",
                    name: "Credit",
                    component: Credit
                },
                {
                    path: "expensecategory",
                    name: "Expense Category",
                    component: ExpenseCategory
                },
                {
                    path: "expensemanagement",
                    name: "Expense Management",
                    component: ExpenseManagement
                },
                {
                    path: "newinward",
                    name: "New Inward",
                    component: NewInward
                },
                {
                    path: "manageinward",
                    name: "ManageInward",
                    component: ManageInward
                },
                {
                    path: "newchallan",
                    name: "New Challan",
                    component: NewChallan
                },
                {
                    path: "smchallan",
                    name: "S & M Challan",
                    component: SMChallan
                },
                {
                    path: "newdirectinvoice",
                    name: "New Direct Invoice",
                    component: GenerateDirectInvoice
                },
                {
                    path: "managedirectinvoice",
                    name: "Manage Direct Invoices",
                    component: ManageDirectInvoices
                },
                {
                    path: "invoicefromchallan",
                    name: "GenerateFromChallan",
                    component: GenerateFromChallan
                },
                {
                    path: "managechallaninvoice",
                    name: "ManageChallanInvoice",
                    component: ManageChallanInvoice
                }
            ]
        }
    ]
}
