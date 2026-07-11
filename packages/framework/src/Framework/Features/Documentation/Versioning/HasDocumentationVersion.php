<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Documentation\Versioning;

/**
 * Interface for pages that may belong to a documentation version.
 *
 * This allows version-aware features like the sidebar, search, and version switcher to resolve
 * the version of the page being rendered, regardless of whether it is a documentation page
 * or a dynamically generated page like the documentation search page.
 */
interface HasDocumentationVersion
{
    /**
     * Get the documentation version this page belongs to, or null if it does not belong to one.
     */
    public function getDocumentationVersion(): ?DocumentationVersion;
}
