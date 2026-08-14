<script>
(function () {
    var form = document.getElementById('contact-form');
    var message = document.getElementById('contact-message');
    var counter = document.getElementById('char-count');

    if (!form) {
        return;
    }

    var supportEmail = form.dataset.supportEmail || '';
    var ticketUrl = form.dataset.ticketUrl || '#';
    var complainValue = form.dataset.complainValue || 'complain';
    var maxLength = parseInt(form.dataset.maxLength || '150', 10);

    if (message && counter) {
        message.addEventListener('input', function () {
            counter.textContent = String(message.value.length);
        });

        if (message.maxLength <= 0 && maxLength > 0) {
            message.maxLength = maxLength;
        }
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        if (!form.reportValidity()) {
            return;
        }

        var reasonField = form.querySelector('[name="reason_for_connect"]');
        var reason = reasonField ? reasonField.value : '';

        if (reason === complainValue && ticketUrl !== '#') {
            window.location.href = ticketUrl;
            return;
        }

        var name = (form.querySelector('[name="name"]')?.value || '').trim();
        var email = (form.querySelector('[name="email"]')?.value || '').trim();
        var phone = (form.querySelector('[name="phone"]')?.value || '').trim();
        var body = (form.querySelector('[name="message"]')?.value || '').trim();
        var subject = 'تواصل من الموقع — ' + name;
        var mailBody = 'الاسم: ' + name + '\n'
            + 'البريد: ' + email + '\n'
            + 'الجوال: ' + phone + '\n'
            + 'سبب التواصل: ' + reason + '\n\n'
            + body;

        if (!supportEmail) {
            return;
        }

        window.location.href = 'mailto:' + encodeURIComponent(supportEmail)
            + '?subject=' + encodeURIComponent(subject)
            + '&body=' + encodeURIComponent(mailBody);
    });
})();
</script>
