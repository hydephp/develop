<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;

/**
 * Publication pages adds an easy way to create custom no-code page types,
 * with support using a custom front matter schema and Blade templates.
 */
class PublicationPage extends Concerns\BaseMarkdownPage
{
    public PublicationType $type;

    public static string $sourceDirectory = '__publications';
    public static string $outputDirectory = '__publications';
    public static string $template = '__dynamic';

    public function __construct(PublicationType $type, string $identifier = '', FrontMatter|array $matter = [], Markdown|string $markdown = '')
    {
        $this->type = $type;

        parent::__construct($identifier, $matter, $markdown);
    }

    // TODO: override method to get output directory from publication type etc

    public function compile(): string
    {
        // TODO: Implement compile() method.
    }
}
