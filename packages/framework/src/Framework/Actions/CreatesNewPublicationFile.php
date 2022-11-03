<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Concerns\InteractsWithDirectories;
use Illuminate\Support\Str;

/**
 * Scaffold a new Markdown, Blade, or documentation page.
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\CreatesNewPageSourceFileTest
 */
class CreatesNewPublicationFile
{
    use InteractsWithDirectories;

    public function __construct(
        protected \stdclass $pubType,
        protected array $fieldData
    ) {
        $this->createPage();
    }

    protected function createPage(): int|false
    {
        $dir = dirname($this->pubType->file);
        @mkdir($dir);
        $canonical = Str::camel($this->fieldData[$this->pubType->canonicalField]);
        $slug      = Str::slug($this->fieldData[$this->pubType->canonicalField]);

        $output = "---\n";
        $output .= "__canonical: {$canonical}\n";
        $output .= "__slug: {$slug}\n";
        foreach ($this->fieldData as $k => $v) {
            $output .= "{$k}: {$v}\n";
        }
        $output .= "---\n";
        $output .= "Raw MD text ...\n";

        return file_put_contents("$dir/$canonical.md", $output);
    }
}
