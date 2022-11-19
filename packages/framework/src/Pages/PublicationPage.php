<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;
use Illuminate\Support\Str;
use function view;

/**
 * Publication pages adds an easy way to create custom no-code page types,
 * with support using a custom front matter schema and Blade templates.
 */
class PublicationPage extends Concerns\BaseMarkdownPage
{
    public PublicationType $type;

    public static string $sourceDirectory = '';
    public static string $outputDirectory = '';
    public static string $template = '__dynamic';

    public function __construct(PublicationType $type, string $identifier = '', FrontMatter|array $matter = [], Markdown|string $markdown = '')
    {
        parent::__construct("{$type->getDirectory()}/$identifier", $matter, $markdown);
        $this->type = $type;
    }

    // TODO: override method to get output directory from publication type etc

    public function compile(): string
    {
        $detailTemplate = $this->type->getSchema()['detailTemplate'];
        $component = Str::before("pubtypes.$detailTemplate", '.blade.php');

        return view('hyde::layouts.pubtype')->with(['component' => $component, 'publication' => $this])->render();
    }
}
