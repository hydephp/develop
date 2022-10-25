<?php

namespace Hyde\Framework\Actions;

use Hyde\Framework\Concerns\HydePage;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Hyde;

/**
 * Converts a Page Model into a static HTML page.
 *
 * @see \Hyde\Framework\Testing\Feature\StaticPageBuilderTest
 */
class StaticPageBuilder
{
    use InteractsWithDirectories;

    /**
     * Construct the class.
     *
     * @param  \Hyde\Framework\Concerns\HydePage  $page  the Page to compile into HTML
     * @param  bool  $selfInvoke  if set to true the class will invoke when constructed
     */
    public function __construct(protected HydePage $page, bool $selfInvoke = false)
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
        Hyde::shareViewData($this->page);

        $this->needsParentDirectory(Hyde::sitePath($this->page->getOutputPath()));

        return $this->save($this->page->compile());
    }

    /**
     * Save the compiled HTML to file.
     *
     * @param  string  $contents  to save to the file
     * @return string the path to the saved file
     */
    protected function save(string $contents): string
    {
        $path = Hyde::sitePath($this->page->getOutputPath());

        file_put_contents($path, $contents);

        return $path;
    }
}
