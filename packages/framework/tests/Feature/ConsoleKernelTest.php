<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use LaravelZero\Framework\Kernel as LaravelZeroKernel;
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

    public function testLaravelZeroBootstrappersHaveNotChanged()
    {
        $bootstrappers = (new ReflectionMethod(app(LaravelZeroKernel::class), 'bootstrappers'))->invoke(app(LaravelZeroKernel::class));

        $this->assertSame([
            \LaravelZero\Framework\Bootstrap\CoreBindings::class,
            \LaravelZero\Framework\Bootstrap\LoadEnvironmentVariables::class,
            \LaravelZero\Framework\Bootstrap\LoadConfiguration::class,
            \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
            \LaravelZero\Framework\Bootstrap\RegisterFacades::class,
            \LaravelZero\Framework\Bootstrap\RegisterProviders::class,
            \Illuminate\Foundation\Bootstrap\BootProviders::class,
        ], $bootstrappers);
    }

    public function testHydeBootstrapperInjections()
    {
        $bootstrappers = (new ReflectionMethod(app(ConsoleKernel::class), 'bootstrappers'))->invoke(app(ConsoleKernel::class));

        $this->assertIsArray($bootstrappers);
        $this->assertContains(LoadYamlConfiguration::class, $bootstrappers);
        $this->assertSame(range(0, count($bootstrappers) - 1), array_keys($bootstrappers));

        $this->assertSame([
            \LaravelZero\Framework\Bootstrap\CoreBindings::class,
            \LaravelZero\Framework\Bootstrap\LoadEnvironmentVariables::class,
            \Hyde\Foundation\Internal\LoadConfiguration::class,
            \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
            \LaravelZero\Framework\Bootstrap\RegisterFacades::class,
            \Hyde\Foundation\Internal\LoadYamlConfiguration::class,
            \LaravelZero\Framework\Bootstrap\RegisterProviders::class,
            \Illuminate\Foundation\Bootstrap\BootProviders::class,
        ], $bootstrappers);
    }
}
