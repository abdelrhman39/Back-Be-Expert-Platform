/**
 * مصادقة تجريبية للوحة التحكم — يُستبدل بطلب API عند ربط الخادم
 */
(function (global) {
    var STORAGE_KEY = 'domain_admin_session';
    var DEMO_USERS = [
        'admin@local.invalid',
        'admin@example.com',
    ];
    var DEMO_PASSWORD = 'Admin@123';
    var DEMO_USER = {
        email: 'admin@example.com',
        password: DEMO_PASSWORD,
        name: 'مسؤول المنصة',
        role: 'super_admin',
    };

    function readSession() {
        try {
            var raw = sessionStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }

    function writeSession(data) {
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    }

    var domainAdmin = {
        isLoggedIn: function () {
            var s = readSession();
            return !!(s && s.token && s.expires > Date.now());
        },

        getSession: function () {
            return readSession();
        },

        login: function (email, password) {
            var normalized = (email || '').trim().toLowerCase();
            if (
                DEMO_USERS.indexOf(normalized) === -1 ||
                password !== DEMO_PASSWORD
            ) {
                return {
                    ok: false,
                    message: 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
                };
            }
            writeSession({
                token: 'demo_' + Date.now(),
                email: DEMO_USER.email,
                name: DEMO_USER.name,
                role: DEMO_USER.role,
                expires: Date.now() + 8 * 60 * 60 * 1000,
            });
            return { ok: true };
        },

        logout: function () {
            sessionStorage.removeItem(STORAGE_KEY);
        },

        requireAuth: function (loginPath) {
            if (!domainAdmin.isLoggedIn()) {
                window.location.replace(loginPath || 'login.html');
                return false;
            }
            return true;
        },
    };

    global.domainAdmin = domainAdmin;
})(typeof window !== 'undefined' ? window : this);
