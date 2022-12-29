<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use function array_merge;
use Hyde\Framework\Actions\Concerns\CreateAction;
use Hyde\Framework\Actions\Contracts\CreateActionContract;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use function rtrim;
use RuntimeException;
use function substr;
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
    ) {
        $this->outputPath = "{$this->pubType->getDirectory()}/{$this->getFilename()}.md";
    }

    protected function handleCreate(): void
    {
        $output = "---
{$this->createFrontMatter()}
---

## Write something awesome.

";

        $this->save($output);
    }

    protected function getFilename(): string
    {
        return $this->formatStringForStorage(substr($this->getCanonicalValue(), 0, 64));
    }

    protected function getCanonicalValue(): string
    {
        if ($this->pubType->canonicalField === '__createdAt') {
            return Carbon::now()->format('Y-m-d H:i:s');
        }

        return (string) $this->fieldData->get($this->pubType->canonicalField)
            ?: throw new RuntimeException("Could not find field value for '{$this->pubType->canonicalField}' which is required as it's the type's canonical field", 404);
    }

    protected function createFrontMatter(): string
    {
        return rtrim(Yaml::dump($this->getMergedData(), flags: YAML::DUMP_MULTI_LINE_LITERAL_BLOCK));
    }

    protected function getMergedData(): array
    {
        return array_merge(['__createdAt' => Carbon::now()],
            $this->normalizeData($this->fieldData->toArray())
        );
    }

    /**
     * @internal
     *
     * @param  array<string, mixed>  $array
     * @return array<string, mixed>
     */
    public function normalizeData(array $array): array
    {
        foreach ($array as $key => $value) {
            $type = $this->pubType->getFields()->get($key);

            if ($type->type === PublicationFieldTypes::Text) {
                // In order to properly store text fields as block literals,
                // we need to make sure they end with a newline.
                $array[$key] = trim($value)."\n";
            }

            if ($type->type === PublicationFieldTypes::Integer) {
                $array[$key] = (int) $value;
            }

            if ($type->type === PublicationFieldTypes::Boolean) {
                $array[$key] = (bool) $value;
            }

            if ($type->type === PublicationFieldTypes::Float) {
                $array[$key] = (float) $value;
            }

            if ($type->type === PublicationFieldTypes::Array) {
                $array[$key] = (array) $value;
            }
        }

        return $array;
    }
}
