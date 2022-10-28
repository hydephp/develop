<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Framework\Actions\Constructors\FindsNavigationDataForPage;
use Hyde\Framework\Actions\Constructors\FindsTitleForPage;
use Hyde\Framework\Factories\BlogPostDataFactory;
use Hyde\Framework\Factories\HydePageDataFactory;
use Hyde\Hyde;
use Hyde\Pages\MarkdownPost;

/**
 * @deprecated Refactor to use factories instead.
 */
trait ConstructsPageSchemas
{
    protected function constructPageSchemas(): void
    {
        $this->constructPageSchema();

        if ($this instanceof MarkdownPost) {
            $this->constructFactoryData(new BlogPostDataFactory($this->matter, $this->markdown));
        }
    }

    protected function constructPageSchema(): void
    {
        $this->constructFactoryData(new HydePageDataFactory($this->matter, $this->markdown ?? false, $this->identifier, $this->getOutputPath(), $this));
    }
}
