<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories\Concerns;

use Hyde\Framework\Factories\BlogPostDataFactory;
use Hyde\Framework\Factories\HydePageDataFactory;
use Hyde\Pages\MarkdownPost;

trait HasFactory
{
    public function constructFactoryData(PageDataFactory $factory): void
    {
        foreach ($factory->toArray() as $key => $value) {
            $this->{$key} = $value;
        }
    }

    protected function constructPageSchemas(): void
    {
        $this->constructFactoryData(new HydePageDataFactory($this->toCoreDataObject()));

        if ($this instanceof MarkdownPost) {
            $this->constructFactoryData(new BlogPostDataFactory($this->toCoreDataObject()));
        }
    }

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
}
