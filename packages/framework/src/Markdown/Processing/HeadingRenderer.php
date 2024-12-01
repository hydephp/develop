<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing;

use Hyde\Pages\DocumentationPage;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

/**
 * Renders a heading node, and supports built-in permalink generation.
 *
 * @see \League\CommonMark\Extension\CommonMark\Renderer\Block\HeadingRenderer
 */
class HeadingRenderer implements NodeRendererInterface
{
    /** @var ?class-string<\Hyde\Pages\Concerns\HydePage> */
    protected ?string $pageClass = null;

    /** @param ?class-string<\Hyde\Pages\Concerns\HydePage> $pageClass */
    public function __construct(string $pageClass = null)
    {
        $this->pageClass = $pageClass;
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (! ($node instanceof Heading)) {
            throw new \InvalidArgumentException('Incompatible node type: '.get_class($node));
        }

        $content = $childRenderer->renderNodes($node->children());

        $rendered = view('hyde::components.markdown-heading', [
            'level' => $node->getLevel(),
            'slot' => $content,
            'addPermalink' => $this->canAddPermalink($content, $node->getLevel()),
            'extraAttributes' => $node->data->get('attributes'),
        ])->render();

        return $this->postProcess($rendered);
    }

    protected function canAddPermalink(string $content, int $level): bool
    {
        return config('markdown.permalinks.enabled', true)
            && $level >= config('markdown.permalinks.min_level', 2)
            && $level <= config('markdown.permalinks.max_level', 6)
            && ! str_contains($content, 'class="heading-permalink"')
            && in_array($this->pageClass, config('markdown.permalinks.pages', [DocumentationPage::class]));
    }

    protected function postProcess(string $html): string
    {
        $html = str_replace('<h1 >', '<h1>', $html);
        $html = str_replace('<h2 >', '<h2>', $html);
        $html = str_replace('<h3 >', '<h3>', $html);
        $html = str_replace('<h4 >', '<h4>', $html);
        $html = str_replace('<h5 >', '<h5>', $html);
        $html = str_replace('<h6 >', '<h6>', $html);

        return implode('', array_map('trim', explode("\n", $html)));
    }
}
