<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Hyde\Foundation\HydeKernel;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Foundation\Kernel\RouteCollection;
use Illuminate\Contracts\Support\Arrayable;

/**
 * A trait to mock kernel features for testing.
 *
 * @property \Hyde\Testing\TestKernel $kernel
 */
trait MocksKernelFeatures
{
    /**
     * Create a new mock kernel with the given pages added to its routes.
     *
     * @param  array<\Hyde\Pages\Concerns\HydePage>  $pages
     * @return $this
     */
    protected function withPages(array $pages): static
    {
        $this->setupTestKernel();

        $this->kernel->setRoutes(collect($pages)->map(fn (HydePage $page) => $page->getRoute()));

        return $this;
    }

    protected function setupTestKernel(): void
    {
        $this->kernel = new TestKernel();

        HydeKernel::setInstance($this->kernel);
    }
}

class TestKernel extends HydeKernel
{
    protected ?RouteCollection $mockRoutes = null;

    public function setRoutes(Arrayable $routes): void
    {
        $this->mockRoutes = RouteCollection::make($routes);
    }

    /** @return \Hyde\Foundation\Kernel\RouteCollection<string, \Hyde\Support\Models\Route> */
    public function routes(): RouteCollection
    {
        return $this->mockRoutes ?? parent::routes();
    }
}
