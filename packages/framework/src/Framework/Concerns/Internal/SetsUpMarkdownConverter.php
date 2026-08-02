<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Facades\Config;
use Hyde\Markdown\Extensions\Nodes\CodeBlock;
use Hyde\Markdown\Extensions\Processing\CodeBlockRenderer;
use Hyde\Markdown\Extensions\Processing\PrepareCodeBlocks;
use Hyde\Markdown\Extensions\Processing\WrapCodeBlocks;
use Hyde\Markdown\Extensions\TerminalExtension;
use Hyde\Markdown\Processing\BladeBlockProcessor;
use Hyde\Markdown\Processing\BladeDownProcessor;
use Hyde\Markdown\Processing\ShortcodeProcessor;
use Hyde\Markdown\Processing\DynamicMarkdownLinkProcessor;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Event\DocumentPreRenderEvent;
use Torchlight\Commonmark\V2\TorchlightExtension;

use function array_merge;
use function in_array;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * Sets up the Markdown converter for the Markdown service.
 *
 * @internal This trait is not covered by the backward compatibility promise.
 *
 * @see \Hyde\Framework\Services\MarkdownService
 */
trait SetsUpMarkdownConverter
{
    protected function enableDynamicExtensions(): void
    {
        $this->addExtension(TerminalExtension::class);

        if ($this->canEnableTorchlight()) {
            $this->addExtension(TorchlightExtension::class);
        }

        if (Config::getBool('markdown.allow_html', true)) {
            $this->enableAllHtmlElements();
        }
    }

    protected function enableConfigDefinedExtensions(): void
    {
        foreach (Config::getArray('markdown.extensions', []) as $extensionClassName) {
            $this->addExtension($extensionClassName);
        }
    }

    protected function mergeMarkdownConfiguration(): void
    {
        $this->config = array_merge(Config::getArray('markdown.config', []), $this->config);
    }

    public function initializeExtension(string $extensionClassName): void
    {
        $this->converter->getEnvironment()->addExtension(new $extensionClassName());
    }

    /**
     * Registered ahead of every extension, and at the highest priority, so that the code and info
     * string a listener collects have already had the Hyde syntax taken out of them. Listeners
     * sharing a priority run in registration order, so both of those matter.
     */
    protected function prepareCodeBlocks(): void
    {
        $this->converter->getEnvironment()->addEventListener(
            DocumentParsedEvent::class, new PrepareCodeBlocks(), PHP_INT_MAX
        );
    }

    /**
     * Hyde renders the view around a code block, not the code, which is left to whichever renderer
     * the environment already had for the fence. So the fence is wrapped in a node of our own, last
     * of all, once every listener has had the document in the shape it expects.
     */
    protected function configureCodeBlockRenderer(): void
    {
        $this->converter->getEnvironment()
            ->addEventListener(DocumentPreRenderEvent::class, new WrapCodeBlocks(), PHP_INT_MIN)
            ->addRenderer(CodeBlock::class, new CodeBlockRenderer());
    }

    protected function registerPreProcessors(): void
    {
        $enableBlade = Config::getBool('markdown.enable_blade', true);

        // Registered first so blocks are extracted before other processors read the fences.
        $this->registerPreProcessor(BladeBlockProcessor::class, $enableBlade);
        $this->registerPreProcessor(BladeDownProcessor::class, $enableBlade);

        $this->registerPreProcessor(ShortcodeProcessor::class);
    }

    protected function registerPostProcessors(): void
    {
        $enableBlade = Config::getBool('markdown.enable_blade', true);

        $this->registerPostProcessor(BladeBlockProcessor::class, $enableBlade);
        $this->registerPostProcessor(BladeDownProcessor::class, $enableBlade);

        $this->registerPostProcessor(DynamicMarkdownLinkProcessor::class);
    }

    protected function registerPreProcessor(string $class, bool $when = true): void
    {
        if (! in_array($class, $this->preprocessors) && $when) {
            $this->preprocessors[] = $class;
        }
    }

    protected function registerPostProcessor(string $class, bool $when = true): void
    {
        if (! in_array($class, $this->postprocessors) && $when) {
            $this->postprocessors[] = $class;
        }
    }
}
