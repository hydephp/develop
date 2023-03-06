<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Hyde;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Pages\Concerns\HydePage;

/**
 * Converts a Page Model into a static HTML page.
 *
 * @see \Hyde\Framework\Testing\Feature\StaticPageBuilderTest
 */
class StaticPageBuilder
{
    use InteractsWithDirectories;

    protected HydePage $page;

    /**
     * Construct a new static page builder instance.
     *
     * @param  \Hyde\Pages\Concerns\HydePage  $page  the Page to compile into HTML
     * @param  bool  $selfInvoke  if set to true the class will invoke when constructed
     */
    public function __construct(HydePage $page, bool $selfInvoke = false)
    {
        $this->page = $page;

        if ($selfInvoke) {
            $this->__invoke();
        }
    }

    /**
     * Invoke the static page builder.
     */
    public function __invoke(): string
    {
        $path = Hyde::sitePath($this->page->getOutputPath());

        Hyde::shareViewData($this->page);

        $this->needsParentDirectory($path);

        Filesystem::putContents($path, $this->page->compile());

        return $path;
    }
}
