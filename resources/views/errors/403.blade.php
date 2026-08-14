<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 | لوحة التحكم</title>
    <link rel="stylesheet" href="{{ static_asset('admin/css/admin.css') }}">
</head>
<body class="admin-dashboard-body" data-admin-layout="app">
    <div class="admin-app admin-app--dashboard" style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;">
        <section class="admin-crud-card" style="max-width:28rem;width:100%;text-align:center;padding:2rem 1.5rem;">
            <h1 style="font-size:1.15rem;font-weight:800;margin:0 0 0.5rem;color:var(--sa-ink);">403 — الوصول مرفوض</h1>
            <p style="color:var(--sa-muted);margin:0 0 1.25rem;line-height:1.5;">{{ $exception->getMessage() ?: 'ليس لديك صلاحية للوصول إلى هذه الصفحة.' }}</p>
            <a href="{{ route('admin.dashboard') }}" class="admin-btn-primary admin-btn-primary--sm">العودة للرئيسية</a>
        </section>
    </div>
</body>
</html>
