<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions;

use Hyde\Facades\Config;
use Hyde\Framework\Exceptions\InvalidConfigurationException;
use Illuminate\Support\HtmlString;

use function in_array;
use function view;

/** @internal */
class CodeBlockViewModel
{
    public function __construct(
        public readonly string $contents,
        public readonly ?string $language = null,
        public readonly HtmlString|string|null $label = null,
    ) {
        //
    }

    public function render(): string
    {
        if ($this->label !== null) {
            $this->validateLabelStyle();
        }

        return view('hyde::components.markdown.code-block', $this->viewData())->render();
    }

    /** @return array{contents: string, language: ?string, label: \Illuminate\Support\HtmlString|string|null} */
    protected function viewData(): array
    {
        return [
            'contents' => $this->contents,
            'language' => $this->language,
            'label' => $this->label,
        ];
    }

    protected function validateLabelStyle(): void
    {
        $style = Config::getString('markdown.code_block_label_style', 'header');

        if (! in_array($style, ['header', 'badge'], true)) {
            throw new InvalidConfigurationException(
                "Invalid code block label style [$style]. Supported styles are [header] and [badge].",
                'markdown',
                'code_block_label_style',
            );
        }
    }
}
