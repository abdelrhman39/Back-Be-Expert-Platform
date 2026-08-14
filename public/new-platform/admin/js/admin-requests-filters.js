/** تعبئة قوائم الفلاتر المشتركة لصفحات الطلبات */
(function () {
    var d = window.domainRequests;
    if (!d) return;

    function fill(id, items) {
        var sel = document.getElementById(id);
        if (!sel) return;
        var placeholder = sel.options[0];
        var emptyVal = placeholder ? placeholder.value : '';
        var emptyText = placeholder ? placeholder.textContent : 'الكل';
        sel.innerHTML = '';
        var o0 = document.createElement('option');
        o0.value = emptyVal;
        o0.textContent = emptyText;
        sel.appendChild(o0);
        items.forEach(function (item) {
            var opt = document.createElement('option');
            opt.value = item.value;
            opt.textContent = item.label;
            sel.appendChild(opt);
        });
    }

    fill('f-semester', d.semesters);
    fill('f-program', d.programs);
})();
