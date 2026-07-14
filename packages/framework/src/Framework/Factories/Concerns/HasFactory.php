<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories\Concerns;

use Hyde\Framework\Factories\BlogPostDataFactory;
use Hyde\Framework\Factories\HydePageDataFactory;
use Hyde\Pages\MarkdownPost;

trait HasFactory
{
    public function toCoreDataObject(): CoreDataObject
    {
        return new CoreDataObject(
            $this->matter,
            $this->markdown ?? false,
            static::class,
            $this->identifier,
            $this->getSourcePath(),
            $this->getOutputPath(),
            $this->getRouteKey(),
        );
    }

    protected function constructFactoryData(): void
    {
        $this->assignFactoryData(new HydePageDataFactory($this->toCoreDataObject()));

        if ($this instanceof MarkdownPost) {
            $this->assignFactoryData(new BlogPostDataFactory($this->toCoreDataObject()));
        }
    }

    protected function assignFactoryData(PageDataFactory $factory): void
    {
        foreach ($factory->toArray() as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
