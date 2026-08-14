@php
    $pageHelp = \App\Support\PageHelp::current();
    // Guides are for admin console only — never on the public platform / student portal.
    $showPageHelp = request()->routeIs('admin.*');
@endphp

@if ($showPageHelp)
<button type="button" class="page-help-trigger" data-page-help-open aria-label="شرح آلية عمل الصفحة" title="شرح آلية عمل الصفحة">
    <i class="fa-solid fa-circle-question" aria-hidden="true"></i>
    <span>دليل الصفحة</span>
</button>

<dialog class="page-help-dialog" data-page-help-dialog dir="rtl" aria-labelledby="page-help-title">
    <div class="page-help-dialog__head">
        <div>
            <span class="page-help-dialog__eyebrow">مساعد الإدارة</span>
            <h2 id="page-help-title">{{ $pageHelp['title'] }}</h2>
        </div>
        <button type="button" data-page-help-close aria-label="إغلاق"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="page-help-dialog__body">
        <p>{{ $pageHelp['description'] }}</p>
        @if ($pageHelp['steps'])
            <ol>
                @foreach ($pageHelp['steps'] as $step)
                    <li><span>{{ $loop->iteration }}</span><div>{{ $step }}</div></li>
                @endforeach
            </ol>
        @endif
        <div class="page-help-dialog__note">
            <i class="fa-solid fa-shield-halved"></i>
            تظهر الإجراءات حسب صلاحيات حسابك، وتُسجل العمليات الحساسة في سجل التدقيق.
        </div>
    </div>
</dialog>

@once
    <style>
        .page-help-trigger{position:fixed;inset-inline-start:1.15rem;bottom:1.15rem;z-index:1040;display:inline-flex;align-items:center;gap:.45rem;padding:.68rem .9rem;border:0;border-radius:999px;background:#145a38;color:#fff;font:800 .75rem/1 inherit;box-shadow:0 10px 26px rgba(20,90,56,.3);cursor:pointer;transition:transform .2s ease,box-shadow .2s ease}
        .page-help-trigger:hover{transform:translateY(-2px);box-shadow:0 14px 32px rgba(20,90,56,.38)}.page-help-trigger i{font-size:1rem}
        .page-help-dialog{width:min(560px,calc(100vw - 2rem));max-height:calc(100vh - 2rem);padding:0;border:0;border-radius:20px;background:#fff;color:#17251f;box-shadow:0 28px 80px rgba(15,23,42,.32);overflow:hidden}
        .page-help-dialog::backdrop{background:rgba(15,23,42,.55);backdrop-filter:blur(3px)}
        .page-help-dialog__head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.2rem 1.3rem;background:linear-gradient(135deg,#0f5132,#1b8354);color:#fff}
        .page-help-dialog__eyebrow{font-size:.65rem;font-weight:900;opacity:.75}.page-help-dialog h2{margin:.25rem 0 0;font-size:1.18rem;font-weight:900}
        .page-help-dialog__head button{display:grid;place-items:center;width:2.2rem;height:2.2rem;border:1px solid rgba(255,255,255,.25);border-radius:10px;background:rgba(255,255,255,.1);color:#fff;cursor:pointer}
        .page-help-dialog__body{padding:1.25rem;overflow:auto}.page-help-dialog__body>p{margin:0 0 1rem;color:#52645b;font-size:.82rem;line-height:1.8}
        .page-help-dialog ol{display:grid;gap:.65rem;margin:0;padding:0;list-style:none}.page-help-dialog li{display:flex;align-items:flex-start;gap:.65rem;padding:.75rem;border:1px solid #e1ebe5;border-radius:12px;background:#f8fbf9;font-size:.78rem;font-weight:700;line-height:1.7}
        .page-help-dialog li>span{display:grid;place-items:center;width:1.65rem;height:1.65rem;flex:0 0 auto;border-radius:50%;background:#dcfce7;color:#166534;font-size:.68rem}
        .page-help-dialog__note{display:flex;align-items:flex-start;gap:.55rem;margin-top:1rem;padding:.75rem;border-radius:12px;background:#eff6ff;color:#1e40af;font-size:.7rem;line-height:1.7}
        @media(max-width:600px){.page-help-trigger span{display:none}.page-help-trigger{width:3rem;height:3rem;justify-content:center;padding:0}.page-help-dialog{border-radius:16px}}
    </style>
    <script>
        document.addEventListener('click', function (event) {
            var dialog = document.querySelector('[data-page-help-dialog]');
            if (!dialog) return;
            if (event.target.closest('[data-page-help-open]')) dialog.showModal();
            if (event.target.closest('[data-page-help-close]')) dialog.close();
            if (event.target === dialog) dialog.close();
        });
    </script>
@endonce
@endif
