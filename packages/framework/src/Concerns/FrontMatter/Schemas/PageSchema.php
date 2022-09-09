<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas;

use JetBrains\PhpStorm\ArrayShape;

/**
 * These are the front matter properties that are supported for all* Hyde pages.
 * *Support for front matter in Blade pages is experimental.
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
     * @yamlType string|optional
     *
     * @example "https://example.com/about"
     */
    public ?string $canonicalUrl = null;
}
