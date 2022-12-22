<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use function array_flip;
use function array_keys;
use function array_merge;
use function file_exists;
use Hyde\Console\Concerns\ValidatingCommand;
use Hyde\Framework\Actions\CreatesNewPublicationType;
use Hyde\Framework\Features\Publications\Models\PublicationField;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Framework\Features\Publications\PublicationService;
use Illuminate\Support\Str;
use InvalidArgumentException;
use function is_dir;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Collection;
use function scandir;
use function strtolower;
use function trim;

/**
 * Hyde Command to create a new publication type.
 *
 * @see \Hyde\Framework\Actions\CreatesNewPublicationType
 * @see \Hyde\Framework\Testing\Feature\Commands\MakePublicationTypeCommandTest
 */
class MakePublicationTypeCommand extends ValidatingCommand
{
    /** @var string */
    protected $signature = 'make:publicationType
		{title? : The name of the Publication Type to create. Will be used to generate the storage directory}';

    /** @var string */
    protected $description = 'Create a new publication type definition';

    public function safeHandle(): int
    {
        $this->title('Creating a new Publication Type!');

        $title = $this->argument('title');
        if (! $title) {
            $title = trim($this->askWithValidation('name', 'Publication type name', ['required', 'string']));
            $dirname = Str::slug($title);
            if (file_exists($dirname) && is_dir($dirname) && count(scandir($dirname)) > 2) {
                throw new InvalidArgumentException("Storage path [$dirname] already exists");
            }
        }

        $fields = $this->captureFieldsDefinitions();

        $sortField = $this->getSortField($fields);

        $sortAscending = $this->getSortDirection();

        $pageSize = $this->getPageSize();
        $prevNextLinks = $this->getPrevNextLinks();

        $canonicalField = $this->getCanonicalField($fields);

        $creator = new CreatesNewPublicationType($title, $fields, $canonicalField, $sortField, $sortAscending, $prevNextLinks, $pageSize, $this->output);
        $creator->create();

        $this->info('Publication type created successfully!');

        return Command::SUCCESS;
    }

    protected function captureFieldsDefinitions(): Collection
    {
        $this->output->writeln('<bg=magenta;fg=white>You now need to define the fields in your publication type:</>');
        $count = 1;
        $fields = Collection::create();
        do {
            $this->line('');
            $this->output->writeln("<bg=cyan;fg=white>Field #$count:</>");

            $fieldData = [];
            do {
                $fieldData['name'] = Str::kebab(trim($this->askWithValidation('name', 'Field name', ['required'])));
                $duplicate = $this->checkIfFieldIsDuplicate($fields, $fieldData['name']);
            } while ($duplicate);

            $type = $this->getFieldType();

            if ($type === 10) {
                $fieldData = $this->getFieldDataForTag($fieldData);
            }
            $addAnother = $this->askWithValidation('addAnother', '<bg=magenta;fg=white>Add another field (y/n)</>', ['required', 'string', 'in:y,n'], 'n');

            // map field choice to actual field type
            $fieldData['type'] = PublicationFieldTypes::values()[$type - 1];

            $fields->add(PublicationField::fromArray($fieldData));
            $count++;
        } while (strtolower($addAnother) !== 'n');

        return $fields;
    }

    protected function getFieldType(): int
    {
        $options = PublicationFieldTypes::cases();
        foreach ($options as $key => $value) {
            $options[$key] = $value->name;
        }
        $options[4] = 'Datetime (YYYY-MM-DD (HH:MM:SS))';
        $options[5] = 'URL';
        $options[8] = 'Local Image';
        $options[9] = 'Tag (select value from list)';

        return (int) $this->choice('Field type', $options, 1) + 1;
    }

    protected function getSortField(Collection $fields): string
    {
        $options = array_merge(['dateCreated (meta field)'], $fields->pluck('name')->toArray());

        $selected = $this->choice('Choose the default field you wish to sort by', $options, 'dateCreated (meta field)');

        return $selected === 'dateCreated (meta field)' ? '__createdAt' : $options[(array_flip($options)[$selected])];
    }

    protected function getSortDirection(): bool
    {
        $options = [
            'Ascending (oldest items first if sorting by dateCreated)'  => true,
            'Descending (newest items first if sorting by dateCreated)' => false,
        ];

        return $options[$this->choice('Choose the default sort direction', array_keys($options), 'Ascending (oldest items first if sorting by dateCreated)')];
    }

    protected function getPageSize(): int
    {
        return (int) $this->askWithValidation(
            'pageSize',
            'Enter the pageSize (0 for no limit)',
            ['required', 'integer', 'between:0,100'],
            25
        );
    }

    protected function getPrevNextLinks(): bool
    {
        return (bool) $this->askWithValidation(
            'prevNextLinks',
            'Generate previous/next links in detail view (y/n)',
            ['required', 'string', 'in:y,n'],
            'y'
        );
    }

    protected function getCanonicalField(Collection $fields): string
    {
        $options = $fields->reject(function (PublicationField $field): bool {
            // Temporary verbose check to see code coverage
            if ($field->type === 'image') {
                return true;
            } elseif ($field->type === 'tag') {
                return true;
            } else {
                return false;
            }
        })->pluck('name');

        return $this->choice('Choose a canonical name field (the values of this field have to be unique!)', $options->toArray(), $options->first());
    }

    protected function validateLengths(string $min, string $max): bool
    {
        if ($max < $min) {
            $this->error('Field length [max] cannot be less than [min]');

            return false;
        }

        return true;
    }

    protected function getFieldDataForTag(array $fieldData): array
    {
        $allTags = PublicationService::getAllTags();
        $offset = 1;
        foreach ($allTags as $k => $v) {
            $this->line("  $offset - $k");
            $offset++;
        }
        $offset--; // The above loop overcounts by 1
        $selected = $this->askWithValidation('tagGroup', 'Tag Group', ['required', 'integer', "between:1,$offset"], 0);
        $fieldData['tagGroup'] = $allTags->keys()->{$selected - 1};
        $fieldData['min'] = 0;
        $fieldData['max'] = 0;

        return $fieldData;
    }

    protected function checkIfFieldIsDuplicate(Collection $fields, $name): bool
    {
        $duplicate = $fields->where('name', $name)->count();
        if ($duplicate) {
            $this->error("Field name [$name] already exists!");
        }

        return (bool) $duplicate;
    }
}
