<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Hyde\Facades\Config;
use Hyde\Foundation\Facades\Routes;
use Hyde\Pages\Concerns\BaseMarkdownPage;
use Hyde\Support\Models\Route;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersion;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

use function trim;
use function sprintf;
use function Hyde\unslash;
use function basename;

/**
 * Page class for documentation pages.
 *
 * Documentation pages are stored in the _docs directory and using the .md extension.
 * The Markdown will be compiled to HTML using the documentation page layout to the _site/docs/ directory.
 *
 * When documentation versioning is enabled through the `docs.versions` configuration, each page belongs to the
 * version subdirectory it is stored in (like `_docs/1.x`), and is compiled to a matching subdirectory of the
 * output directory (like `_site/docs/1.x`). Source files stored outside the version directories are then
 * not part of any version, and are therefore not discovered as documentation pages.
 *
 * @see https://hydephp.com/docs/2.x/documentation-pages
 */
class DocumentationPage extends BaseMarkdownPage
{
    public static string $sourceDirectory = '_docs';
    public static string $outputDirectory = 'docs';
    public static string $template = 'hyde::layouts/docs';

    /**
     * Get the route for the documentation index page, if it exists.
     *
     * When documentation versioning is enabled, this is the generated redirect page at the documentation root.
     *
     * @see \Hyde\Framework\Features\Documentation\Versioning\DocumentationVersion::home() To get the index page of a specific version.
     */
    public static function home(): ?Route
    {
        return Routes::find(static::homeRouteName());
    }

    /**
     * Get the route key for the documentation index page, for example `docs/index`.
     *
     * This is always the documentation root, regardless of whether documentation versioning is enabled.
     *
     * @see \Hyde\Framework\Features\Documentation\Versioning\DocumentationVersion::homeRouteName() To get the route key of a specific version's index page.
     */
    public static function homeRouteName(): string
    {
        return static::baseRouteKey().'/index';
    }

    /**
     * Get the documentation version this page belongs to, or null if documentation versioning is disabled.
     */
    public function getDocumentationVersion(): ?DocumentationVersion
    {
        return DocumentationVersions::fromIdentifier($this->identifier);
    }

    /** @see https://hydephp.com/docs/2.x/documentation-pages#automatic-edit-page-button */
    public function getOnlineSourcePath(): string|false
    {
        if (Config::getNullableString('docs.source_file_location_base') === null) {
            return false;
        }

        return sprintf('%s/%s.md', trim(Config::getString('docs.source_file_location_base'), '/'), $this->identifier);
    }

    /**
     * Get the route key for the page.
     *
     * If flattened outputs are enabled, this will use the identifier basename so nested pages are flattened.
     * Pages belonging to a documentation version keep the version prefix, so only the structure within the version is flattened.
     */
    public function getRouteKey(): string
    {
        return Config::getBool('docs.flattened_output_paths', true)
            ? unslash(static::outputDirectory().'/'.$this->versionedBasename(basename(parent::getRouteKey())))
            : parent::getRouteKey();
    }

    /**
     * Get the path where the compiled page will be saved.
     *
     * If flattened outputs are enabled, this will use the identifier basename so nested pages are flattened.
     * Pages belonging to a documentation version keep the version prefix, so only the structure within the version is flattened.
     */
    public function getOutputPath(): string
    {
        return Config::getBool('docs.flattened_output_paths', true)
            ? static::outputPath($this->versionedBasename(basename($this->identifier)))
            : parent::getOutputPath();
    }

    /**
     * Prefix a flattened page basename with the page's version name, if the page belongs to a version.
     */
    protected function versionedBasename(string $basename): string
    {
        $version = $this->getDocumentationVersion();

        return $version === null ? $basename : "$version->name/$basename";
    }
}
