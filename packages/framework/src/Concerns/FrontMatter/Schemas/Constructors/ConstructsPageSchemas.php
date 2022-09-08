<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas\Constructors;

use Hyde\Framework\Actions\Constructors\FindsNavigationDataForPage;
use Hyde\Framework\Actions\Constructors\FindsTitleForPage;
use Hyde\Framework\Concerns\FrontMatter\Schemas\PageSchema;
use Hyde\Framework\Hyde;

trait ConstructsPageSchemas
{
    protected function constructPageSchemas(): void
    {
        if ($this->usesSchema(PageSchema::class)) {
            $this->constructPageSchema();
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
            return $this->getRoute()->getQualifiedUrl();
        }

        return null;
    }

    protected function usesSchema(string $schema): bool
    {
        return in_array($schema, class_uses_recursive($this));
    }
}
