<?php

declare(strict_types=1);

namespace Hyde\Pages\HybridPages;

use Hyde\Markdown\Models\FrontMatter;
use Illuminate\Support\Facades\Blade;

class BladePageBlock extends HybridPageBlock
{
    public function render(): string
    {
        return Blade::render($this->content, $this->data->toArray());
    }

    /**
     * Blade content is never front-matter-parsed — it may legitimately
     * contain `---` or colons. The whole block is the template, verbatim.
     *
     * @return array{FrontMatter, string}
     */
    protected function parse(string $content): array
    {
        return [FrontMatter::fromArray([]), $content];
    }
}
