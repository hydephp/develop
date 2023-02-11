<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\Services\LoadYamlConfiguration;
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
        $bootstrappers = (new ReflectionMethod($kernel, 'bootstrappers'))->invoke($kernel);

        $this->assertIsArray($bootstrappers);
        $this->assertContains(LoadYamlConfiguration::class, $bootstrappers);

        $this->assertSame([
            0 => 'LaravelZero\Framework\Bootstrap\CoreBindings',
            1 => 'LaravelZero\Framework\Bootstrap\LoadEnvironmentVariables',
            2 => 'LaravelZero\Framework\Bootstrap\LoadConfiguration',
            3 => 'Illuminate\Foundation\Bootstrap\HandleExceptions',
            4 => 'LaravelZero\Framework\Bootstrap\RegisterFacades',
            5 => 'Hyde\Foundation\Services\LoadYamlConfiguration',
            6 => 'LaravelZero\Framework\Bootstrap\RegisterProviders',
            7 => 'Illuminate\Foundation\Bootstrap\BootProviders',
        ], $bootstrappers);
    }
}
