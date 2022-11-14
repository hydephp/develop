<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Actions\Interfaces\CreateActionInterface;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\HydeHelper;
use Illuminate\Support\Str;
use Rgasch\Collection\Collection;

use function Safe\date;
use function Safe\file_put_contents;

/**
 * Scaffold a new Markdown, Blade, or documentation page.
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\CreatesNewPageSourceFileTest
 */
class CreatesNewPublicationFile implements CreateActionInterface
{
    use InteractsWithDirectories;

    protected string $result;

    public function __construct(
        protected Collection $pubType,
        protected Collection $fieldData
    ) {
    }

    public function create(): void
    {
        $dir      = dirname($this->pubType->schemaFile);
        $slug     = Str::of($this->fieldData[$this->pubType->canonicalField])->substr(0, 64)->slug()->toString();
        $fileName = HydeHelper::formatNameForStorage($slug);
        $outFile  = "$dir/$fileName.md";

        $now    = date('Y-m-d H:i:s');
        $output = "---\n";
        $output .= "__canonical: {$fileName}\n";
        $output .= "__createdAt: {$now}\n";
        foreach ($this->fieldData as $k => $v) {
            $field = $this->pubType->fields->where('name', $k)->first();
            if ($field->type !== 'text') {
                $output .= "{$k}: {$v}\n";
                continue;
            }

            // Text fields have different syntax
            $output .= "{$k}: |\n";
            foreach ($v as $line) {
                $output .= "  $line\n";
            }
        }
        $output .= "---\n";
        $output .= "Raw MD text ...\n";

        $this->result = $output;
        print "Saving publication data to [$outFile]\n";

        file_put_contents($outFile, $output);
    }

    public function getResult(): string
    {
        return $this->result;
    }
}
