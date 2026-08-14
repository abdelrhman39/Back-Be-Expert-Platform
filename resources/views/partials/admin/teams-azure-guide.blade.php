@php
    use App\Support\TeamsSettings;

    $redirectUri = TeamsSettings::redirectUri();
    $appUrl = config('app.url');
@endphp

<section class="admin-crud-card teams-azure-guide">
    <div class="admin-crud-card__head">
        <h2>دليل ربط Microsoft Teams و Azure AD</h2>
        <p class="admin-crud-card__meta">خطوات عملية لربط المنصة مع Microsoft 365 — المحاضرات، الحضور، التسجيلات، وربط حساب الطالب.</p>
    </div>

    <div class="teams-guide-block teams-guide-block--info">
        <h3>ماذا يفعل النظام بعد الربط؟</h3>
        <ul>
            <li><strong>إنشاء اجتماعات Teams</strong> تلقائياً لكل حصة أكاديمية (رابط انضمام + تسجيل تلقائي).</li>
            <li><strong>مزامنة الحضور</strong> من تقارير Teams ومطابقة الطلاب عبر البريد المرتبط.</li>
            <li><strong>مزامنة التسجيلات</strong> بعد انتهاء المحاضرة ونشرها للطلاب (يدوي أو تلقائي).</li>
            <li><strong>ربط حساب الطالب</strong> عبر OAuth — كل طالب يربط Microsoft Teams من الإعدادات/الملف الشخصي.</li>
            <li><strong>بانر المحاضرة</strong> — تنبيه «جارية الآن» و«قادمة» في بوابة الطالب.</li>
        </ul>
    </div>

    <div class="teams-guide-block">
        <h3>المتطلبات قبل البدء</h3>
        <ul>
            <li>اشتراك <strong>Microsoft 365</strong> للمؤسسة (Teams + Azure AD).</li>
            <li>صلاحية <strong>Global Administrator</strong> أو <strong>Application Administrator</strong> في Azure لمنح Admin Consent.</li>
            <li>حساب <strong>Organizer</strong> (محاضر/منسق) مرخّص لـ Teams Meetings + Cloud Recording (تحقق مع IT).</li>
            <li>تفعيل <strong>Cloud recording</strong> في Teams Admin Center للمؤسسة.</li>
            <li>عنوان المنصة ثابت في <code>APP_URL</code>: <span dir="ltr">{{ $appUrl }}</span></li>
        </ul>
    </div>

    <div class="teams-guide-block">
        <h3>الخطوة 1 — تسجيل التطبيق في Azure</h3>
        <ol>
            <li>افتح <a href="https://portal.azure.com/#view/Microsoft_AAD_RegisteredApps/ApplicationsListBlade" target="_blank" rel="noopener">Azure Portal → App registrations</a>.</li>
            <li>اضغط <strong>New registration</strong>:
                <ul>
                    <li>الاسم: <code>domain Platform</code> (أو اسم مؤسستكم).</li>
                    <li>Supported account types: <strong>Accounts in this organizational directory only</strong> (Single tenant).</li>
                    <li>Redirect URI: <strong>Web</strong> → <code dir="ltr">{{ $redirectUri }}</code></li>
                </ul>
            </li>
            <li>بعد الإنشاء، انسخ:
                <ul>
                    <li><strong>Application (client) ID</strong> → حقل Client ID أعلاه.</li>
                    <li><strong>Directory (tenant) ID</strong> → حقل Tenant ID أعلاه.</li>
                </ul>
            </li>
            <li>من <strong>Certificates &amp; secrets</strong> → New client secret → انسخ القيمة فوراً إلى Client Secret (لا تُعرض مرة أخرى).</li>
        </ol>
    </div>

    <div class="teams-guide-block">
        <h3>الخطوة 2 — صلاحيات API (Microsoft Graph)</h3>
        <p>من <strong>API permissions → Add a permission → Microsoft Graph</strong>:</p>

        <h4 class="teams-guide-sub">أ) Delegated — لربط حساب الطالب (OAuth)</h4>
        <table class="teams-guide-table">
            <thead><tr><th>الصلاحية</th><th>الاستخدام</th></tr></thead>
            <tbody>
                <tr><td><code>openid</code>, <code>profile</code>, <code>email</code></td><td>تسجيل الدخول وقراءة البريد</td></tr>
                <tr><td><code>offline_access</code></td><td>تجديد Token تلقائياً</td></tr>
                <tr><td><code>User.Read</code></td><td>مطابقة هوية الطالب مع Teams</td></tr>
            </tbody>
        </table>

        <h4 class="teams-guide-sub">ب) Application — للمنصة (Client Credentials)</h4>
        <table class="teams-guide-table">
            <thead><tr><th>الصلاحية</th><th>الاستخدام في domain</th></tr></thead>
            <tbody>
                <tr><td><code>OnlineMeetings.ReadWrite.All</code></td><td>إنشاء اجتماعات Teams للحصص</td></tr>
                <tr><td><code>OnlineMeetingArtifact.Read.All</code></td><td>قراءة تقارير الحضور</td></tr>
                <tr><td><code>OnlineMeetingRecording.Read.All</code></td><td>مزامنة تسجيلات المحاضرات</td></tr>
            </tbody>
        </table>
        <p class="teams-guide-note">بعد إضافة صلاحيات Application، اضغط <strong>Grant admin consent for [tenant]</strong> — بدونها لن تعمل المزامنة.</p>
    </div>

    <div class="teams-guide-block">
        <h3>الخطوة 3 — Organizer User ID</h3>
        <ol>
            <li>افتح <a href="https://portal.azure.com/#view/Microsoft_AAD_Users/Administration/MsGraphUsersMenuBlade/~/AllUsers" target="_blank" rel="noopener">Azure AD → Users</a>.</li>
            <li>اختر حساب المحاضر/المنسق (يجب أن يملك ترخيص Teams).</li>
            <li>انسخ <strong>Object ID</strong> → حقل Organizer User ID أعلاه.</li>
        </ol>
        <p class="teams-guide-note">جميع الاجتماعات تُنشأ باسم هذا الحساب. تقارير الحضور والتسجيلات تُقرأ من اجتماعاته.</p>
    </div>

    <div class="teams-guide-block">
        <h3>الخطوة 4 — إعدادات Redirect URI</h3>
        <p>يجب أن يطابق الرابط في Azure <strong>بالضبط</strong> ما يلي:</p>
        <div class="admin-copy-field">
            <input type="text" class="admin-control" readonly dir="ltr" value="{{ $redirectUri }}">
            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" onclick="navigator.clipboard.writeText('{{ $redirectUri }}')">نسخ</button>
        </div>
        <p class="teams-guide-note">مسار ربط الطالب: <code dir="ltr">{{ url('/integrations/microsoft/connect') }}</code> — يتطلب تسجيل دخول.</p>
    </div>

    <div class="teams-guide-block">
        <h3>الخطوة 5 — حفظ الإعدادات وتشغيل المزامنة</h3>
        <ol>
            <li>فعّل «تكامل Microsoft Teams» واحفظ جميع الحقول.</li>
            <li>أنشئ حصة من الشعبة → سيتم إنشاء اجتماع Teams تلقائياً.</li>
            <li>بعد المحاضرة شغّل (أو انتظر المجدول):
                <ul>
                    <li><code dir="ltr">php artisan teams:sync-attendance</code> — كل 15 دقيقة</li>
                    <li><code dir="ltr">php artisan teams:sync-recordings</code> — كل 30 دقيقة</li>
                </ul>
            </li>
            <li>اطلب من الطلاب ربط حساب Teams من <strong>الإعدادات → Microsoft Teams</strong>.</li>
        </ol>
    </div>

    <div class="teams-guide-block teams-guide-block--warn">
        <h3>استكشاف الأخطاء الشائعة</h3>
        <table class="teams-guide-table">
            <thead><tr><th>المشكلة</th><th>الحل</th></tr></thead>
            <tbody>
                <tr><td>فشل إنشاء الاجتماع</td><td>تحقق من Admin Consent و<code>OnlineMeetings.ReadWrite.All</code></td></tr>
                <tr><td>لا يظهر تقرير حضور</td><td>انتظر 15–30 دقيقة بعد انتهاء المحاضرة؛ تحقق من Organizer ID</td></tr>
                <tr><td>التسجيل processing</td><td>Cloud Recording قد يستغرق 1–4 ساعات؛ أو الصق رابطاً يدوياً</td></tr>
                <tr><td>الطالب غير محضّر</td><td>يجب ربط Teams + استخدام نفس البريد في M365</td></tr>
                <tr><td>redirect_uri mismatch</td><td>طابق <code>APP_URL</code> مع Azure Redirect URI حرفياً</td></tr>
            </tbody>
        </table>
    </div>

    <div class="teams-guide-block">
        <h3>ما يُخزَّن في المنصة (لا حاجة لـ .env بعد الحفظ)</h3>
        <ul>
            <li>Tenant ID, Client ID, Client Secret — في <code>platform_settings</code> (مشفّر).</li>
            <li>Organizer User ID — معرّف المنظم.</li>
            <li>إعدادات الحضور والتسجيل — من هذه الصفحة.</li>
            <li>Tokens الطلاب — في <code>microsoft_teams_connections</code> (مشفّرة).</li>
        </ul>
    </div>
</section>
