<?php

declare(strict_types=1);

namespace Hyde\Publications\Actions;

use Hyde\Framework\Actions\ConvertsArrayToFrontMatter;
use Hyde\Publications\Models\PublicationFieldValue;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\PublicationFieldTypes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

use function substr;

/**
 * Scaffold a publication file.
 *
 * @see \Hyde\Publications\Commands\MakePublicationCommand
 * @see \Hyde\Publications\Testing\Feature\CreatesNewPublicationPageTest
 */
class CreatesNewPublicationPage extends CreateAction
{
    protected bool $force = false;
    protected Collection $fieldData;
    protected PublicationType $publicationType;

    /**
     * @param  \Hyde\Publications\Models\PublicationType  $publicationType
     * @param  \Illuminate\Support\Collection<string, \Hyde\Publications\Models\PublicationFieldValue>  $fieldData
     * @param  bool  $force
     */
    public function __construct(PublicationType $publicationType, Collection $fieldData, bool $force = false)
    {
        $fieldData->prepend(new PublicationFieldValue(PublicationFieldTypes::Datetime, (string) Carbon::now()), '__createdAt');

        $this->publicationType = $publicationType;
        $this->fieldData = $fieldData;
        $this->force = $force;
        $this->outputPath = "{$this->publicationType->getDirectory()}/{$this->getFilename()}.md";
    }

    protected function handleCreate(): void
    {
        $this->save("{$this->createFrontMatter()}\n## Write something awesome.\n\n");
    }

    protected function getFilename(): string
    {
        return $this->formatStringForStorage(substr($this->getCanonicalValue(), 0, 64));
    }

    protected function getCanonicalValue(): string
    {
        $canonicalFieldName = $this->publicationType->canonicalField;
        if ($canonicalFieldName === '__createdAt') {
            return $this->getFieldFromCollection('__createdAt')->getValue()->format('Y-m-d H:i:s');
        }

        if ($this->fieldData->has($canonicalFieldName)) {
            $field = $this->getFieldFromCollection($canonicalFieldName);

            return (string) $field->getValue();
        } else {
            return throw new RuntimeException("Could not find field value for '$canonicalFieldName' which is required as it's the type's canonical field", 404);
        }
    }

    protected function createFrontMatter(): string
    {
        return (new ConvertsArrayToFrontMatter())->execute(
            $this->normalizeData($this->fieldData),
            flags: YAML::DUMP_MULTI_LINE_LITERAL_BLOCK
        );
    }

    /**
     * @param  Collection<string, PublicationFieldValue>  $data
     * @return array<string, mixed>
     */
    protected function normalizeData(Collection $data): array
    {
        return $data->mapWithKeys(function (PublicationFieldValue $field, string $key): array {
            return [$key => $field->getValue()];
        })->toArray();
    }

    protected function getFieldFromCollection(string $key): PublicationFieldValue
    {
        return $this->fieldData->get($key);
    }
}
