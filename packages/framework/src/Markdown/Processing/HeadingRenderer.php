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
 *
 * @todo Add more options to customize the permalink.
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
        $html = preg_replace('/<h([1-6]) >/', '<h$1>', $html);

        return implode('', array_map('trim', explode("\n", $html)));
    }
}
