@php
    $active = $active ?? 'form-fields';
@endphp

<nav class="ff-subnav" aria-label="إعدادات البرنامج">
    <a href="{{ route('admin.fellowships.edit', $fellowship) }}" @class(['ff-subnav__item', 'is-active' => $active === 'details'])>
        تفاصيل النموذج
    </a>
    <a href="{{ route('admin.fellowships.form-fields', $fellowship) }}" @class(['ff-subnav__item', 'is-active' => $active === 'form-fields'])>
        حقول النموذج
    </a>
    <a href="{{ route('admin.fellowships.settings', $fellowship) }}" @class(['ff-subnav__item', 'is-active' => $active === 'settings'])>
        الإعدادات
    </a>
</nav>
