<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas;

use Hyde\Framework\Actions\Constructors\FindsNavigationDataForPage;
use Hyde\Framework\Actions\Constructors\FindsTitleForPage;
use Hyde\Framework\Hyde;
use JetBrains\PhpStorm\ArrayShape;


/**
 * These are the front matter properties that are supported for Hyde Markdown pages.
 */
trait PageSchema
{
    /**
     * The title of the page used in the HTML <title> tag, among others.
     *
     * @example "Home", "About", "Blog Feed"
     * @yamlType string|optional
     */
    public string $title;

    /**
     * The settings for how the page should be presented in the navigation menu.
     * All array values are optional, as long as the array is not empty.
     *
     * @yamlType array|optional
     *
     * @example ```yaml
     * navigation:
     *   title: "Home"
     *   hidden: true
     *   priority: 1
     * ```
     */
    #[ArrayShape(['title' => 'string', 'hidden' => 'bool', 'priority' => 'int'])]
    public ?array $navigation = null;

    /**
     * The canonical URL of the page.
     *
     * @yamlType array|optional
     *
     * @example "https://example.com/about"
     */
    public ?string $canonicalUrl = null;

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
}
