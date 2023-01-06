<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Closure;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\Contracts\DynamicPage;
use Illuminate\Support\Facades\View;

/**
 * A virtual page is a page that does not have a source file.
 *
 * @experimental This feature is experimental and may change substantially before the 1.0.0 release.
 *
 * This can be useful for creating pagination pages and the like.
 * When used in a package, it's on the package developer to ensure
 * that the virtual page is registered with Hyde, usually within the
 * boot method of the package's service provider so it can be compiled.
 */
class VirtualPage extends HydePage implements DynamicPage
{
    protected string|Closure $contents;
    protected string $view;

    public static string $sourceDirectory = '';
    public static string $outputDirectory = '';
    public static string $fileExtension = '';

    public static function make(string $identifier = '', FrontMatter|array $matter = [], string|Closure $contents = ''): static
    {
        return new static($identifier, $matter, $contents);
    }

    public function __construct(string $identifier, FrontMatter|array $matter = [], string|Closure $contents = '', string $view = '')
    {
        parent::__construct($identifier, $matter);

        $this->contents = $contents;
        $this->view = $view;
    }

    public function contents(): string
    {
        return $this->contents;
    }

    public function compile(): string
    {
        if ($this->view) {
            return View::make($this->view, $this->matter->toArray())->render();
        }

        if ($this->contents instanceof Closure) {
            return ($this->contents)($this);
        }

        return $this->contents();
    }
}
