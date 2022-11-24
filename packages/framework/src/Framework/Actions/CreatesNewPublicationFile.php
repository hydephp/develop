<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Actions\Concerns\CreateAction;
use Hyde\Framework\Actions\Contracts\CreateActionContract;
use Hyde\Framework\Features\Publications\Models\PublicationFieldType;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Rgasch\Collection\Collection;
use RuntimeException;

/**
 * Scaffold a publication file.
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\CreatesNewPublicationFileTest
 */
class CreatesNewPublicationFile extends CreateAction implements CreateActionContract
{
    protected string $result;

    public function __construct(
        protected PublicationType $pubType,
        protected Collection $fieldData,
        protected bool $force = false,
        protected ?OutputStyle $output = null,
    ) {
        $dir = ($this->pubType->getDirectory());
        $canonicalFieldName = $this->pubType->canonicalField;
        $canonicalFieldDefinition = $this->pubType->getFields()->filter(fn (PublicationFieldType $field): bool => $field->name === $canonicalFieldName)->first() ?? throw new RuntimeException("Could not find field definition for '$canonicalFieldName'");
        $canonicalValue = $canonicalFieldDefinition->type !== 'array' ? $this->fieldData->{$canonicalFieldName} : $this->fieldData->{$canonicalFieldName}[0];
        $canonicalStr = Str::of($canonicalValue)->substr(0, 64);

        $slug = $canonicalStr->slug()->toString();
        $fileName = $this->formatStringForStorage($slug);

        $outFile = ("$dir/$fileName.md");
        $this->outputPath = $outFile;
    }

    protected function handleCreate(): void
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $output = "---\n";
        $output .= "__createdAt: $now\n";
        foreach ($this->fieldData as $name => $value) {
            /** @var PublicationFieldType $fieldDefinition */
            $fieldDefinition = $this->pubType->getFields()->where('name', $name)->firstOrFail();

            if ($fieldDefinition->type == 'text') {
                $output .= "$name: |\n";
                foreach ($value as $line) {
                    $output .= "  $line\n";
                }
                continue;
            }

            if ($fieldDefinition->type == 'array') {
                $output .= "$name:\n";
                foreach ($value as $item) {
                    $output .= "  - \"$item\"\n";
                }
                continue;
            }

            $output .= "$name: $value\n";
        }
        $output .= "---\n";
        $output .= "Raw MD text ...\n";

        $this->result = $output;
        $this->output?->writeln(sprintf('Saving publication data to [%s]', $this->getOutputPath()));

        $this->save($output);
    }

    public function getResult(): string
    {
        return $this->result;
    }
}
