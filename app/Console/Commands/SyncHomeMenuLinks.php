<?php

namespace App\Console\Commands;

use App\Models\CmsMenu;
use App\Models\CmsMenuItem;
use App\Services\CmsMenuService;
use Illuminate\Console\Command;

class SyncHomeMenuLinks extends Command
{
    protected $signature = 'cms:sync-home-menu';

    protected $description = 'Point header/footer program menu links to catalog listing pages';

    public function handle(CmsMenuService $menus): int
    {
        $header = CmsMenu::query()->where('key', 'header_main')->first();

        if ($header) {
            $programs = CmsMenuItem::query()
                ->where('menu_id', $header->id)
                ->where('label_ar', 'البرامج التدريبية')
                ->first();

            if ($programs) {
                $programs->update([
                    'link_type' => 'route',
                    'route_name' => 'courses.index',
                    'url' => null,
                    'label_en' => $programs->label_en ?: 'Training programs',
                ]);

                $this->syncProgramChildren($header->id, $programs->id, [
                    'الشهادات الاحترافية' => ['courses.certificates', 'Professional certificates'],
                    'الدبلومات' => ['courses.diplomas', 'Diplomas'],
                    'الزمالات المهنية' => ['fellowships.index', 'Professional fellowships'],
                ], 31);
            }

            CmsMenuItem::query()
                ->where('menu_id', $header->id)
                ->whereNull('parent_id')
                ->whereIn('label_ar', ['تسجيل الطلبات', 'التقديم والتسجيل'])
                ->update([
                    'label_ar' => 'التقديم والتسجيل',
                    'label_en' => 'Apply and register',
                ]);
        }

        $footer = CmsMenu::query()->where('key', 'footer_programs')->first();

        if ($footer) {
            $this->syncProgramChildren($footer->id, null, [
                'الشهادات الاحترافية' => ['courses.certificates', 'Professional certificates'],
                'الدبلومات' => ['courses.diplomas', 'Diplomas'],
                'الزمالات المهنية' => ['fellowships.index', 'Professional fellowships'],
            ], 10);
        }

        CmsMenuItem::query()->where('label_ar', 'برنامج مهارات')->delete();
        CmsMenuItem::query()
            ->where(function ($query) {
                $query->where('label_ar', 'like', 'برنامج وعد%')
                    ->orWhere('url', 'like', '%/apply/employee%')
                    ->orWhere('url', 'like', '%/apply/job_seeker%')
                    ->orWhere('url', 'like', '%/apply/job-seeker%');
            })
            ->delete();

        $menus->forgetCache('header_main');
        $menus->forgetCache('footer_programs');

        $this->info('Program menu links now point to catalog pages.');

        return self::SUCCESS;
    }

    protected function upsertMenuItem(
        int $menuId,
        string $labelAr,
        string $routeName,
        string $labelEn,
        int $sortOrder,
        ?int $parentId = null,
    ): void {
        $matches = CmsMenuItem::query()
            ->where('menu_id', $menuId)
            ->where('label_ar', $labelAr)
            ->orderBy('id')
            ->get();

        $item = $matches->shift();
        $payload = [
            'parent_id' => $parentId,
            'link_type' => 'route',
            'route_name' => $routeName,
            'url' => null,
            'sort_order' => $sortOrder,
            'label_en' => $labelEn,
            'is_active' => true,
        ];

        if ($item) {
            $item->update($payload);
        } else {
            CmsMenuItem::query()->create(array_merge($payload, [
                'menu_id' => $menuId,
                'label_ar' => $labelAr,
            ]));
        }

        $matches->each->delete();
    }

    /** @param  array<string, array{0: string, 1: string}>  $links */
    protected function syncProgramChildren(int $menuId, ?int $parentId, array $links, int $startOrder): void
    {
        $order = $startOrder;

        foreach ($links as $label => [$routeName, $labelEn]) {
            $this->upsertMenuItem($menuId, $label, $routeName, $labelEn, $order, $parentId);
            $order += 10;
        }
    }
}
