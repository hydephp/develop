<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Faker\Factory;
use Hyde\Framework\Actions\Concerns\CreateAction;
use Hyde\Framework\Actions\Contracts\CreateActionContract;
use Hyde\Framework\Features\Publications\Models\PublicationFieldType;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationService;
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
        $directory = $this->pubType->getDirectory();

        for ($i = 0; $i < $this->number; $i++) {
            $publicationData = $this->generatePublicationData();
            $output = $publicationData->output;
            $canonicalValue = $publicationData->canonicalValue;
            $slug = Str::of($canonicalValue)->substr(0, 64)->slug()->toString() ?: 'untitled';
            $fileName = "$directory/{$this->formatStringForStorage($slug)}.md";
            file_put_contents($fileName, $output);
        }
    }

    protected function generatePublicationData(): Collection
    {
        $faker = Factory::create();
        $now = Carbon::today()->subDays($faker->numberBetween(1, 360))->addSeconds($faker->numberBetween(0, 86400));
        $canonicalFieldName = $this->pubType->canonicalField;
        $canonicalValue = '';

        $output = "---\n";
        $output .= "__createdAt: $now\n";
        /** @var PublicationFieldType $field */
        foreach ($this->pubType->getFields() as $field) {
            $lines = [];

            switch ($field->type->value) {
                case 'array':
                    $nLines = $faker->numberBetween(3, 20);
                    for ($i = 0; $i < $nLines; $i++) {
                        $lines[] = $faker->word();
                    }
                    $output .= "$field->name:\n  - ".implode("\n  - ", $lines)."\n";
                    $canonicalValue = $field->name == $canonicalFieldName ? $lines[0].$faker->numberBetween(1, 100000) : '';
                    break;
                case 'boolean':
                    $value = $faker->numberBetween(0, 100) < 50 ? 'true' : 'false';
                    $output .= "$field->name: $value\n";
                    break;
                case 'datetime':
                    $min = $field->min ? Carbon::parse($field->min) : Carbon::today()->subDays(365)->addSeconds($faker->numberBetween(0, 86400));
                    $max = $field->max ? Carbon::parse($field->max) : Carbon::today()->subDays(1)->addSeconds($faker->numberBetween(0, 86400));
                    $value = Carbon::createFromTimestamp(rand($max->timestamp, $min->timestamp))->format('Y-m-d H:i:s');
                    $output .= "$field->name: $value\n";
                    $canonicalValue = $field->name == $canonicalFieldName ? $value : '';
                    break;
                case 'float':
                    $min = $field->min ?: -10000000;
                    $max = $field->max ?: 10000000;
                    $value = $faker->numberBetween($min, $max) / 100;
                    $output .= "$field->name: $value\n";
                    $canonicalValue = $field->name == $canonicalFieldName ? $value : '';
                    break;
                case 'image':
                    $min = $field->min ?: 400;
                    $max = $field->max ?: 400;
                    $id = $faker->numberBetween(1, 1000) / 100;
                    $output .= "$field->name: https://picsum.photos/id/$id/$min/$max\n";
                    break;
                case 'integer':
                    $min = $field->min ?: -100000;
                    $max = $field->max ?: 100000;
                    $value = $faker->numberBetween($min, $max);
                    $output .= "$field->name: $value\n";
                    $canonicalValue = $field->name == $canonicalFieldName ? $value : '';
                    break;
                case 'string':
                    $min = $field->min ?: 0;
                    $max = $field->max ?: 255;
                    $value = Str::of($faker->sentence(10))->limit($faker->numberBetween($min, $max), '...');
                    $output .= "$field->name: $value\n";
                    $canonicalValue = $field->name == $canonicalFieldName ? $value : '';
                    break;
                case 'tag':
                    $tagValue = PublicationService::getValuesForTagName($field->tagGroup, false)->random();
                    $output .= "$field->name: $tagValue\n";
                    break;
                case 'text':
                    $nLines = $faker->numberBetween(3, 20);
                    for ($i = 0; $i < $nLines; $i++) {
                        $lines[] = $faker->sentence($faker->numberBetween(5, 20));
                    }
                    $output .= "$field->name: |\n  ".implode("\n  ", $lines)."\n";
                    $canonicalValue = $field->name == $canonicalFieldName ? $lines[0].$faker->numberBetween(1, 100000) : '';
                    break;
                case 'url':
                    $text = Str::of($faker->sentence($faker->numberBetween(3, 10)))->replace(' ', '+');
                    $value = 'https://google.com?q='.$text;
                    $output .= "$field->name: $value\n";
                    $canonicalValue = $field->name == $canonicalFieldName ? $value : '';
                    break;
                default:
                    throw new \InvalidArgumentException(
                        "Unhandled field type [$field->type]. Possible field types are: ".implode(', ', PublicationFieldType::TYPES)
                    );
            }
        }
        $output .= "---\n";
        $output .= "\n## Write something awesome.\n\n";

        return Collection::create(['output' => $output, 'canonicalValue' => $canonicalValue]);
    }
}
