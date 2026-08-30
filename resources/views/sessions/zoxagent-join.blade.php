<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $session->displayTitle() }} — قاعة المحاضرة</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #0b1016;
        }
        .zox-join-frame {
            display: block;
            border: 0;
            width: 100%;
            height: 100%;
            background: #0b1016;
        }
    </style>
</head>
<body>
<iframe
    id="zoxMeetFrame"
    class="zox-join-frame"
    src="{{ $embedUrl }}"
    allow="{{ $iframeAllow }}"
    allowfullscreen
    referrerpolicy="origin"
    title="قاعة المحاضرة"
></iframe>
<script>
(function () {
    const returnUrl = @json($returnUrl);
    let redirected = false;
    function goBackToPlatform() {
        if (redirected) return;
        redirected = true;
        window.location.replace(returnUrl);
    }
    window.addEventListener('message', function (event) {
        const data = event.data || {};
        if (data.source !== 'zoxagent-rooms') return;
        if (data.type === 'disconnected' || data.type === 'left') {
            goBackToPlatform();
        }
    });
})();
</script>
</body>
</html>
