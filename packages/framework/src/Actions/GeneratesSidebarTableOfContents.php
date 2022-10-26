<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Models\Markdown\Markdown;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Generates a table of contents for the Markdown document.
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\GeneratesSidebarTableOfContentsTest
 */
class GeneratesSidebarTableOfContents
{
    protected string $markdown;

    public function __construct(Markdown|string $markdown)
    {
        $this->markdown = (string) $markdown;
    }

    public function execute(): string
    {
        return $this->withoutMarker($this->convert($this->getMarkdownConverter()));
    }

    protected function getConfig(): array
    {
        return [
            'table_of_contents' => [
                'html_class' => 'table-of-contents',
                'position' => 'top',
                'style' => 'bullet',
                'min_heading_level' => config('docs.table_of_contents.min_heading_level', 2),
                'max_heading_level' => config('docs.table_of_contents.max_heading_level', 4),
                'normalize' => 'relative',
            ],
            'heading_permalink' => [
                'fragment_prefix' => '',
            ],
        ];
    }

    protected function getMarkdownConverter(): MarkdownConverter
    {
        $environment = new Environment($this->getConfig());
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new HeadingPermalinkExtension());
        $environment->addExtension(new TableOfContentsExtension());

        return new MarkdownConverter($environment);
    }

    protected function convert(MarkdownConverter $converter): string
    {
        return $converter->convert($this->withEndMarker())->getContent();
    }

    protected function withEndMarker(): string
    {
        // We add a marker that will be injected right after the compiled table of contents.
        return "[[END_TOC]]\n" . $this->markdown;
    }

    protected function withoutMarker(string $html): string
    {
        // We can then use the position of this marker to only return the table of contents.
        return substr($html, 0, strpos($html, '<p>[[END_TOC]]'));
    }
}
