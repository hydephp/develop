<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Hyde\Foundation\FileCollection;
use Hyde\Foundation\PageCollection;
use Hyde\Foundation\RouteCollection;

/**
 * When creating a HydePHP extension, you should create a class that extends this one.
 *
 * After registering your implementation with the HydeKernel (usually in a service provider),
 * Hyde will be able to use the information within to integrate your plugin, and to allow you to
 * hook into various parts of the internal application lifecycle, and through that, all aspects of Hyde.
 *
 * Before creating your extension, it will certainly be helpful if you first become familiar
 * with the basic internal architecture of HydePHP, as well as how the auto-discovery system functions.
 *
 * @link https://hydephp.com/docs/master/architecture-concepts
 *
 * It's important that your class is registered before the HydeKernel boots.
 * An excellent place for this is the 'register' method of your extensions service provider,
 * where you call the 'registerExtension' method of the HydeKernel singleton instance,
 * which you can access via the Hyde\Hyde facade, or via the service container.
 *
 * @example `$this->app->make(HydeKernel::class)->registerExtension(MyExtension::class);`
 *
 * @see \Hyde\Framework\Testing\Feature\HydeExtensionTest
 */
abstract class HydeExtension
{
    /**
     * If your extension adds new page classes, you should register them here.
     *
     * Hyde will then automatically discover source files for the new page class,
     * generate routes, and compile the pages during the build process.
     *
     * @return array<class-string<\Hyde\Pages\Concerns\HydePage>>
     */
    public static function getPageClasses(): array
    {
        return [];
    }

    /**
     * If your extension needs to hook into the file discovery process,
     * you can configure the following handler method. It will be called
     * at the end of the file discovery process. The collection instance
     * will be injected, so that you can interact with it directly.
     */
    public static function discoverFiles(FileCollection $collection): void
    {
        //
    }

    /**
     * If your extension needs to hook into the page discovery process,
     * you can configure the following handler method. It will be called
     * at the end of the page discovery process. The collection instance
     * will be injected, so that you can interact with it directly.
     */
    public static function discoverPages(PageCollection $collection): void
    {
        //
    }

    /**
     * If your extension needs to hook into the route discovery process,
     * you can configure the following handler method. It will be called
     * at the end of the route discovery process. The collection instance
     * will be injected, so that you can interact with it directly.
     */
    public static function discoverRoutes(RouteCollection $collection): void
    {
        //
    }
}
