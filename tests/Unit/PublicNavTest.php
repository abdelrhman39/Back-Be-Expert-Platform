<?php

namespace Tests\Unit;

use App\Support\PublicNav;
use Illuminate\Http\Request;
use Tests\TestCase;

class PublicNavTest extends TestCase
{
    public function test_exact_home_path_matches_only_the_locale_root(): void
    {
        $this->bindRequestPath('ar');

        $this->assertTrue(PublicNav::isExact('/ar'));
        $this->assertFalse(PublicNav::isExact('/ar/about'));
        $this->assertTrue(PublicNav::isActive('/ar'));
    }

    public function test_nested_paths_mark_the_parent_as_active(): void
    {
        $this->bindRequestPath('ar/apply/client');

        $this->assertTrue(PublicNav::isActive('/ar/apply', [
            ['url' => '/ar/apply/client'],
        ]));
        $this->assertTrue(PublicNav::isExact('/ar/apply/client'));
        $this->assertFalse(PublicNav::isExact('/ar/apply/company'));
    }

    public function test_hash_and_empty_urls_are_never_current(): void
    {
        $this->bindRequestPath('ar');

        $this->assertFalse(PublicNav::isActive('#'));
        $this->assertFalse(PublicNav::isActive('javascript:void(0)'));
        $this->assertFalse(PublicNav::isActive(null));
    }

    private function bindRequestPath(string $path): void
    {
        $request = Request::create('http://localhost/'.$path);
        $this->app->instance('request', $request);
    }
}
