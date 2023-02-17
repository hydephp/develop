<?php

declare(strict_types=1);

namespace Hyde\Publications\Models;

use Hyde\Framework\Concerns\ValidatesExistence;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;
use Hyde\Pages\Concerns;
use Hyde\Publications\Actions\PublicationPageCompiler;

/**
 * Publication pages adds an easy way to create custom no-code page types,
 * with support using a custom front matter schema and Blade templates.
 *
 * @see \Hyde\Publications\Testing\Feature\PublicationPageTest
 */
class PublicationPage extends Concerns\BaseMarkdownPage
{
    use ValidatesExistence;

    // Identifier
    public static string $publicationType;
    public PublicationType $type;

    public static string $sourceDirectory = '';
    public static string $outputDirectory = '';
    public static string $template = '__dynamic';

    public static function make(string $identifier = '', FrontMatter|array $matter = [], string|Markdown $markdown = '', ?PublicationType $type = null): static
    {
        return new static($identifier, $matter, $markdown, $type);
    }

    public function __construct(string $identifier = '', FrontMatter|array $matter = [], Markdown|string $markdown = '', ?PublicationType $type = null)
    {
        $this->type = $type ?? PublicationType::get(static::$publicationType);

        parent::__construct($identifier, $matter, $markdown);
    }

    public function compile(): string
    {
        return $this->renderComponent();
    }

    protected function renderComponent(): string
    {
        return PublicationPageCompiler::call($this);
    }
}
