<?php

namespace Tests\Unit;

use App\Support\Utf8Text;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class Utf8TextTest extends TestCase
{
    #[Test]
    public function it_repairs_mojibake_arabic(): void
    {
        $arabic = 'مركز التعلم المستمر';
        $mojibake = mb_convert_encoding($arabic, 'UTF-8', 'ISO-8859-1');

        $this->assertTrue(Utf8Text::looksMojibake($mojibake));
        $this->assertSame($arabic, Utf8Text::repair($mojibake));
    }

    #[Test]
    public function it_leaves_valid_arabic_unchanged(): void
    {
        $arabic = 'الجامعة العربية المفتوحة';

        $this->assertFalse(Utf8Text::looksMojibake($arabic));
        $this->assertSame($arabic, Utf8Text::repair($arabic));
    }

    #[Test]
    public function it_interpolates_platform_tokens(): void
    {
        $text = '{platform_name} — {platform_org}';

        $this->assertSame(
            'مركز التعلم المستمر — الجامعة العربية المفتوحة',
            Utf8Text::interpolate($text, 'ar'),
        );
        $this->assertSame(
            'Continuing Learning Center — Arab Open University',
            Utf8Text::interpolate($text, 'en'),
        );
    }

    #[Test]
    public function it_interpolates_nested_arrays(): void
    {
        $data = Utf8Text::deep([
            'title' => '{platform_name}',
            'items' => [
                ['body' => 'تابع {platform_org}'],
            ],
        ], 'ar');

        $this->assertSame('مركز التعلم المستمر', $data['title']);
        $this->assertSame('تابع الجامعة العربية المفتوحة', $data['items'][0]['body']);
    }
}
