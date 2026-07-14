<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories\Concerns;

use Hyde\Framework\Factories\BlogPostDataFactory;
use Hyde\Framework\Factories\HydePageDataFactory;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Models\RouteKey;

trait HasFactory
{
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

        $this->assignFactoryData(new HydePageDataFactory($pageData));

        if ($this instanceof MarkdownPost) {
            $this->assignFactoryData(new BlogPostDataFactory($pageData));
        }
    }

    protected function assignFactoryData(PageDataFactory $factory): void
    {
        foreach ($factory->toArray() as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
