<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Framework\Factories\BlogPostDataFactory;
use Hyde\Framework\Factories\HydePageDataFactory;
use Hyde\Pages\MarkdownPost;

/**
 * @deprecated Refactor to use factories instead.
 */
trait ConstructsPageSchemas
{
    protected function constructPageSchemas(): void
    {
        $this->constructFactoryData(new HydePageDataFactory(
            $this->matter,
            $this->markdown ?? false,
            $this::class,
            $this->identifier,
            $this->getOutputPath(),
            $this->routeKey)
        );

        if ($this instanceof MarkdownPost) {
            $this->constructFactoryData(new BlogPostDataFactory($this->matter, $this->markdown));
        }
    }
}
