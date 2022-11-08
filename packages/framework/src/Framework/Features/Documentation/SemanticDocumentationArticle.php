<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Documentation;

use Hyde\Facades\Features;
use Hyde\Pages\DocumentationPage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use function str_contains;

/**
 * Class to make Hyde documentation pages smarter,
 * by dynamically enriching them with semantic HTML.
 *
 * @see \Hyde\Framework\Testing\Feature\Services\HydeSmartDocsTest
 */
class SemanticDocumentationArticle
{
    protected DocumentationPage $page;
    protected string $html;

    protected string $header = '';
    protected string $body;
    protected string $footer = '';

    /**
     * Create a new SemanticDocumentationArticle instance, process, and return it.
     *
     * @param  \Hyde\Pages\DocumentationPage  $page  The source page object
     * @return static new processed instance
     */
    public static function create(DocumentationPage $page): static
    {
        return (new self($page))->process();
    }

    public function __construct(DocumentationPage $page)
    {
        $this->page = $page;
        $this->html = $page->markdown->compile();
    }

    public function renderHeader(): HtmlString
    {
        return new HtmlString($this->header);
    }

    public function renderBody(): HtmlString
    {
        return new HtmlString($this->body);
    }

    public function renderFooter(): HtmlString
    {
        return new HtmlString($this->footer);
    }

    /** @internal */
    public function process(): self
    {
        $this->tokenize();

        $this->addDynamicHeaderContent();
        $this->addDynamicFooterContent();

        return $this;
    }

    protected function tokenize(): static
    {
        // The HTML content is expected to be two parts. To create semantic HTML,
        // we need to split the content into header and body. We do this by
        // extracting the first <h1> tag and everything before it.

        if (str_contains($this->html, '<h1>')) {
            // Split the HTML content by the first newline, which is always after the <h1> tag
            [$this->header, $this->body] = explode("\n", $this->html, 2);
        } else {
            $this->body = $this->html;
        }

        $this->normalizeBody();

        return $this;
    }

    protected function addDynamicHeaderContent(): static
    {
        // Hook to add dynamic content to the header.
        // This is where we can add TOC, breadcrumbs, etc.

        if ($this->canRenderSourceLink('header')) {
            $this->header .= $this->renderSourceLink();
        }

        return $this;
    }

    protected function addDynamicFooterContent(): static
    {
        // Hook to add dynamic content to the footer.
        // This is where we can add copyright, attributions, info, etc.

        if (config('torchlight.attribution.enabled', true) && $this->hasTorchlight()) {
            $this->footer .= Str::markdown(config(
                'torchlight.attribution.markdown',
                'Syntax highlighted by torchlight.dev'
            ));
        }

        if ($this->canRenderSourceLink('footer')) {
            $this->footer .= $this->renderSourceLink();
        }

        return $this;
    }

    protected function renderSourceLink(): string
    {
        return view('hyde::components.docs.edit-source-button', [
            'href' => $this->page->getOnlineSourcePath(),
        ])->render();
    }

    /**
     * Does the current document use Torchlight?
     *
     * @return bool
     */
    public function hasTorchlight(): bool
    {
        return Features::hasTorchlight() && str_contains($this->html, 'Syntax highlighted by torchlight.dev');
    }

    /**
     * Do we satisfy the requirements to render an edit source button in the supplied position?
     *
     * @param  string  $inPosition
     * @return bool
     */
    protected function canRenderSourceLink(string $inPosition): bool
    {
        $config = config('docs.edit_source_link_position', 'both');
        $positions = $config === 'both' ? ['header', 'footer'] : [$config];

        return ($this->page->getOnlineSourcePath() !== false) && in_array($inPosition, $positions);
    }

    /**
     * Remove possible trailing newlines added by the Markdown compiler to normalize the body.
     */
    protected function normalizeBody(): void
    {
        $this->body = trim($this->body, "\n");
    }
}
