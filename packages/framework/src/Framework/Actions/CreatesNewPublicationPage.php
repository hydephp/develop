<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use function array_merge;
use Hyde\Framework\Actions\Concerns\CreateAction;
use Hyde\Framework\Actions\Contracts\CreateActionContract;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use function trim;

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
        $fileName = $this->getFilename();
        $directory = $this->pubType->getDirectory();
        $this->outputPath = "$directory/$fileName.md";
    }

    protected function handleCreate(): void
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $output = <<<MARKDOWN
            ---
            {$this->createFrontMatter($now)}
            ---
            
            ## Write something awesome.
            
            
            MARKDOWN;

        $this->output?->writeln("Saving publication data to [$this->outputPath]");

        $this->save($output);
    }

    protected function getCanonicalValue(string $canonicalFieldName): string
    {
        if ($canonicalFieldName === '__createdAt') {
            return Carbon::now()->format('Y-m-d H:i:s');
        }

        return (string) $this->fieldData->get($canonicalFieldName)
            ?: throw new RuntimeException("Could not find field value for '$canonicalFieldName' which is required for as it's the type's canonical field", 404);
    }

    protected function createFrontMatter(string $now): string
    {
        return rtrim(Yaml::dump($this->normalizeData(array_merge(
            ['__createdAt' => $now], $this->fieldData->toArray())),
            flags: YAML::DUMP_MULTI_LINE_LITERAL_BLOCK
        ));
    }

    protected function normalizeData(array $array): array
    {
        foreach ($array as $key => $value) {
            $type = $this->pubType->getFields()->get($key);

            if ($key === '__createdAt') {
                $array[$key] = Carbon::parse($value);
            } elseif ($type?->type === PublicationFieldTypes::Text) {
                // In order to properly store text fields as block literals,
                // we need to make sure they end with a newline.
                $array[$key] = trim($value)."\n";
            }
        }

        return $array;
    }

    protected function getFilename(): string
    {
        $canonicalValue = $this->getCanonicalValue($this->pubType->canonicalField);
        $canonicalStr = Str::of($canonicalValue)->substr(0, 64);

        return $this->formatStringForStorage($canonicalStr->slug()->toString());
    }
}
