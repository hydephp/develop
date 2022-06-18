<?php

namespace Hyde\Framework\Concerns\FacadeHelpers;

use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Models\DocumentationPage;

/**
 * Provide static facade methods, and instance helpers for HydeSmartDocs.
 *
 * @see \Hyde\Framework\Services\HydeSmartDocs
 * @see \Hyde\Testing\Framework\Feature\Services\HydeSmartDocsTest
 */
trait HydeSmartDocsFacade
{
    /**
     * Create a new HydeSmartDocs instance, process, and return it.
     *
     * @param DocumentationPage $page The source page object
     * @param string $html compiled HTML content
     * @return static new processed instance
     */
    public static function create(DocumentationPage $page, string $html): static
    {
        return (new static($page, $html))->process();
    }

    /**
     * Does the current document use Torchlight?
     *
     * @return boolean
     */
    public function hasTorchlight(): bool
    {
        return Features::hasTorchlight() && str_contains($this->html, 'Syntax highlighted by torchlight.dev');
    }

    /**
     * Do we satisfy the requirements to render an edit source button in the supplied position?
     *
     * @param string $inPosition
     * @return boolean
     */
    protected function canRenderSourceLink(string $inPosition): bool
    {
        $config = config('docs.edit_source_link_position', 'both');
        $positions = $config === 'both' ? ['header', 'footer'] : [$config];

        return ($this->page->getOnlineSourcePath() !== false) && in_array($inPosition, $positions);
    }
}
