<?php

declare(strict_types=1);

namespace Hyde\Pages;

use function file_get_contents;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
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
            'publication' => new HtmlString($this->renderComponent()),
        ])->render();
    }

    protected function renderComponent(): string
    {
        $data = [
            'publication' => $this,
        ];

        $template = $this->type->detailTemplate;
        if (str_contains($template, '::')) {
            return view($template, $data)->render();
        }

        // Using the Blade facade we can render any file without having to register the directory with the view finder.
        return Blade::render(
            file_get_contents(Hyde::path("{$this->type->getDirectory()}/$template.blade.php")), $data
        );
    }
}
