<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Documentation\Versioning;

use Stringable;
use Hyde\Support\Models\Route;
use Hyde\Pages\DocumentationPage;
use Hyde\Foundation\Facades\Routes;

use function Hyde\unslash;

/**
 * Value object representing a documentation version registered in the `docs.versions` configuration.
 *
 * Each version corresponds to a subdirectory of the documentation source directory (for example `_docs/1.x`),
 * which is compiled to a matching subdirectory of the documentation output directory (for example `docs/1.x`).
 *
 * @see \Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions
 */
final class DocumentationVersion implements Stringable
{
    public function __construct(
        public readonly string $name,
    ) {
    }

    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * Is this the default version? The default version is linked in the main navigation,
     * and is the target of the generated documentation root redirect page.
     *
     * Which version is the default one is determined by the configuration, and not by the version itself.
     */
    public function isDefault(): bool
    {
        return DocumentationVersions::default()?->name === $this->name;
    }

    /**
     * Get the route key prefix for pages belonging to this version. For example, `docs/1.x`.
     */
    public function routeKeyPrefix(): string
    {
        return unslash(DocumentationPage::outputDirectory().'/'.$this->name);
    }

    /**
     * Get the route key for this version's index page. For example, `docs/1.x/index`.
     */
    public function homeRouteName(): string
    {
        return $this->routeKeyPrefix().'/index';
    }

    /**
     * Get the route for this version's index page, if it exists.
     */
    public function home(): ?Route
    {
        return Routes::find($this->homeRouteName());
    }
}
