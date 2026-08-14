<?php

namespace Database\Seeders;

use App\Models\CatalogCourse;
use App\Models\CatalogCourseLesson;
use App\Models\CatalogCourseModule;
use Illuminate\Database\Seeder;

class CourseContentDemoSeeder extends Seeder
{
    public function run(): void
    {
        $courses = CatalogCourse::query()->limit(5)->get();

        foreach ($courses as $index => $course) {
            if (CatalogCourseModule::query()->where('course_id', $course->id)->exists()) {
                continue;
            }

            $module1 = CatalogCourseModule::query()->create([
                'course_id' => $course->id,
                'title_ar' => 'مقدمة في الدورة',
                'sort_order' => 1,
            ]);

            CatalogCourseLesson::query()->create([
                'module_id' => $module1->id,
                'title_ar' => 'مرحباً بك في الدورة',
                'type' => 'html',
                'body_ar' => '<p>في هذا الدرس ستتعرف على أهداف الدورة ومتطلباتها وطريقة المتابعة.</p><ul><li>فهم محتوى البرنامج</li><li>التعرف على أدوات التعلم</li><li>خطة الإنجاز الأسبوعية</li></ul>',
                'duration_minutes' => 10,
                'sort_order' => 1,
            ]);

            CatalogCourseLesson::query()->create([
                'module_id' => $module1->id,
                'title_ar' => 'فيديو تعريفي',
                'type' => 'video',
                'external_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'duration_minutes' => 5,
                'sort_order' => 2,
            ]);

            $module2 = CatalogCourseModule::query()->create([
                'course_id' => $course->id,
                'title_ar' => 'الوحدة الأولى — المفاهيم الأساسية',
                'sort_order' => 2,
            ]);

            CatalogCourseLesson::query()->create([
                'module_id' => $module2->id,
                'title_ar' => 'قراءة: المفاهيم الأساسية',
                'type' => 'document',
                'body_ar' => '<p>محتوى تعليمي نصي يشرح المفاهيم الأساسية للموضوع مع أمثلة تطبيقية من بيئة العمل.</p><p>بعد الانتهاء من القراءة، اضغط «تم إكمال الدرس» للمتابعة.</p>',
                'duration_minutes' => 20,
                'sort_order' => 1,
            ]);

            CatalogCourseLesson::query()->create([
                'module_id' => $module2->id,
                'title_ar' => 'ملخص الوحدة',
                'type' => 'html',
                'body_ar' => '<p>راجع النقاط الرئيسية التي تعلمتها في هذه الوحدة قبل الانتقال للتطبيق العملي.</p>',
                'duration_minutes' => 8,
                'sort_order' => 2,
            ]);
        }
    }
}
