<?php

namespace Hyde\Framework\Actions;

use Hyde\Framework\Concerns\AbstractPage;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Hyde;

/**
 * Converts a Page Model into a static HTML page.
 *
 * @see \Hyde\Framework\Testing\Feature\StaticPageBuilderTest
 */
class StaticPageBuilder
{
    use InteractsWithDirectories;

    /** @var string The relative path to the output directory */
    public static string $outputPath;

    /**
     * Construct the class.
     *
     * @param  \Hyde\Framework\Concerns\AbstractPage|PageContract  $page  the Page to compile into HTML
     * @param  bool  $selfInvoke  if set to true the class will invoke when constructed
     */
    public function __construct(protected AbstractPage|PageContract $page, bool $selfInvoke = false)
    {
        if ($selfInvoke) {
            $this->__invoke();
        }
    }

    /**
     * Run the page builder.
     *
     * @return string
     */
    public function __invoke(): string
    {
        view()->share('page', $this->page);
        view()->share('currentPage', $this->page->getCurrentPagePath());
        view()->share('currentRoute', $this->page->getRoute());

        $this->needsDirectory(Hyde::getSiteOutputPath());
        $this->needsDirectory(dirname(Hyde::getSiteOutputPath($this->page->getOutputPath())));

        return $this->save($this->page->compile());
    }

    /**
     * Save the compiled HTML to file.
     *
     * @param  string  $contents  to save to the file
     * @return string the path to the saved file (since v0.32.x)
     */
    protected function save(string $contents): string
    {
        $path = Hyde::getSiteOutputPath($this->page->getOutputPath());

        file_put_contents($path, $contents);

        return $path;
    }
}
