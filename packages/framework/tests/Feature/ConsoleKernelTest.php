<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Foundation\Internal\LoadYamlConfiguration;
use Illuminate\Contracts\Console\Kernel;
use Hyde\Foundation\ConsoleKernel;
use Hyde\Testing\TestCase;
use ReflectionMethod;

/**
 * This test covers our custom console kernel, which is responsible for registering our custom bootstrappers.
 *
 * @covers \Hyde\Foundation\ConsoleKernel
 *
 * Our custom bootstrapping system depends on code from Laravel Zero which is marked as internal.
 * Sadly, there is no way around working with this private API. Since they may change the API
 * at any time, we have tests here to detect if their code changes, so we can catch it early.
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

        $bootstrappers = (new ReflectionMethod($kernel, 'bootstrappers'))->invoke($kernel);

        $this->assertIsArray($bootstrappers);
        $this->assertContains(LoadYamlConfiguration::class, $bootstrappers);

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
