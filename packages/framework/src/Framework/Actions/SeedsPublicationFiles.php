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
    protected PublicationType $pubType;
    protected int $number = 1;

    protected array $matter;
    protected string $canonicalValue;
    protected Generator $faker;

    public function __construct(PublicationType $pubType, int $number = 1) {
        $this->number = $number;
        $this->pubType = $pubType;
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
            $this->matter[$field->name] = $this->generateFieldData($field);
            $this->getCanonicalFieldName($field);
        }

        if (! $this->canonicalValue) {
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

    protected function generateFieldData(PublicationField $field): string|int|float|array|bool
    {
        return match ($field->type->value) {
             'array' => $this->getArrayItems(),
             'boolean' => rand(0, 100) < 50,
             'datetime' => "{$this->getDateTimeValue()}",
             'float' => rand(-10000000, 10000000) / 100,
             'image' => 'https://picsum.photos/id/'.rand(1, 1000).'/400/400',
             'integer' => rand(-100000, 100000),
             'string' => substr($this->faker->sentence(10), 0, rand(0, 255)),
             'tag' => $this->getTags($field),
             'text' => $this->getTextValue(rand(3, 20)),
             'url' => $this->faker->url(),
        };
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
