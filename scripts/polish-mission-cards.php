<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (! Schema::hasTable('cms_page_translations')) {
    echo "No cms_page_translations table\n";
    exit(0);
}

$vision = 'أن يكون مركز التعلم المستمر المرجع الرائد إقليميًا في تقديم التعليم الاحترافي، وبوابة التحول المعرفي التي تمكّن الأفراد والمؤسسات من اكتساب مهارات المستقبل وفق أعلى المعايير الأكاديمية والمهنية.';
$goals = 'يهدف مركز التعلم المستمر إلى تقديم برامج تعليمية احترافية مواكبة لسوق العمل تنمّي المهارات التطبيقية وتدعم التطور المهني، من خلال محتوى عالي الجودة وتقنيات تعليم حديثة.';

$updated = 0;

foreach (DB::table('cms_page_translations')->get() as $row) {
    if (! is_string($row->blocks) || $row->blocks === '') {
        continue;
    }

    $blocks = json_decode($row->blocks, true);
    if (! is_array($blocks)) {
        continue;
    }

    $changed = false;

    foreach ($blocks as &$block) {
        if (($block['id'] ?? '') !== 'mission_vision_goals' && ($block['type'] ?? '') !== 'cards_grid') {
            continue;
        }

        if (($block['id'] ?? '') === 'mission_vision_goals') {
            $block['data']['title'] = $block['data']['title'] ?? 'من نحن';
            $block['data']['lead'] = $block['data']['lead'] ?? 'رسالة ورؤية وأهداف مركز التعلم المستمر في تطوير المهارات وبناء القدرات.';
        }

        $items = $block['data']['items'] ?? [];
        foreach ($items as $i => $item) {
            $body = (string) ($item['body'] ?? '');
            $body = str_replace(
                [
                    'منصة مركز التعلم المستمر',
                    'أن تكون مركز التعلم المستمر',
                    'تهدف مركز التعلم المستمر',
                    'أن تكون منصة مركز التعلم المستمر',
                    'تهدف منصة مركز التعلم المستمر',
                ],
                [
                    'مركز التعلم المستمر',
                    'أن يكون مركز التعلم المستمر',
                    'يهدف مركز التعلم المستمر',
                    'أن يكون مركز التعلم المستمر',
                    'يهدف مركز التعلم المستمر',
                ],
                $body
            );

            if (($item['title'] ?? '') === 'رؤيتنا' || ($item['title'] ?? '') === 'Our Vision') {
                if (str_contains($body, 'كن خبير') || str_contains($body, 'منصة ')) {
                    $body = $vision;
                }
            }

            if (($item['title'] ?? '') === 'أهدافنا' || ($item['title'] ?? '') === 'Our Goals') {
                if (str_contains($body, 'كن خبير') || str_starts_with($body, 'تهدف منصة')) {
                    $body = $goals;
                }
            }

            if ($body !== ($item['body'] ?? '')) {
                $items[$i]['body'] = $body;
                $changed = true;
            }
        }

        $block['data']['items'] = $items;
    }
    unset($block);

    if ($changed) {
        DB::table('cms_page_translations')->where('id', $row->id)->update([
            'blocks' => json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
        $updated++;
    }
}

echo "mission cards polished: {$updated}\n";
