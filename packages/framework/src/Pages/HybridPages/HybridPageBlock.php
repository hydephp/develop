<?php

declare(strict_types=1);

namespace Hyde\Pages\HybridPages;

use Spatie\YamlFrontMatter\YamlFrontMatter;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Pages\HybridPage;

abstract class HybridPageBlock
{
    protected HybridPage $page;
    protected FrontMatter $data;
    // raw block content, verbatim — the identity source
    protected string $content;
    // content minus front matter (the template / slot)
    protected string $body;

    public function __construct(HybridPage $page, string $content)
    {
        $this->page = $page;
        $this->content = $content;

        [$this->data, $this->body] = $this->parse($content);
    }

    /**
     * Split raw block content into its front matter and body.
     *
     * @return array{\Hyde\Markdown\Models\FrontMatter, string}
     */
    protected function parse(string $content): array
    {
        $document = YamlFrontMatter::markdownCompatibleParse($content);

        return [FrontMatter::fromArray($document->matter()), $document->body()];
    }

    public function signature(): string
    {
        return sprintf('<!-- HYDE[HybridPageBlock]%s -->', $this->hash());
    }

    protected function hash(): string
    {
        return hash('sha256', static::class."\0".$this->content);
    }

    abstract public function render(): string;
}
