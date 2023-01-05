<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Hyde\Pages\Concerns\HydePage;

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
class VirtualPage extends HydePage
{
    protected string $contents;

    public static string $sourceDirectory = '';
    public static string $outputDirectory = '';
    public static string $fileExtension = '';

    public function __construct(string $identifier, string $contents)
    {
        parent::__construct($identifier);

        $this->contents = $contents;
    }

    public function contents(): string
    {
        return $this->contents;
    }

    public function compile(): string
    {
        return $this->contents();
    }
}
