<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Concerns\FacadeHelpers\HydeSmartDocsFacade;
use Hyde\Framework\Models\DocumentationPage;

/**
 * Class to make Hyde documentation pages smarter,
 * allowing for rich and dynamic content.
 */
class HydeSmartDocs
{
    use HydeSmartDocsFacade;

    protected DocumentationPage $page;
    protected string $html;

    protected string $header;
    protected string $body;
    protected string $footer;

    public function __construct(DocumentationPage $page, string $html)
    {
        $this->page = $page;
        $this->html = $html;
    }

    public function renderHeader(): string
    {
        return $this->header;
    }

    public function renderBody(): string
    {
        return $this->body;
    }

    public function renderFooter(): string
    {
        return $this->footer;
    }

    /** @internal */
    public function process(): self
    {
        $this->tokenize();

        $this->addDynamicHeaderContent();
        $this->addDynamicFooterContent();

        return $this;
    }

    protected function tokenize(): self
    {
        // The HTML content is expected to be two parts. To create semantic HTML,
        // we need to split the content into header and body. We do this by
        // extracting the first <h1> tag and everything before it.

        // Split the HTML content by the first newline
        $parts = explode("\n", $this->html, 2);

        $this->header = $parts[0];
        $this->body = $parts[1] ?? '';

        return $this;
    }

    protected function addDynamicHeaderContent(): self
    {
        // Hook to add dynamic content to the header.
        // This is where we can add TOC, breadcrumbs, etc.

        return $this;
    }

    protected function addDynamicFooterContent(): self
    {
        // Hook to add dynamic content to the footer.
        // This is where we can add copyright, attributions, info, etc.

        return $this;
    }
}