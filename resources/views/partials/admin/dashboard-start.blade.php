@php
    $dashSubnav = $dashSubnav ?? 'home';
    $dashHeader = $dashHeader ?? ($dashSubnav === 'home' ? 'home' : 'stats');
    $dashSidebarActive = $dashSidebarActive ?? route('admin.dashboard');
@endphp

@include('partials.admin.shell-start', [
    'shellLayout' => 'dashboard',
    'shellSidebarActive' => $dashSidebarActive,
    'shellActiveHeader' => $dashHeader,
    'dashSubnav' => $dashSubnav,
])
