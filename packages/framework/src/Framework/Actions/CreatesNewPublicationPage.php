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
 * @see \Hyde\Console\Commands\MakePublicationCommand
 * @see \Hyde\Framework\Testing\Feature\Actions\CreatesNewPublicationPageTest
 */
class CreatesNewPublicationPage extends CreateAction implements CreateActionContract
{
    public function __construct(
        protected PublicationType $pubType,
        protected Collection $fieldData,
        protected bool $force = false,
        protected ?OutputStyle $output = null,
    ) {
        $canonicalFieldName = $this->pubType->canonicalField;
        $canonicalFieldDefinition = $this->pubType->getFields()->filter(fn (PublicationFieldType $field): bool => $field->name === $canonicalFieldName)->first() ?? $this->handleMissingCanonicalField(
            $canonicalFieldName
        );
        $canonicalValue = $canonicalFieldDefinition->type !== 'array' ? $this->fieldData->{$canonicalFieldName} : $this->fieldData->{$canonicalFieldName}[0];
        $canonicalStr = Str::of($canonicalValue)->substr(0, 64);

        $fileName = $this->formatStringForStorage($canonicalStr->slug()->toString());
        $directory = $this->pubType->getDirectory();
        $this->outputPath = "$directory/$fileName.md";
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
        $output .= "\n## Write something awesome.\n\n";

        $this->output?->writeln("Saving publication data to [$this->outputPath]");

        $this->save($output);
    }

    protected function handleMissingCanonicalField(string $canonicalFieldName)
    {
        return throw new RuntimeException(
            "Could not find field definition for '$canonicalFieldName'"
        );
    }
}
