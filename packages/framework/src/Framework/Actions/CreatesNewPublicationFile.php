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
        protected Collection $fieldData,
        protected bool $force = false
    ) {
    }

    public function create(): void
    {
        $dir = dirname($this->pubType->schemaFile);
        $canonicalFieldName = $this->pubType->canonicalField;
        $canonicalFieldDef = $this->pubType->fields->filter(fn ($f) => $f->name === $canonicalFieldName)->first();
        $canonicalValue = $canonicalFieldDef->type != 'array' ? $this->fieldData->{$canonicalFieldName} : $this->fieldData->{$canonicalFieldName}[0];
        $canonicalStr = Str::of($canonicalValue)->substr(0, 64);
        $slug = $canonicalStr->slug()->toString();
        $fileName = HydeHelper::formatNameForStorage($slug);
        $outFile = "$dir/$fileName.md";
        if (file_exists($outFile) && ! $this->force) {
            throw new \InvalidArgumentException("File [$outFile] already exists");
        }

        $now = date('Y-m-d H:i:s');
        $output = "---\n";
        $output .= "__createdAt: {$now}\n";
        foreach ($this->fieldData as $k => $v) {
            $field = $this->pubType->fields->where('name', $k)->first();

            if ($field->type == 'text') {
                $output .= "{$k}: |\n";
                foreach ($v as $line) {
                    $output .= "  $line\n";
                }
                continue;
            }

            if ($field->type == 'array') {
                $output .= "{$k}:\n";
                foreach ($v as $item) {
                    $output .= "  - \"$item\"\n";
                }
                continue;
            }

            $output .= "{$k}: {$v}\n";
        }
        $output .= "---\n";
        $output .= "Raw MD text ...\n";

        $this->result = $output;
        echo "Saving publication data to [$outFile]\n";

        file_put_contents($outFile, $output);
    }

    public function getResult(): string
    {
        return $this->result;
    }
}
