<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Cache;

#[\PHPUnit\Framework\Attributes\CoversNothing]
class CacheClearCommandTest extends TestCase
{
    public function testCacheClearCommand()
    {
        Cache::remember('foo', 60, fn () => 'bar');

        $this->assertSame('bar', Cache::get('foo'));

        $this->artisan('cache:clear')
            ->expectsOutputToContain('Application cache cleared successfully.')
            ->assertExitCode(0);

        $this->assertNull(Cache::get('foo'));
    }
}
