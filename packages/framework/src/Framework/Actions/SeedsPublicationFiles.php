<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Faker\Factory;
use Faker\Generator;
use Hyde\Framework\Actions\Concerns\CreateAction;
use Hyde\Framework\Actions\Contracts\CreateActionContract;
use Hyde\Framework\Features\Publications\Models\PublicationField;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Pages\PublicationPage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

use function in_array;
use function rand;
use function substr;
use function time;

/**
 * Seed publication files for a publication type.
 *
 * @see \Hyde\Console\Commands\SeedPublicationCommand
 * @see \Hyde\Framework\Testing\Feature\Actions\SeedsPublicationFilesTest
 */
class SeedsPublicationFiles extends CreateAction implements CreateActionContract
{
    protected array $matter;
    protected string $canonicalValue;
    protected Generator $faker;

    public function __construct(
        protected PublicationType $pubType,
        protected int $number = 1
    ) {
    }

    protected function handleCreate(): void
    {
        $this->create();
    }

    public function create(): void
    {
        for ($i = 0; $i < $this->number; $i++) {
            $this->matter = [];
            $this->canonicalValue = '';

            $this->generatePublicationData();
            $identifier = Str::slug(substr($this->canonicalValue, 0, 64));

            $page = new PublicationPage($identifier, $this->matter, '## Write something awesome.', $this->pubType);
            $page->save();
        }
    }

    protected function generatePublicationData(): void
    {
        $this->faker = Factory::create();
        $now = Carbon::today()->subDays(rand(1, 360))->addSeconds(rand(0, 86400));

        $this->matter['__createdAt'] = "$now\n";
        foreach ($this->pubType->getFields() as $field) {
            $this->generateFieldData($field);
            $this->getCanonicalFieldName($field);
        }

        if (!$this->canonicalValue) {
            $this->canonicalValue = $this->faker->sentence(3);
        }
    }

    protected function getDateTimeValue(): string
    {
        return date('Y-m-d H:i:s', rand(
            time() - 86400 + (rand(0, 86400)),
            time() - (86400 * 365) + (rand(0, 86400))
        ));
    }

    protected function getTextValue($lines): string
    {
        $value = '';

        for ($i = 0; $i < $lines; $i++) {
            $value .= $this->faker->sentence(rand(5, 20))."\n";
        }

        return $value;
    }

    protected function generateFieldData(PublicationField $field): void {
        switch ($field->type->value) {
            case 'array':
                $this->matter[$field->name] = $this->getArrayItems();
                break;
            case 'boolean':
                $this->matter[$field->name] = rand(0, 100) < 50;
                break;
            case 'datetime':
                $this->matter[$field->name] = "{$this->getDateTimeValue()}";
                break;
            case 'float':
                $this->matter[$field->name] = rand(-10000000, 10000000) / 100;
                break;
            case 'image':
                $this->matter[$field->name] = 'https://picsum.photos/id/'.rand(1, 1000).'/400/400';
                break;
            case 'integer':
                $this->matter[$field->name] = rand(-100000, 100000);
                break;
            case 'string':
                $this->matter[$field->name] = substr($this->faker->sentence(10), 0, rand(0, 255));
                break;
            case 'tag':
                $this->matter[$field->name] = $this->getTags($field);
                break;
            case 'text':
                $this->matter[$field->name] = $this->getTextValue(rand(3, 20));
                break;
            case 'url':
                $this->matter[$field->name] = $this->faker->url();
                break;
        }
    }

    protected function getCanonicalFieldName(PublicationField $field): void
    {
        if ($this->canFieldTypeCanBeCanonical($field->type->value)) {
            if ($field->name === $this->pubType->canonicalField) {
                $this->canonicalValue = $this->matter[$field->name];
            }
        }
    }

    protected function canFieldTypeCanBeCanonical(string $value): bool
    {
        return in_array($value, ['url', 'text', 'string', 'integer', 'float', 'datetime', 'array']);
    }

    protected function getArrayItems(): array
    {
        $arrayItems = [];
        for ($i = 0; $i < rand(3, 20); $i++) {
            $arrayItems[] = $this->faker->word();
        }
        return $arrayItems;
    }

    protected function getTags(PublicationField $field): string
    {
        $tags = PublicationService::getValuesForTagName($field->tagGroup, false);
        return $tags->isEmpty() ? '' : $tags->random();
    }
}
