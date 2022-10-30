<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Framework\Factories\BlogPostDataFactory;
use Hyde\Framework\Factories\Concerns\CoreDataObject;
use Hyde\Framework\Factories\HydePageDataFactory;
use Hyde\Pages\MarkdownPost;

/**
 * @deprecated Refactor to use factories instead.
 */
trait ConstructsPageSchemas
{
    protected function constructPageSchemas(): void
    {
        $pageData = new CoreDataObject(
            $this->matter,
            $this->markdown ?? false,
            static::class,
            $this->identifier,
            $this->getSourcePath(),
            $this->getOutputPath(),
            $this->getRouteKey(),
        );

        $this->constructFactoryData(new HydePageDataFactory($pageData));

        if ($this instanceof MarkdownPost) {
            $this->constructFactoryData(new BlogPostDataFactory($this->matter, $this->markdown));
        }
    }
}
