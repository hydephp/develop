<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Foundation\Internal\LoadYamlConfiguration;
use Illuminate\Contracts\Console\Kernel;
use Hyde\Foundation\ConsoleKernel;
use Hyde\Testing\TestCase;
use ReflectionMethod;

/**
 * @covers \Hyde\Foundation\ConsoleKernel
 */
class ConsoleKernelTest extends TestCase
{
    public function testIsInstantiable()
    {
        $this->assertInstanceOf(ConsoleKernel::class, app(ConsoleKernel::class));
    }

    public function testClassImplementsKernelInterface()
    {
        $this->assertInstanceOf(Kernel::class, app(ConsoleKernel::class));
    }

    public function testBootstrappers()
    {
        $kernel = app(ConsoleKernel::class);

        // Normally, protected code does not need to be unit tested, but since this array is so vital, we want to inspect it.
        $bootstrappers = (new ReflectionMethod($kernel, 'bootstrappers'))->invoke($kernel);

        $this->assertIsArray($bootstrappers);
        $this->assertContains(LoadYamlConfiguration::class, $bootstrappers);

        // Another assertion that is usually a no-no, testing vendor code, especially those which are marked as internal.
        // We do this here however, so we get a heads-up if the vendor code changes as that could break our code.

        $this->assertSame([
            0 => 'LaravelZero\Framework\Bootstrap\CoreBindings',
            1 => 'LaravelZero\Framework\Bootstrap\LoadEnvironmentVariables',
            2 => 'Hyde\Foundation\Internal\LoadConfiguration',
            3 => 'Illuminate\Foundation\Bootstrap\HandleExceptions',
            4 => 'LaravelZero\Framework\Bootstrap\RegisterFacades',
            5 => 'Hyde\Foundation\Internal\LoadYamlConfiguration',
            6 => 'LaravelZero\Framework\Bootstrap\RegisterProviders',
            7 => 'Illuminate\Foundation\Bootstrap\BootProviders',
        ], $bootstrappers);
    }
}
