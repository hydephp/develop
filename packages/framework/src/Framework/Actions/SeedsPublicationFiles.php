<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Features\Publications\Models\PublicationField;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Pages\PublicationPage;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use function in_array;
use function mt_getrandmax;
use function mt_rand;
use function rand;
use function substr;
use function time;
use function trim;
use function ucfirst;

/**
 * Seed publication files for a publication type.
 *
 * @internal This class is not part of the public API and does not adhere to the BC promise.
 *
 * @see \Hyde\Console\Commands\SeedPublicationCommand
 * @see \Hyde\Framework\Testing\Feature\Actions\SeedsPublicationFilesTest
 */
class SeedsPublicationFiles
{
    protected PublicationType $pubType;
    protected int $number = 1;

    protected array $matter;
    protected string $canonicalValue;

    public function __construct(PublicationType $pubType, int $number = 1)
    {
        $this->number = $number;
        $this->pubType = $pubType;
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
        $this->matter['__createdAt'] = Carbon::today()->subDays(rand(1, 360))->addSeconds(rand(0, 86400));
        foreach ($this->pubType->getFields() as $field) {
            $this->matter[$field->name] = $this->generateFieldData($field);
            $this->getCanonicalFieldName($field);
        }

        if (! $this->canonicalValue) {
            $this->canonicalValue = $this->fakeSentence(3);
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
            $value .= $this->fakeSentence(rand(5, 20))."\n";
        }

        return $value;
    }

    protected function generateFieldData(PublicationField $field): string|int|float|array|bool
    {
        return match ($field->type->value) {
            'array' => $this->getArrayItems(),
            'boolean' => rand(0, 100) < 50,
            'datetime' => $this->getDateTimeValue(),
            'float' => ((mt_rand() / mt_getrandmax()) * (200000)) + -100000,
            'image' => 'https://picsum.photos/id/'.rand(1, 1000).'/400/400',
            'integer' => rand(-100000, 100000),
            'string' => substr($this->fakeSentence(10), 0, rand(0, 255)),
            'tag' => $this->getTags(),
            'text' => $this->getTextValue(rand(3, 20)),
            'url' => $this->fakeUrl(),
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
            $arrayItems[] = $this->fakeWord();
        }

        return $arrayItems;
    }

    protected function getTags(): string
    {
        $tags = PublicationService::getValuesForTagName($this->pubType->getIdentifier(), false);

        return $tags->isEmpty() ? '' : $tags->random();
    }

    private const WORDS = [
        'lorem',        'ipsum',       'dolor',        'sit',
        'amet',         'consectetur', 'adipiscing',   'elit',
        'a',            'ac',          'accumsan',     'ad',
        'aenean',       'aliquam',     'aliquet',      'ante',
        'aptent',       'arcu',        'at',           'auctor',
        'augue',        'bibendum',    'blandit',      'class',
        'commodo',      'condimentum', 'congue',       'consequat',
        'conubia',      'convallis',   'cras',         'cubilia',
        'cum',          'curabitur',   'curae',        'cursus',
        'dapibus',      'diam',        'dictum',       'dictumst',
        'dignissim',    'dis',         'donec',        'dui',
        'duis',         'egestas',     'eget',         'eleifend',
        'elementum',    'enim',        'erat',         'eros',
        'est',          'et',          'etiam',        'eu',
        'euismod',      'facilisi',    'facilisis',    'fames',
        'faucibus',     'felis',       'fermentum',    'feugiat',
        'fringilla',    'fusce',       'gravida',      'habitant',
        'habitasse',    'hac',         'hendrerit',    'himenaeos',
        'iaculis',      'id',          'imperdiet',    'in',
        'inceptos',     'integer',     'interdum',     'justo',
        'lacinia',      'lacus',       'laoreet',      'lectus',
        'leo',          'libero',      'ligula',       'litora',
        'lobortis',     'luctus',      'maecenas',     'magna',
        'magnis',       'malesuada',   'massa',        'mattis',
        'mauris',       'metus',       'mi',           'molestie',
        'mollis',       'montes',      'morbi',        'mus',
        'nam',          'nascetur',    'natoque',      'nec',
        'neque',        'netus',       'nibh',         'nisi',
        'nisl',         'non',         'nostra',       'nulla',
        'nullam',       'nunc',        'odio',         'orci',
        'ornare',       'parturient',  'pellentesque', 'penatibus',
        'per',          'pharetra',    'phasellus',    'placerat',
        'platea',       'porta',       'porttitor',    'posuere',
        'potenti',      'praesent',    'pretium',      'primis',
        'proin',        'pulvinar',    'purus',        'quam',
        'quis',         'quisque',     'rhoncus',      'ridiculus',
        'risus',        'rutrum',      'sagittis',     'sapien',
        'scelerisque',  'sed',         'sem',          'semper',
        'senectus',     'sociis',      'sociosqu',     'sodales',
        'sollicitudin', 'suscipit',    'suspendisse',  'taciti',
        'tellus',       'tempor',      'tempus',       'tincidunt',
        'torquent',     'tortor',      'tristique',    'turpis',
        'ullamcorper',  'ultrices',    'ultricies',    'urna',
        'ut',           'varius',      'vehicula',     'vel',
        'velit',        'venenatis',   'vestibulum',   'vitae',
        'vivamus',      'viverra',     'volutpat',     'vulputate',
    ];

    private function fakeSentence(int $words): string
    {
        $sentence = '';
        for ($i = 0; $i < $words; $i++) {
            $sentence .= $this->fakeWord().' ';
        }

        return ucfirst(trim($sentence)).'.';
    }

    private function fakeWord(): string
    {
        return Arr::random(self::WORDS);
    }

    private function fakeUrl(): string
    {
        return 'https://example.com/'.$this->fakeWord();
    }
}
