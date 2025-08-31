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
 * Our custom bootstrapping system depends on code from Laravel Zero which is marked as internal.
 * Sadly, there is no way around working with this private API. Since they may change the API
 * at any time, we have tests here to detect if their code changes, so we can catch it early.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\ConsoleKernel::class)]
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
        $this->assertSame([
            \LaravelZero\Framework\Bootstrap\CoreBindings::class,
            \LaravelZero\Framework\Bootstrap\LoadEnvironmentVariables::class,
            \LaravelZero\Framework\Bootstrap\LoadConfiguration::class,
            \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
            \LaravelZero\Framework\Bootstrap\RegisterFacades::class,
            \LaravelZero\Framework\Bootstrap\RegisterProviders::class,
            \Illuminate\Foundation\Bootstrap\BootProviders::class,
        ], $this->getBootstrappersFromKernel(app(LaravelZeroKernel::class)));
    }

    public function testHydeBootstrapperInjections()
    {
        $bootstrappers = $this->getBootstrappersFromKernel(app(ConsoleKernel::class));

        $this->assertIsArray($bootstrappers);
        $this->assertContains(LoadYamlConfiguration::class, $bootstrappers);

        $this->assertSame([
            \LaravelZero\Framework\Bootstrap\CoreBindings::class,
            \LaravelZero\Framework\Bootstrap\LoadEnvironmentVariables::class,
            \Hyde\Foundation\Internal\LoadYamlEnvironmentVariables::class,
            \Hyde\Foundation\Internal\LoadConfiguration::class,
            \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
            \LaravelZero\Framework\Bootstrap\RegisterFacades::class,
            \Hyde\Foundation\Internal\LoadYamlConfiguration::class,
            \LaravelZero\Framework\Bootstrap\RegisterProviders::class,
            \Illuminate\Foundation\Bootstrap\BootProviders::class,
        ], $bootstrappers);
    }

    protected function getBootstrappersFromKernel(\Illuminate\Foundation\Console\Kernel $kernel): array
    {
        return (new ReflectionMethod($kernel, 'bootstrappers'))->invoke($kernel);
    }
}
