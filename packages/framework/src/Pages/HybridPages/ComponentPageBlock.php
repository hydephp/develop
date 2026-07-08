<?php

declare(strict_types=1);

namespace Hyde\Pages\HybridPages;

use Hyde\Markdown\Models\FrontMatter;
use Hyde\Pages\HybridPage;
use Symfony\Component\Yaml\Yaml;

class ComponentPageBlock extends HybridPageBlock
{
    protected string $name;

    public function __construct(HybridPage $page, string $content, string $name)
    {
        $this->name = $name;

        parent::__construct($page, $content);
    }

    public function render(): string
    {
        return $this->content;
    }

    /** @return array{FrontMatter, string} */
    protected function parse(string $content): array
    {
        // Triple-dash form → front matter + Markdown slot.
        if (str_starts_with(ltrim($content), '---')) {
            return parent::parse($content);
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
