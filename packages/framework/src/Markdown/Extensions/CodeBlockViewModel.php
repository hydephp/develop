<?php

declare(strict_types=1);

namespace Hyde\Markdown\Extensions;

use Illuminate\Support\HtmlString;

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
}
