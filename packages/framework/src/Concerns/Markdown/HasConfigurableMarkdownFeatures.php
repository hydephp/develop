<?php

namespace Hyde\Framework\Concerns\Markdown;

use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Helpers\Markdown;
use Hyde\Framework\Models\Pages\DocumentationPage;

/**
 * Allow the Markdown service to have configurable features.
 *
 * @see HasMarkdownFeatures for global feature management.
 */
trait HasConfigurableMarkdownFeatures
{
    protected array $features = [];

    public function addFeature(string $feature): self
    {
        if (! in_array($feature, $this->features)) {
            $this->features[] = $feature;
        }

        return $this;
    }

    public function removeFeature(string $feature): self
    {
        if (in_array($feature, $this->features)) {
            $this->features = array_diff($this->features, [$feature]);
        }

        return $this;
    }

    public function withTableOfContents(): self
    {
        $this->addFeature('table-of-contents');

        return $this;
    }

    public function withPermalinks(): self
    {
        $this->addFeature('permalinks');

        return $this;
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features);
    }

    public function canEnablePermalinks(): bool
    {
        if ($this->hasFeature('permalinks')) {
            return true;
        }

        if ($this->isDocumentationPage() && Markdown::hasTableOfContents()) {
            return true;
        }

        return false;
    }

    public function isDocumentationPage(): bool
    {
        return isset($this->sourceModel) && $this->sourceModel === DocumentationPage::class;
    }

    public function canEnableTorchlight(): bool
    {
        return $this->hasFeature('torchlight') ||
                    Features::hasTorchlight();
    }
}
