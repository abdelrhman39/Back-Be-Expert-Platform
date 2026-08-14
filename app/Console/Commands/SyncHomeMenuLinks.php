<?php

namespace App\Console\Commands;

use App\Models\CmsMenu;
use App\Models\CmsMenuItem;
use App\Services\CmsMenuService;
use Illuminate\Console\Command;

class SyncHomeMenuLinks extends Command
{
    protected $signature = 'cms:sync-home-menu';

    protected $description = 'Update header/footer program menu links to homepage sections';

    public function handle(CmsMenuService $menus): int
    {
        $header = CmsMenu::query()->where('key', 'header_main')->first();

        if ($header) {
            $programs = CmsMenuItem::query()
                ->where('menu_id', $header->id)
                ->where('label_ar', 'البرامج التدريبية')
                ->first();

            if ($programs) {
                $this->syncProgramChildren($header->id, $programs->id, [
                    'الشهادات الاحترافية' => '/ar#section-certificates',
                    'الدبلومات' => '/ar#section-diplomas',
                    'الزمالات المهنية' => '/ar#section-fellowships',
                    'برنامج مهارات' => '/ar#section-mahara',
                ], 31);
            }
        }

        $footer = CmsMenu::query()->where('key', 'footer_programs')->first();

        if ($footer) {
            $this->syncProgramChildren($footer->id, null, [
                'الشهادات الاحترافية' => '/ar#section-certificates',
                'الدبلومات' => '/ar#section-diplomas',
                'الزمالات المهنية' => '/ar#section-fellowships',
                'برنامج مهارات' => '/ar#section-mahara',
            ], 10);
        }

        $menus->forgetCache('header_main');
        $menus->forgetCache('footer_programs');

        $this->info('Homepage menu links synced.');

        return self::SUCCESS;
    }

    /** @param  array<string, string>  $links */
    protected function syncProgramChildren(int $menuId, ?int $parentId, array $links, int $startOrder): void
    {
        $order = $startOrder;

        foreach ($links as $label => $url) {
            $query = CmsMenuItem::query()
                ->where('menu_id', $menuId)
                ->where('label_ar', $label);

            if ($parentId) {
                $query->where('parent_id', $parentId);
            }

            $item = $query->first();

            if ($item) {
                $item->update([
                    'link_type' => 'url',
                    'url' => $url,
                    'sort_order' => $order,
                ]);
            } elseif ($parentId) {
                CmsMenuItem::query()->create([
                    'menu_id' => $menuId,
                    'parent_id' => $parentId,
                    'sort_order' => $order,
                    'label_ar' => $label,
                    'link_type' => 'url',
                    'url' => $url,
                    'is_active' => true,
                ]);
            }

            $order += 10;
        }
    }
}
