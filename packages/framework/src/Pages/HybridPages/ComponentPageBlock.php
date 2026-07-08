<?php

declare(strict_types=1);

namespace Hyde\Pages\HybridPages;

use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;
use Hyde\Pages\HybridPage;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\View\ComponentAttributeBag;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Symfony\Component\Yaml\Yaml;

class ComponentPageBlock extends HybridPageBlock
{
    protected string $name;
    protected string $body;
    protected FrontMatter $data;

    public function __construct(HybridPage $page, string $content, string $name)
    {
        $this->name = $name;
        [$this->data, $this->body] = $this->parse($content);

        parent::__construct($page, $content);
    }

    public function render(): string
    {
        $slot = $this->body === '' ? '' : Markdown::render($this->body, $this->page::class);

        return Blade::render(
            sprintf('<x-%s :$attributes>{!! $slot !!}</x-%s>', $this->name, $this->name),
            [
                'attributes' => new ComponentAttributeBag($this->data->toArray()),
                'slot' => new HtmlString($slot),
                'page' => $this->page,
            ],
        );
    }

    /** @return array{FrontMatter, string} */
    protected function parse(string $content): array
    {
        // Triple-dash form → front matter + Markdown slot.
        if (str_starts_with(ltrim($content), '---')) {
            $document = YamlFrontMatter::markdownCompatibleParse($content);

            return [FrontMatter::fromArray($document->matter()), $document->body()];
        }

        // Shorthand form → the entire block is front matter, no slot.
        $matter = Yaml::parse($content);

        return [FrontMatter::fromArray(is_array($matter) ? $matter : []), ''];
    }

    protected function hash(): string
    {
        return hash('sha256', static::class."\0".$this->name."\0".$this->content);
    }
}
