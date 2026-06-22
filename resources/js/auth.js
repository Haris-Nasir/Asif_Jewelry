const TOKEN_KEY = 'asif_jewelry_token';
const USER_KEY = 'asif_jewelry_user';

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

export function pdfUrl(path) {
    const token = getToken();
    if (!token) return path;
    const separator = path.includes('?') ? '&' : '?';
    return `${path}${separator}token=${encodeURIComponent(token)}`;
}
