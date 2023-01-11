<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use function func_get_args;
use Hyde\Foundation\Concerns\HydeExtension;
use Hyde\Foundation\FileCollection;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\PageCollection;
use Hyde\Foundation\RouteCollection;
use Hyde\Hyde;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Foundation\Concerns\HydeExtension
 * @covers \Hyde\Foundation\Concerns\ManagesHydeKernel
 * @covers \Hyde\Foundation\HydeKernel
 * @covers \Hyde\Foundation\FileCollection
 * @covers \Hyde\Foundation\PageCollection
 * @covers \Hyde\Foundation\RouteCollection
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
