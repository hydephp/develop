<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Modules\Markdown\BladeDownProcessor;
use Hyde\Framework\Modules\Markdown\CodeblockFilepathProcessor;
use Hyde\Framework\Modules\Markdown\MarkdownConverter;
use Hyde\Framework\Modules\Markdown\ShortcodeProcessor;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use Torchlight\Commonmark\V2\TorchlightExtension;

/**
 * Dynamically creates a Markdown converter tailored for the target model and setup,
 * then converts the Markdown to HTML using both pre- and post-processors.
 *
 * @see \Hyde\Framework\Testing\Feature\MarkdownServiceTest
 */
class MarkdownService
{
    public string $markdown;
    public ?string $sourceModel = null;

    protected array $config = [];
    protected array $extensions = [];
    protected MarkdownConverter $converter;

    protected string $html;
    protected array $features = [];

    protected bool $useTorchlight;
    protected bool $torchlightAttribution;

    public function __construct(string $markdown, ?string $sourceModel = null)
    {
        $this->sourceModel = $sourceModel;
        $this->markdown = $markdown;
    }

    public function parse(): string
    {
        $this->setupConverter();

        $this->runPreprocessing();

        $this->html = $this->converter->convert($this->markdown);

        $this->runPostProcessing();

        return $this->html;
    }

    public function addExtension(string $extensionClassName): void
    {
        if (! in_array($extensionClassName, $this->extensions)) {
            $this->extensions[] = $extensionClassName;
        }
    }

    public function initializeExtension(string $extensionClassName): void
    {
        $this->converter->getEnvironment()->addExtension(new $extensionClassName());
    }

    protected function setupConverter(): void
    {
        // Determine what dynamic extensions to enable

        if ($this->canEnablePermalinks()) {
            $this->addExtension(HeadingPermalinkExtension::class);

            $this->config = array_merge([
                'heading_permalink' =>[
                    'id_prefix' => '',
                    'fragment_prefix' => '',
                    'symbol' => '#',
                    'insert' => 'after',
                    'min_heading_level' => 2,
                ],
            ], $this->config);
        }

        if ($this->canEnableTorchlight()) {
            $this->addExtension(TorchlightExtension::class);
        }

        if (config('markdown.allow_html', false)) {
            $this->addExtension(DisallowedRawHtmlExtension::class);

            $this->config = array_merge([
                'disallowed_raw_html' => [
                    'disallowed_tags' => [],
                ],
            ], $this->config);
        }

        // Add any custom extensions defined in config
        foreach (config('markdown.extensions', []) as $extensionClassName) {
            $this->addExtension($extensionClassName);
        }

        // Merge any custom configuration options
        $this->config = array_merge(config('markdown.config', []), $this->config);

        $this->converter = new MarkdownConverter($this->config);

        foreach ($this->extensions as $extension) {
            $this->initializeExtension($extension);
        }
    }

    protected function runPreprocessing(): void
    {
        if (config('markdown.enable_blade', false)) {
            $this->markdown = BladeDownProcessor::preprocess($this->markdown);
        }

        $this->markdown = ShortcodeProcessor::process($this->markdown);

        $this->markdown = CodeblockFilepathProcessor::preprocess($this->markdown);
    }

    protected function runPostProcessing(): void
    {
        if ($this->determineIfTorchlightAttributionShouldBeInjected()) {
            $this->html .= $this->injectTorchlightAttribution();
        }

        if (config('markdown.enable_blade', false)) {
            $this->html = BladeDownProcessor::process($this->html);
        }

        if (config('markdown.features.codeblock_filepaths', true)) {
            $this->html = CodeblockFilepathProcessor::process($this->html);
        }

        // Remove any Hyde annotations (everything between `// HYDE!` and `HYDE! //`) (must be done last)
        $this->html = preg_replace('/ \/\/ HYDE!.*HYDE! \/\//s', '', $this->html);
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }

    public function removeFeature(string $feature): static
    {
        if (in_array($feature, $this->features)) {
            $this->features = array_diff($this->features, [$feature]);
        }

        return $this;
    }

    public function addFeature(string $feature): static
    {
        if (! in_array($feature, $this->features)) {
            $this->features[] = $feature;
        }

        return $this;
    }

    public function withPermalinks(): static
    {
        $this->addFeature('permalinks');

        return $this;
    }

    public function isDocumentationPage(): bool
    {
        return isset($this->sourceModel) && $this->sourceModel === DocumentationPage::class;
    }

    public function withTableOfContents(): static
    {
        $this->addFeature('table-of-contents');

        return $this;
    }

    public function canEnableTorchlight(): bool
    {
        return $this->hasFeature('torchlight') ||
            Features::hasTorchlight();
    }

    public function canEnablePermalinks(): bool
    {
        if ($this->hasFeature('permalinks')) {
            return true;
        }

        if ($this->isDocumentationPage() && DocumentationPage::hasTableOfContents()) {
            return true;
        }

        return false;
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features);
    }

    protected function determineIfTorchlightAttributionShouldBeInjected(): bool
    {
        return ! $this->isDocumentationPage()
            && config('torchlight.attribution.enabled', true)
            && str_contains($this->html, 'Syntax highlighted by torchlight.dev');
    }

    protected function injectTorchlightAttribution(): string
    {
        return '<br>'.$this->converter->convert(config(
            'torchlight.attribution.markdown',
            'Syntax highlighted by torchlight.dev'
        ));
    }
}
