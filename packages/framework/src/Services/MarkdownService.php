<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Framework\Actions\MarkdownConverter;
use Hyde\Framework\Concerns\Internal\SetsUpMarkdownConverter;
use Hyde\Framework\Contracts\MarkdownPostProcessorContract as PostProcessor;
use Hyde\Framework\Contracts\MarkdownPreProcessorContract as PreProcessor;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Models\Pages\DocumentationPage;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;

/**
 * Dynamically creates a Markdown converter tailored for the target model and setup,
 * then converts the Markdown to HTML using both pre- and post-processors.
 *
 * @see \Hyde\Framework\Testing\Feature\MarkdownServiceTest
 */
class MarkdownService
{
    use SetsUpMarkdownConverter;

    public string $markdown;
    public ?string $sourceModel = null;

    protected array $config = [];
    protected array $extensions = [];
    protected MarkdownConverter $converter;

    protected string $html;
    protected array $features = [];

    protected array $preprocessors = [];
    protected array $postprocessors = [];

    public function __construct(string $markdown, ?string $sourceModel = null)
    {
        $this->sourceModel = $sourceModel;
        $this->markdown = $markdown;
    }

    public function parse(): string
    {
        $this->setupConverter();

        $this->runPreProcessing();

        $this->html = (string) $this->converter->convert($this->markdown);

        $this->runPostProcessing();

        return $this->html;
    }

    protected function setupConverter(): void
    {
        $this->enableDynamicExtensions();

        $this->enableConfigDefinedExtensions();

        $this->mergeMarkdownConfiguration();

        $this->converter = new MarkdownConverter($this->config);

        foreach ($this->extensions as $extension) {
            $this->initializeExtension($extension);
        }

        $this->registerPreProcessors();
        $this->registerPostProcessors();
    }

    public function addExtension(string $extensionClassName): void
    {
        if (! in_array($extensionClassName, $this->extensions)) {
            $this->extensions[] = $extensionClassName;
        }
    }

    protected function runPreProcessing(): void
    {
        /** @var PreProcessor $processor */
        foreach ($this->preprocessors as $processor) {
            $this->markdown = $processor::preprocess($this->markdown);
        }
    }

    protected function runPostProcessing(): void
    {
        if ($this->determineIfTorchlightAttributionShouldBeInjected()) {
            $this->html .= $this->injectTorchlightAttribution();
        }

        /** @var PostProcessor $processor */
        foreach ($this->postprocessors as $processor) {
            $this->html = $processor::postprocess($this->html);
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

    protected function configurePermalinksExtension(): void
    {
        $this->addExtension(HeadingPermalinkExtension::class);

        $this->config = array_merge([
            'heading_permalink' => [
                'id_prefix' => '',
                'fragment_prefix' => '',
                'symbol' => '#',
                'insert' => 'after',
                'min_heading_level' => 2,
            ],
        ], $this->config);
    }

    protected function enableAllHtmlElements(): void
    {
        $this->addExtension(DisallowedRawHtmlExtension::class);

        $this->config = array_merge([
            'disallowed_raw_html' => [
                'disallowed_tags' => [],
            ],
        ], $this->config);
    }
}
