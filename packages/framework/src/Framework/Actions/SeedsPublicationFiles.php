<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Faker\Factory;
use Hyde\Framework\Actions\Concerns\CreateAction;
use Hyde\Framework\Actions\Contracts\CreateActionContract;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Pages\PublicationPage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
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
    protected \Faker\Generator $faker;

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
            [$this->matter, $this->canonicalValue] = $this->generatePublicationData();
            $identifier = Str::slug(substr($this->canonicalValue, 0, 64));

            $page = new PublicationPage($identifier, $this->matter, '## Write something awesome.', $this->pubType);
            $page->save();
        }
    }

    protected function generatePublicationData(): array
    {
        $this->faker = Factory::create();
        $now = Carbon::today()->subDays(rand(1, 360))->addSeconds(rand(0, 86400));
        $canonicalFieldName = $this->pubType->canonicalField;
        $this->canonicalValue = '';

        $this->matter = [];
        $this->matter['__createdAt'] = "$now\n";
        foreach ($this->pubType->getFields() as $field) {
            [$this->matter, $this->canonicalValue] = $this->generateFieldData($field, $canonicalFieldName);
        }

        return [$this->matter, $this->canonicalValue ?: $this->faker->sentence(3)];
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

    protected function generateFieldData(
        \Hyde\Framework\Features\Publications\Models\PublicationField $field,
    string $canonicalFieldName
    ): array {
        switch ($field->type->value) {
            case 'array':
                $arrayItems = [];
                for ($i = 0; $i < rand(3, 20); $i++) {
                    $arrayItems[] = $this->faker->word();
                }
                $this->matter[$field->name] = $arrayItems;
                $value = $arrayItems[0].'-'.rand(1, 100000);
                $this->canonicalValue = $field->name == $canonicalFieldName ? $value : '';
                break;
            case 'boolean':
                $this->matter[$field->name] = rand(0, 100) < 50;
                break;
            case 'datetime':
                $value = $this->getDateTimeValue();
                $this->matter[$field->name] = "$value";
                $this->canonicalValue = $field->name == $canonicalFieldName ? $value : '';
                break;
            case 'float':
                $value = rand(-10000000, 10000000) / 100;
                $this->matter[$field->name] = $value;
                $this->canonicalValue = $field->name == $canonicalFieldName ? $value : '';
                break;
            case 'image':
                $this->matter[$field->name] = 'https://picsum.photos/id/'.rand(1, 1000).'/400/400';
                break;
            case 'integer':
                $value = rand(-100000, 100000);
                $this->matter[$field->name] = $value;
                $this->canonicalValue = $field->name == $canonicalFieldName ? $value : '';
                break;
            case 'string':
                $value = substr($this->faker->sentence(10), 0, rand(0, 255));
                $this->matter[$field->name] = "$value\n";
                $this->canonicalValue = $field->name == $canonicalFieldName ? $value : '';
                break;
            case 'tag':
                $tags = PublicationService::getValuesForTagName($field->tagGroup, false);
                $tagValue = $tags->isEmpty() ? '' : $tags->random();
                $this->matter[$field->name] = $tagValue;
                break;
            case 'text':
                $value = $this->getTextValue(rand(3, 20));
                $this->matter[$field->name] = $value;
                $this->canonicalValue = $field->name == $canonicalFieldName ? $value : '';
                break;
            case 'url':
                $value = $this->faker->url();
                $this->matter[$field->name] = $value;
                $this->canonicalValue = $field->name == $canonicalFieldName ? $value : '';
                break;
        }

        return [$this->matter, $this->canonicalValue];
    }
}
