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
use Rgasch\Collection\Collection;
use function Safe\file_put_contents;

/**
 * Seed publication files for a publication type.
 *
 * @see \Hyde\Console\Commands\SeedPublicationCommand
 * @see \Hyde\Framework\Testing\Feature\Actions\SeedsPublicationFilesTest
 */
class SeedsPublicationFiles extends CreateAction implements CreateActionContract
{
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
            [$matter, $canonicalValue] = $this->generatePublicationData();
            $identifier = Str::of($canonicalValue)->substr(0, 64)->slug()->toString();

            $page = new PublicationPage($identifier, $matter, '## Write something awesome.', $this->pubType);
            $page->save();
        }
    }

    protected function generatePublicationData(): array
    {
        $faker = Factory::create();
        $now = Carbon::today()->subDays(rand(1, 360))->addSeconds(rand(0, 86400));
        $canonicalFieldName = $this->pubType->canonicalField;
        $canonicalValue = '';

        $matter = [];
        $matter['__createdAt'] = "$now\n";
        /** @var \Hyde\Framework\Features\Publications\Models\PublicationField $field */
        foreach ($this->pubType->getFields() as $field) {
            $lines = [];

            switch ($field->type->value) {
                case 'array':
                    $nLines = rand(3, 20);
                    for ($i = 0; $i < $nLines; $i++) {
                        $lines[] = $faker->word();
                    }
                    $matter[$field->name] = $lines;
                    $canonicalValue = $field->name == $canonicalFieldName ? $lines[0].rand(1, 100000) : '';
                    break;
                case 'boolean':
                    $matter[$field->name] = rand(0, 100) < 50;
                    break;
                case 'datetime':
                    $value = $this->getDateTimeValue();
                    $matter[$field->name] = "$value";
                    $canonicalValue = $field->name == $canonicalFieldName ? $value : '';
                    break;
                case 'float':
                    $value = rand(-10000000, 10000000) / 100;
                    $matter[$field->name] = $value;
                    $canonicalValue = $field->name == $canonicalFieldName ? $value : '';
                    break;
                case 'image':
                    $matter[$field->name] = "https://picsum.photos/id/".(rand(1, 1000) / 100)."/400/400";
                    break;
                case 'integer':
                    $value = rand(-100000, 100000);
                    $matter[$field->name] = $value;
                    $canonicalValue = $field->name == $canonicalFieldName ? $value : '';
                    break;
                case 'string':
                    $value = Str::of($faker->sentence(10))->limit(rand(0, 255));
                    $matter[$field->name] = "$value\n";
                    $canonicalValue = $field->name == $canonicalFieldName ? $value : '';
                    break;
                case 'tag':
                    $tags = PublicationService::getValuesForTagName($field->tagGroup, false);
                    $tagValue = $tags->isEmpty() ? '' : $tags->random();
                    $matter[$field->name] = $tagValue;
                    break;
                case 'text':
                    $nLines = rand(3, 20);
                    for ($i = 0; $i < $nLines; $i++) {
                        $lines[] = $faker->sentence(rand(5, 20));
                    }
                    $matter[$field->name] = "|\n  ".implode("\n  ", $lines)."\n";
                    $canonicalValue = $field->name == $canonicalFieldName ? $lines[0].rand(1, 100000) : '';
                    break;
                case 'url':
                    $text = Str::of($faker->sentence(rand(3, 10)))->replace(' ', '+');
                    $value = 'https://google.com?q='.$text;
                    $matter[$field->name] = "$value\n";
                    $canonicalValue = $field->name == $canonicalFieldName ? $value : '';
                    break;
            }
        }

        return [$matter, $canonicalValue ?: $faker->sentence(3)];
    }

    protected function getDateTimeValue(): string
    {
        return Carbon::createFromTimestamp(rand(
                Carbon::today()->subDay()->addSeconds(rand(0, 86400))->timestamp,
                Carbon::today()->subDays(365)->addSeconds(rand(0, 86400))->timestamp
        ))->format('Y-m-d H:i:s');
    }
}
