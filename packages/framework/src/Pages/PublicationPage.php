<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;
use function view;

/**
 * Publication pages adds an easy way to create custom no-code page types,
 * with support using a custom front matter schema and Blade templates.
 *
 * @see \Hyde\Framework\Testing\Feature\PublicationPageTest
 */
class PublicationPage extends Concerns\BaseMarkdownPage
{
    public PublicationType $type;

    public static string $sourceDirectory = '';
    public static string $outputDirectory = '';
    public static string $template = '__dynamic';

    public function __construct(PublicationType $type, string $identifier = '', FrontMatter|array $matter = [], Markdown|string $markdown = '')
    {
        $this->type = $type;

        parent::__construct("{$type->getDirectory()}/$identifier", $matter, $markdown);
    }

    public function compile(): string
    {
        return view('hyde::layouts.publication', [
            'component' => "pubtypes.{$this->type->getSchema()['detailTemplate']}",
            'publication' => $this,
        ])->render();
    }
}
