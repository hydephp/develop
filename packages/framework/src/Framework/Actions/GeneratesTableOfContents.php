<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Facades\Config;
use Hyde\Markdown\Models\Markdown;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\MarkdownConverter;

use function strpos;
use function substr;

/**
 * Generates a table of contents for the Markdown document, most commonly used for the sidebar.
 */
class GeneratesTableOfContents
{
    protected string $markdown;

    public function __construct(Markdown|string $markdown)
    {
        $this->markdown = (string) $markdown;
    }

    public function execute(): string
    {
        $config = [
            'table_of_contents' => [
                'html_class' => 'table-of-contents',
                'position' => 'top',
                'style' => 'bullet',
                'min_heading_level' => Config::getInt('docs.table_of_contents.min_heading_level', 2),
                'max_heading_level' => Config::getInt('docs.table_of_contents.max_heading_level', 4),
                'normalize' => 'relative',
            ],
            'heading_permalink' => [
                'fragment_prefix' => '',
            ],
        ];

        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new HeadingPermalinkExtension());
        $environment->addExtension(new TableOfContentsExtension());

        $converter = new MarkdownConverter($environment);
        $html = $converter->convert("[[END_TOC]]\n".$this->markdown)->getContent();

        // Return everything before the [[END_TOC]] marker.
        return substr($html, 0, strpos($html, '<p>[[END_TOC]]'));
    }
}
