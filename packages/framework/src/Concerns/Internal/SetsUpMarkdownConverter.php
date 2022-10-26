<?php
declare(strict_types=1);

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Framework\Modules\Markdown\BladeDownProcessor;
use Hyde\Framework\Modules\Markdown\CodeblockFilepathProcessor;
use Hyde\Framework\Modules\Markdown\ShortcodeProcessor;
use Torchlight\Commonmark\V2\TorchlightExtension;

/**
 * @internal Sets up the Markdown converter for the Markdown service.
 *
 * @see \Hyde\Framework\Services\MarkdownService
 */
trait SetsUpMarkdownConverter
{
    protected function enableDynamicExtensions(): void
    {
        if ($this->canEnablePermalinks()) {
            $this->configurePermalinksExtension();
        }

        if ($this->canEnableTorchlight()) {
            $this->addExtension(TorchlightExtension::class);
        }

        if (config('markdown.allow_html', false)) {
            $this->enableAllHtmlElements();
        }
    }

    protected function enableConfigDefinedExtensions(): void
    {
        foreach (config('markdown.extensions', []) as $extensionClassName) {
            $this->addExtension($extensionClassName);
        }
    }

    protected function mergeMarkdownConfiguration(): void
    {
        $this->config = array_merge(config('markdown.config', []), $this->config);
    }

    public function initializeExtension(string $extensionClassName): void
    {
        $this->converter->getEnvironment()->addExtension(new $extensionClassName());
    }

    protected function registerPreProcessors(): void
    {
        $this->registerPreProcessor(BladeDownProcessor::class, config('markdown.enable_blade', false));

        $this->registerPreProcessor(ShortcodeProcessor::class);
        $this->registerPreProcessor(CodeblockFilepathProcessor::class);
    }

    protected function registerPostProcessors(): void
    {
        $this->registerPostProcessor(
            BladeDownProcessor::class,
            config('markdown.enable_blade', false)
        );

        $this->registerPostProcessor(
            CodeblockFilepathProcessor::class,
            config('markdown.features.codeblock_filepaths', true)
        );
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
