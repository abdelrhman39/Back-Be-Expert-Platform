/**
 * المستويات الأكاديمية — بيانات تجريبية
 */
(function (global) {
    var LEVELS = [
        { id: 1, name: 'المستوى الأول', status: 'active' },
        { id: 2, name: 'المستوى الثاني', status: 'active' },
        { id: 3, name: 'المستوى الثالث', status: 'active' },
        { id: 4, name: 'المستوى الرابع', status: 'active' },
        { id: 5, name: 'المستوى الخامس', status: 'active' },
        { id: 6, name: 'المستوى السادس', status: 'active' },
    ];

    function resolve(opts) {
        opts = opts || {};
        if (opts.id != null) {
            return LEVELS.find(function (l) {
                return String(l.id) === String(opts.id);
            });
        }
        if (opts.name) {
            return LEVELS.find(function (l) {
                return l.name === opts.name;
            });
        }
        return null;
    }

    global.domainLevels = {
        list: LEVELS,
        resolve: resolve,
    };
})(typeof window !== 'undefined' ? window : this);
