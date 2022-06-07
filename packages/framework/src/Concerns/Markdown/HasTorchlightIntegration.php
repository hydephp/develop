<?php

namespace Hyde\Framework\Concerns\Markdown;

/**
 * Helper methods for the Torchlight integration.
 *
 * @see \Hyde\Framework\Services\MarkdownConverterService
 */
trait HasTorchlightIntegration
{
    protected bool $useTorchlight;
    protected bool $torchlightAttribution;

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
