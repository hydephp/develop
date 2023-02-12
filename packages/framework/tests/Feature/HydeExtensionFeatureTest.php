<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use BadMethodCallException;
use Hyde\Foundation\Concerns\HydeExtension;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\FileCollection;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Hyde;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Testing\TestCase;
use InvalidArgumentException;
use stdClass;
use function app;
use function func_get_args;

/**
 * @covers \Hyde\Foundation\Concerns\HydeExtension
 * @covers \Hyde\Foundation\Concerns\ManagesHydeKernel
 * @covers \Hyde\Foundation\HydeKernel
 * @covers \Hyde\Foundation\Kernel\FileCollection
 * @covers \Hyde\Foundation\Kernel\PageCollection
 * @covers \Hyde\Foundation\Kernel\RouteCollection
 */
class HydeExtensionFeatureTest extends TestCase
{
    protected HydeKernel $kernel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kernel = HydeKernel::getInstance();
    }

    public function testBaseClassGetPageClasses()
    {
        $this->assertSame([], HydeExtension::getPageClasses());
    }

    public function testBaseClassDiscoveryHandlers()
    {
        HydeExtension::discoverFiles(Hyde::files());
        HydeExtension::discoverPages(Hyde::pages());
        HydeExtension::discoverRoutes(Hyde::routes());

        $this->markTestSuccessful();
    }

    public function testCanRegisterNewExtension()
    {
        HydeKernel::setInstance(new HydeKernel());

        $this->kernel = HydeKernel::getInstance();
        $this->kernel->registerExtension(HydeTestExtension::class);

        $this->assertSame([HydeTestExtension::class], $this->kernel->getRegisteredExtensions());
    }

    public function testHandlerMethodsAreCalledByDiscovery()
    {
        $this->kernel->registerExtension(HydeTestExtension::class);

        $this->assertSame([], HydeTestExtension::$callCache);

        $this->kernel->boot();

        $this->assertSame(['files', 'pages', 'routes'], HydeTestExtension::$callCache);

        HydeTestExtension::$callCache = [];
    }

    public function testFileHandlerDependencyInjection()
    {
        $this->kernel->registerExtension(SpyableTestExtension::class);
        $this->kernel->boot();

        $this->assertInstanceOf(FileCollection::class, ...SpyableTestExtension::getCalled('files'));
    }

    public function testPageHandlerDependencyInjection()
    {
        $this->kernel->registerExtension(SpyableTestExtension::class);
        $this->kernel->boot();

        $this->assertInstanceOf(PageCollection::class, ...SpyableTestExtension::getCalled('pages'));
    }

    public function testRouteHandlerDependencyInjection()
    {
        $this->kernel->registerExtension(SpyableTestExtension::class);
        $this->kernel->boot();

        $this->assertInstanceOf(RouteCollection::class, ...SpyableTestExtension::getCalled('routes'));
    }

    public function test_register_extension_method_throws_exception_when_kernel_is_already_booted()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot register an extension after the Kernel has been booted.');

        app(HydeKernel::class)->boot();
        app(HydeKernel::class)->registerExtension(HydeTestExtension::class);
    }

    public function test_register_extension_method_only_accepts_instances_of_hyde_extension()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The specified class must extend the HydeExtension class.');

        app(HydeKernel::class)->registerExtension(stdClass::class);
    }

    protected function markTestSuccessful(): void
    {
        $this->assertTrue(true);
    }
}

class HydeTestExtension extends HydeExtension
{
    // An easy way to assert the handlers are called.
    public static array $callCache = [];

    public static function getPageClasses(): array
    {
        return [
            HydeExtensionTestPage::class,
        ];
    }

    public static function discoverFiles(FileCollection $collection): void
    {
        static::$callCache[] = 'files';
    }

    public static function discoverPages(PageCollection $collection): void
    {
        static::$callCache[] = 'pages';
    }

    public static function discoverRoutes(RouteCollection $collection): void
    {
        static::$callCache[] = 'routes';
    }
}

class SpyableTestExtension extends HydeExtension
{
    private static array $callCache = [];

    public static function discoverFiles(FileCollection $collection): void
    {
        self::$callCache['files'] = func_get_args();
    }

    public static function discoverPages(PageCollection $collection): void
    {
        self::$callCache['pages'] = func_get_args();
    }

    public static function discoverRoutes(RouteCollection $collection): void
    {
        self::$callCache['routes'] = func_get_args();
    }

    public static function getCalled(string $method): array
    {
        return self::$callCache[$method];
    }
}

class HydeExtensionTestPage extends HydePage
{
    public function compile(): string
    {
        return '';
    }
}
