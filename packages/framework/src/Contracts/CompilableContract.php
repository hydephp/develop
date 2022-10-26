<?php

declare(strict_types=1);

namespace Hyde\Framework\Contracts;

interface CompilableContract
{
    /**
     * Compile the page into static HTML.
     *
     * @return string The compiled HTML for the page.
     */
    public function compile(): string;

    /**
     * Get the path where the compiled page will be saved.
     *
     * @return string Path relative to the site output directory.
     */
    public function getOutputPath(): string;

    /**
     * Get the Blade template for the page.
     *
     * @return string Blade template/view key.
     */
    public function getBladeView(): string;
}
