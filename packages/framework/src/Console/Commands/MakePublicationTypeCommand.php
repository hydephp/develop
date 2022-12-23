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
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function in_array;
use InvalidArgumentException;
use function is_dir;
use LaravelZero\Framework\Commands\Command;
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
		{name? : The name of the Publication Type to create. Will be used to generate the storage directory}';

    /** @var string */
    protected $description = 'Create a new publication type definition';
    protected int $count = 1;

    public function safeHandle(): int
    {
        $this->title('Creating a new Publication Type!');

        $title = $this->argument('name');
        if (! $title) {
            $title = trim($this->askWithValidation('name', 'Publication type name', ['required', 'string']));
            $dirname = Str::slug($title);
            if (file_exists($dirname) && is_dir($dirname) && count(scandir($dirname)) > 2) {
                throw new InvalidArgumentException("Storage path [$dirname] already exists");
            }
        }

        $fields = $this->captureFieldsDefinitions();

        list($sortField, $sortAscending, $pageSize, $prevNextLinks) = $this->getPaginationSettings($fields);

        $canonicalField = $this->getCanonicalField($fields);

        $creator = new CreatesNewPublicationType($title, $fields, $canonicalField->name, $sortField, $sortAscending, $prevNextLinks, $pageSize, $this->output);
        $creator->create();

        $this->info('Publication type created successfully!');

        return Command::SUCCESS;
    }

    protected function captureFieldsDefinitions(): Collection
    {
        $this->line('You now need to define the fields in your publication type:');
        $fields = Collection::make();
        do {
            $this->line('');

            $fieldData = [];
            do {
                $fieldData['name'] = Str::kebab(trim($this->askWithValidation('name', "Enter name for field #$this->count", ['required'])));
                $duplicate = $this->checkIfFieldIsDuplicate($fields, $fieldData['name']);
            } while ($duplicate);

            $type = $this->getFieldType();

            if ($type === PublicationFieldTypes::Tag) {
                $fieldData = $this->getFieldDataForTag($fieldData);
            }
            $addAnother = $this->confirm('Add another field?');

            // map field choice to actual field type
            $fieldData['type'] = $type;

            $fields->add(PublicationField::fromArray($fieldData));
            $this->count++;
        } while ($addAnother);

        return $fields;
    }

    protected function getFieldType(): PublicationFieldTypes
    {
        $options = [
            'String',
            'Datetime',
            'Boolean',
            'Integer',
            'Float',
            'Image',
            'Array',
            'Text',
            'Url',
            'Tag',
        ];

        $choice = $this->choice("Enter type for field #$this->count", $options, 'String');

        return PublicationFieldTypes::from(strtolower($choice));
    }

    protected function getCanonicalField(Collection $selectedFields): PublicationField
    {
        $options = $selectedFields->reject(function (PublicationField $field): bool {
            return in_array($field, PublicationFieldTypes::canonicable());
        })->pluck('name');

        if ($options->isEmpty()) {
            $this->warn('There are no fields that can be canonical. Defaulting to __createdAt instead.');

            return PublicationField::fromArray([
                'name' => '__createdAt',
                'type' => PublicationFieldTypes::Datetime,
            ]);
        }

        return $selectedFields->firstWhere('name',
            $this->choice('Choose a canonical name field (this will be used to generate filenames, so the values need to be unique)',
                $options->toArray(),
                $options->first()
            ));
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

    protected function getPaginationSettings(Collection $fields): array
    {
        $sortField = $this->getSortField($fields);
        $sortAscending = $this->getSortDirection();
        $pageSize = $this->getPageSize();
        $prevNextLinks = $this->getPrevNextLinks();
        return array($sortField, $sortAscending, $pageSize, $prevNextLinks);
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
        return $this->confirm('Generate previous/next links in detail view?', true);
    }
}
