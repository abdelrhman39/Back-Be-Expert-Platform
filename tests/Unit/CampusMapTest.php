<?php

namespace Tests\Unit;

use App\Support\CampusMap;
use Tests\TestCase;

class CampusMapTest extends TestCase
{
    public function test_embed_url_points_to_arab_open_university_saudi_not_hail(): void
    {
        $url = CampusMap::embedUrl();

        $this->assertStringContainsString('google.com/maps', $url);
        $this->assertStringContainsString('output=embed', $url);
        $this->assertStringContainsString(rawurlencode('الجامعة العربية المفتوحة السعودية'), $url);
        $this->assertFalse(CampusMap::isLegacyHailEmbed($url));
        $this->assertTrue(CampusMap::isLegacyHailEmbed('https://www.google.com/maps/embed?pb=!1m14!2d41.699758!3d27.564384'));
    }
}
