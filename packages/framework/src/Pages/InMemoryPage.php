<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Closure;
use Hyde\Framework\Actions\AnonymousViewCompiler;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Pages\Concerns\HydePage;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;

use function Hyde\unslash;
use function str_contains;
use function str_ends_with;
use function str_starts_with;

/**
 * Extendable class for in-memory (or virtual) Hyde pages that are not based on source files.
 *
 * When used in a package, the package developer must ensure that the virtual page is registered
 * with Hyde, usually within the boot method of the package service provider or through a page
 * collection callback in an extension. This is because these pages cannot be discovered
 * automatically since there are no source files to parse.
 *
 * Pages may use literal string contents, a lazy closure, or a Blade view. Contents and views are
 * mutually exclusive. Null constructor values mean that the corresponding source was omitted.
 *
 * Content closures receive the current page as their first argument, which they may declare or omit.
 *
 * This class is especially useful for one-off custom pages. For more advanced use cases, extend this
 * class to add custom methods or override compile() for complete control over page compilation.
 */
class InMemoryPage extends HydePage
{
    public static string $sourceDirectory;
    public static string $outputDirectory;
    public static string $sourceExtension;

    /**
     * The literal page contents, or a closure that generates them at compile time.
     *
     * We inject the current page instance into the closure as we call it.
     *
     * @var string|(Closure(): string)|(Closure(static): string)
     */
    protected string|Closure $contents;
    protected readonly bool $exactOutputPath;

    /**
     * The Blade view key or Blade file path.
     *
     * An empty string means that no view is configured.
     */
    protected string $view;

    /**
     * Static alias for the constructor.
     *
     * @param  string|(Closure(): string)|(Closure(static): string)|null  $contents
     */
    public static function make(
        string $identifier = '',
        FrontMatter|array $matter = [],
        string|Closure|null $contents = null,
        ?string $view = null,
    ): static {
        return new static($identifier, $matter, $contents, $view);
    }

    /**
     * Create an in-memory page whose identifier is used as the exact output path.
     *
     * The output path must be a relative file path contained within the site output directory.
     *
     * @param  string|(Closure(): string)|(Closure(static): string)|null  $contents
     */
    public static function file(
        string $outputPath,
        FrontMatter|array $matter = [],
        string|Closure|null $contents = null,
        ?string $view = null,
    ): static {
        return new static($outputPath, $matter, $contents, $view, exactOutputPath: true);
    }

    /**
     * Create a new in-memory (virtual) page instance.
     *
     * Pass literal contents or a closure to `$contents`, or pass a registered Laravel view key
     * or Blade file path to `$view`.
     *
     * Contents and views cannot be used together. Omit both to create an empty page.
     * An empty view value is treated as no view.
     * Normal construction uses HTML page semantics; use the `file()` constructor to create an exact-path file page.
     *
     * View values ending in `.blade.php` are treated as Blade file paths. Other values are treated
     * as registered Laravel view keys.
     *
     * @param  string  $identifier
     * @param  FrontMatter|array  $matter
     * @param  string|(Closure(): string)|(Closure(static): string)|null  $contents
     * @param  string|null  $view
     * @param  bool  $exactOutputPath  Whether to validate and use the identifier as an exact output path. Prefer the `file()` constructor for this mode.
     *
     * @throws InvalidArgumentException If both contents and a view are supplied.
     */
    public function __construct(
        string $identifier = '',
        FrontMatter|array $matter = [],
        string|Closure|null $contents = null,
        ?string $view = null,
        bool $exactOutputPath = false,
    ) {
        if ($exactOutputPath) {
            $identifier = static::normalizeExactOutputPath($identifier);
        }

        $this->exactOutputPath = $exactOutputPath;

        parent::__construct($identifier, $matter);

        $view = $view === '' ? null : $view;

        if ($contents !== null && $view !== null) {
            throw new InvalidArgumentException(
                'InMemoryPage cannot define both contents and a view.'
            );
        }

        $this->contents = $contents ?? '';
        $this->view = $view ?? '';
    }

    protected static function normalizeExactOutputPath(string $path): string
    {
        if (
            $path === ''
            || str_starts_with($path, '/')
            || str_ends_with($path, '/')
            || str_contains($path, '\\')
            || preg_match('/^[A-Za-z]:/', $path)
            || in_array('..', explode('/', $path), true)
        ) {
            throw new InvalidArgumentException(
                "Invalid exact output path [$path]. The path must be a relative file path inside the site output directory."
            );
        }

        return unslash($path);
    }

    /**
     * Get the path where the compiled page will be saved.
     */
    public function getOutputPath(): string
    {
        if ($this->exactOutputPath) {
            return unslash($this->identifier);
        }

        return parent::getOutputPath();
    }

    /**
     * Get the literal contents or invoke the configured content closure.
     */
    public function getContents(): string
    {
        return $this->contents instanceof Closure
            ? ($this->contents)($this)
            : $this->contents;
    }

    /**
     * Get the Blade view key or file path, or an empty string when none is configured.
     */
    public function getBladeView(): string
    {
        return $this->view;
    }

    /**
     * Get the contents that will be saved to disk for this page.
     */
    public function compile(): string
    {
        $view = $this->getBladeView();

        if ($view === '') {
            return $this->getContents();
        }

        $data = $this->matter->toArray();

        return str_ends_with($view, '.blade.php')
            ? AnonymousViewCompiler::handle($view, $data)
            : View::make($view, $data)->render();
    }
}
