<?php

declare(strict_types=1);

namespace Hyde\Testing;

use function config;
use function file_get_contents;

use Hyde\Facades\Features;
use Hyde\Hyde;

use function Hyde\normalize_newlines;

use Illuminate\View\Component;
use LaravelZero\Framework\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use ResetsApplication;
    use CreatesTemporaryFiles;
    use InteractsWithPages;

    protected static bool $booted = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! static::$booted) {
            $this->resetApplication();

            static::$booted = true;
        }
    }

    protected function tearDown(): void
    {
        $this->cleanUpFilesystem();

        if (method_exists(Component::class, 'flushCache')) {
            /** Until https://github.com/laravel/framework/pull/44648 makes its way into Laravel Zero, we need to clear the view cache ourselves */
            Component::flushCache();
            Component::forgetComponentsResolver();
            Component::forgetFactory();
        }

        Features::clearMockedInstances();

        parent::tearDown();
    }

    protected function assertFileEqualsString(string $string, string $path, bool $strict = false): void
    {
        if ($strict) {
            $this->assertSame($string, file_get_contents(Hyde::path($path)));
        } else {
            $this->assertEquals(normalize_newlines($string), normalize_newlines(file_get_contents(Hyde::path($path))));
        }
    }

    /**
     * Disable the throwing of exceptions on console commands for the duration of the test.
     *
     * Note that this only affects commands using the {@see \Hyde\Console\Concerns\Command::safeHandle()} method.
     */
    protected function throwOnConsoleException(bool $throw = true): void
    {
        config(['app.throw_on_console_exception' => $throw]);
    }
}
