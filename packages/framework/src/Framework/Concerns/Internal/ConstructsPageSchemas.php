<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Framework\Actions\Constructors\FindsNavigationDataForPage;
use Hyde\Framework\Actions\Constructors\FindsTitleForPage;
use Hyde\Framework\Factories\BlogPostFactory;
use Hyde\Hyde;
use Hyde\Pages\MarkdownPost;

trait ConstructsPageSchemas
{
    protected function constructPageSchemas(): void
    {
        $this->constructPageSchema();

        if ($this instanceof MarkdownPost) {
            $this->constructFactoryData(new BlogPostFactory($this->matter, $this->markdown));
        }
    }

    protected function constructPageSchema(): void
    {
        $this->title = FindsTitleForPage::run($this);
        $this->navigation = FindsNavigationDataForPage::run($this);
        $this->canonicalUrl = $this->makeCanonicalUrl();
    }

    protected function makeCanonicalUrl(): ?string
    {
        if (! empty($this->matter('canonicalUrl'))) {
            return $this->matter('canonicalUrl');
        }

        if (Hyde::hasSiteUrl() && ! empty($this->identifier)) {
            return Hyde::url($this->getRoute()->getOutputPath());
        }

        return null;
    }
}
