<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing\BladeBlocks;

use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;
use Hyde\Support\Facades\Render;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\View\ComponentAttributeBag;
use Spatie\YamlFrontMatter\YamlFrontMatter;

use function array_merge;
use function filled;
use function sprintf;

class BladeComponentBlock extends BladeBlock
{
    protected string $name;
    protected string $body;
    protected FrontMatter $data;

    public function __construct(string $content, string $name)
    {
        $this->name = $name;
        [$this->data, $this->body] = $this->parse($content);

        parent::__construct($content);
    }

    protected function render(): string
    {
        $slot = filled($this->body) ? Markdown::render($this->body, $this->pageClass()) : '';

        return Blade::render(
            sprintf('<x-%s :$attributes>{!! $slot !!}</x-%s>', $this->name, $this->name),
            [
                'attributes' => new ComponentAttributeBag($this->data->toArray()),
                'slot' => new HtmlString($slot),
            ],
        );
    }

    /** @return array{FrontMatter, string} */
    protected function parse(string $content): array
    {
        if (str_starts_with($content, '---')) {
            $document = YamlFrontMatter::markdownCompatibleParse($content);

            return [FrontMatter::fromArray($document->matter()), $document->body()];
        }

        return [FrontMatter::fromArray([]), $content];
    }

    /** @return class-string<\Hyde\Pages\Concerns\HydePage>|null */
    protected function pageClass(): ?string
    {
        $page = Render::getPage();

        return $page ? $page::class : null;
    }

    /** @inheritDoc */
    protected function getHashableContent(): array
    {
        return array_merge(parent::getHashableContent(), [$this->name]);
    }
}
