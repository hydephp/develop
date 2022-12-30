<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use function assert;
use Hyde\Framework\Actions\Concerns\CreateAction;
use Hyde\Framework\Actions\Contracts\CreateActionContract;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\DatetimeField;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\PublicationFieldValue;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use function rtrim;
use RuntimeException;
use function substr;
use Symfony\Component\Yaml\Yaml;

/**
 * Scaffold a publication file.
 *
 * @see \Hyde\Console\Commands\MakePublicationCommand
 * @see \Hyde\Framework\Testing\Feature\Actions\CreatesNewPublicationPageTest
 */
class CreatesNewPublicationPage extends CreateAction implements CreateActionContract
{
    protected bool $force = false;
    protected Collection $fieldData;
    protected PublicationType $pubType;

    /**
     * @param  \Hyde\Framework\Features\Publications\Models\PublicationType  $pubType
     * @param  \Illuminate\Support\Collection<string, PublicationFieldValue>  $fieldData
     * @param  bool  $force
     */
    public function __construct(PublicationType $pubType, Collection $fieldData, bool $force = false)
    {
        $this->pubType = $pubType;
        $this->fieldData = $this->mergeAndValidateFieldData($fieldData);
        $this->force = $force;
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
        $canonicalFieldName = $this->pubType->canonicalField;
        if ($canonicalFieldName === '__createdAt') {
            return $this->getFieldValue('__createdAt')->getValue()->format('Y-m-d H:i:s');
        }

        if ($this->fieldData->has($canonicalFieldName)) {
            $field = $this->getFieldValue($canonicalFieldName);

            return (string) $field->getValue(); // TODO here we can check if field has interface allowing it to be canonical, else throw exception
        } else {
            return throw new RuntimeException("Could not find field value for '{$canonicalFieldName}' which is required as it's the type's canonical field", 404);
        }
    }

    protected function createFrontMatter(): string
    {
        return rtrim(Yaml::dump($this->normalizeData($this->fieldData->toArray()), flags: YAML::DUMP_MULTI_LINE_LITERAL_BLOCK));
    }

    /**
     * @param  array<string, PublicationFieldValue>  $array
     * @return array<string, mixed>
     */
    protected function normalizeData(array $array): array
    {
        foreach ($array as $key => $field) {
            $array[$key] = $field->getValue();
        }

        return $array;
    }

    protected function mergeAndValidateFieldData(Collection $fieldData): Collection
    {
        $fieldData->prepend(new DatetimeField(Carbon::now()->format('Y-m-d H:i:s')), '__createdAt');

        return $fieldData;
    }

    protected function getFieldValue(string $key): PublicationFieldValue
    {
        return $this->fieldData->get($key);
    }
}
