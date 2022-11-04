<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Actions\Interfaces\CreateActionInterface;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Illuminate\Support\Str;

/**
 * Scaffold a new Markdown, Blade, or documentation page.
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\CreatesNewPageSourceFileTest
 */
class CreatesNewPublicationFile implements CreateActionInterface
{
    use InteractsWithDirectories;

    public function __construct(
        protected \stdclass $pubType,
        protected array $fieldData
    ) {
    }

    public function create(): string|bool
    {
        $dir = dirname($this->pubType->file);
        @mkdir($dir);
        $canonical = Str::camel($this->fieldData[$this->pubType->canonicalField]);
        $slug      = Str::slug($this->fieldData[$this->pubType->canonicalField]);
        $outFile   = "$dir/$canonical.md";

        $output = "---\n";
        $output .= "__canonical: {$canonical}\n";
        $output .= "__slug: {$slug}\n";
        foreach ($this->fieldData as $k => $v) {
            $output .= "{$k}: {$v}\n";
        }
        $output .= "---\n";
        $output .= "Raw MD text ...\n";

        print "Saving page data to [$outFile]\n";
        return (bool)file_put_contents($outFile, $output);
    }
}
