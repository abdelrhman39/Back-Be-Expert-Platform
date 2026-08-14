<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('لوحة التحكم')]
class extends Component
{
    public array $config = [];

    public function mount(): void
    {
        $name = request()->route()?->getName() ?? '';
        $key = str($name)->after('admin.')->toString();
        $this->config = config('admin.coming_soon.'.$key, [
            'title' => 'قريباً',
            'description' => 'هذا القسم قيد التطوير.',
            'legacy' => null,
        ]);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => url()->current(),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => $config['title'] ?? 'قريباً'],
    ],
])

@include('partials.admin.coming-soon', [
    'title' => $config['title'] ?? 'قريباً',
    'description' => $config['description'] ?? null,
    'legacy' => $config['legacy'] ?? null,
])

@include('partials.admin.shell-end')
