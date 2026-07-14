<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories\Concerns;

use Hyde\Framework\Factories\BlogPostDataFactory;
use Hyde\Framework\Factories\HydePageDataFactory;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Models\RouteKey;

trait HasFactory
{
    protected string $constructionRouteKey;

    public function toCoreDataObject(): CoreDataObject
    {
        return $this->makeCoreDataObject(
            $this->getSourcePath(),
            $this->getOutputPath(),
            $this->getRouteKey(),
        );
    }

    protected function toConstructionCoreDataObject(): CoreDataObject
    {
        // Base construction cannot safely dispatch to instance path overrides. This
        // provisional route is replaced in navigation data when the route is finalized.
        $outputPath = static::outputPath($this->identifier);

        return $this->makeCoreDataObject(
            static::sourcePath($this->identifier),
            $outputPath,
            RouteKey::fromOutputPath($outputPath)->get(),
        );
    }

    private function makeCoreDataObject(string $sourcePath, string $outputPath, string $routeKey): CoreDataObject
    {
        return new CoreDataObject(
            $this->matter,
            $this->markdown ?? false,
            static::class,
            $this->identifier,
            $sourcePath,
            $outputPath,
            $routeKey,
        );
    }

    protected function constructFactoryData(): void
    {
        $pageData = $this->toConstructionCoreDataObject();
        $this->constructionRouteKey = $pageData->routeKey;

        $this->assignFactoryData(new HydePageDataFactory($pageData));

        if ($this instanceof MarkdownPost) {
            $this->assignFactoryData(new BlogPostDataFactory($pageData));
        }
    }

    protected function synchronizeFactoryDataForResolvedRoute(string $routeKey): void
    {
        if ($routeKey === $this->constructionRouteKey) {
            return;
        }

        $pageData = $this->makeCoreDataObject(
            $this->getSourcePath(),
            $this->getOutputPath(),
            $routeKey,
        );

        $this->navigation = (new HydePageDataFactory($pageData))->toArray()['navigation'];
        $this->constructionRouteKey = $routeKey;
    }

    protected function assignFactoryData(PageDataFactory $factory): void
    {
        foreach ($factory->toArray() as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
