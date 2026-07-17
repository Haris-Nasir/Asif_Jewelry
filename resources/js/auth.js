const TOKEN_KEY = 'ayub_jewelry_token';
const USER_KEY = 'ayub_jewelry_user';

export function getToken() {
    return localStorage.getItem(TOKEN_KEY);
}

export function getUser() {
    const raw = localStorage.getItem(USER_KEY);
    return raw ? JSON.parse(raw) : null;
}

export function setAuth(token, user) {
    localStorage.setItem(TOKEN_KEY, token);
    localStorage.setItem(USER_KEY, JSON.stringify(user));
}

export function clearAuth() {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
}

export function isAuthenticated() {
    return !!getToken();
}

export function hasRole(...roles) {
    const user = getUser();
    return user && roles.includes(user.role);
}

export function hasPermission(permission) {
    const user = getUser();
    if (!user) return false;
    if (user.role === 'admin') return true;
    return Array.isArray(user.permissions) && user.permissions.includes(permission);
}

const HOME_ROUTE_OPTIONS = [
    { permission: 'dashboard', path: '/dashboard' },
    { permission: 'stock', path: '/stock' },
    { permission: 'purchases', path: '/manageinward' },
    { permission: 'sales', path: '/smchallan' },
    { permission: 'invoices', path: '/managedirectinvoice' },
    { permission: 'expenses', path: '/expensemanagement' },
    { permission: 'karigar', path: '/karigar' },
    { permission: 'laboratory', path: '/laboratory' },
    { permission: 'masters', path: '/sellquality' },
];

const ROUTE_PERMISSIONS = {
    '/dashboard': 'dashboard',
    '/stock': 'stock',
    '/sellquality': 'masters',
    '/vendor': 'masters',
    '/customer': 'masters',
    '/broker': 'masters',
    '/inwardquality': 'masters',
    '/newinward': 'purchases',
    '/manageinward': 'purchases',
    '/newchallan': 'sales',
    '/smchallan': 'sales',
    '/newdirectinvoice': 'invoices',
    '/managedirectinvoice': 'invoices',
    '/invoicefromchallan': 'invoices',
    '/managechallaninvoice': 'invoices',
    '/expensemanagement': 'expenses',
    '/expensecategory': 'expenses',
    '/laboratory': 'laboratory',
    '/karigar': 'karigar',
};

const ADMIN_ONLY_ROUTES = new Set([
    '/workers',
    '/auditlogs',
    '/manageinvestors',
    '/distributeexpenses',
    '/credit',
    '/bankdetails',
]);

export function getHomeRoute(user = getUser()) {
    if (!user) {
        return '/login';
    }

    if (user.role === 'investor') {
        return '/investor';
    }

    if (user.role === 'admin') {
        return '/dashboard';
    }

    for (const option of HOME_ROUTE_OPTIONS) {
        if (hasPermission(option.permission)) {
            return option.path;
        }
    }

    return '/login';
}

export function canAccessRoute(path, user = getUser()) {
    if (!user) {
        return false;
    }

    if (user.role === 'admin') {
        return true;
    }

    if (user.role === 'investor') {
        return path === '/investor' || path.startsWith('/investor');
    }

    const normalized = path.replace(/\/$/, '') || '/';

    if (ADMIN_ONLY_ROUTES.has(normalized)) {
        return false;
    }

    const permission = ROUTE_PERMISSIONS[normalized];
    if (!permission) {
        return true;
    }

    return hasPermission(permission);
}

export function pdfUrl(path) {
    const token = getToken();
    if (!token) return path;
    const separator = path.includes('?') ? '&' : '?';
    return `${path}${separator}token=${encodeURIComponent(token)}`;
}
