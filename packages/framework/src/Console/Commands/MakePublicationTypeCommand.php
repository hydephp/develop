<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use function array_keys;
use function file_exists;
use Hyde\Console\Concerns\ValidatingCommand;
use Hyde\Framework\Actions\CreatesNewPublicationType;
use Hyde\Framework\Features\Publications\Models\PublicationField;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
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
		{name? : The name of the Publication Type to create. Will be used to generate the storage directory}
        {--use-defaults : Select the default options wherever possible}';

    /** @var string */
    protected $description = 'Create a new publication type definition';
    protected int $count = 0;

    protected Collection $fields;

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

        $this->fields = $this->captureFieldsDefinitions();

        [$sortField, $sortAscending, $pageSize, $prevNextLinks] = ($this->getPaginationSettings());

        $canonicalField = $this->getCanonicalField();

        $creator = new CreatesNewPublicationType($title, $this->fields, $canonicalField->name, $sortField, $sortAscending, $prevNextLinks, $pageSize, $this->output);
        $creator->create();

        $this->info('Publication type created successfully!');

        return Command::SUCCESS;
    }

    protected function captureFieldsDefinitions(): Collection
    {
        $this->line('You now need to define the fields in your publication type:');
        $this->fields = Collection::make();

        $this->fields->add(PublicationField::fromArray([
            'name' => '__createdAt',
            'type' => PublicationFieldTypes::Datetime,
            'normalizeName' => false,
        ]));
        $this->count++;

        do {
            $this->line('');

            $fieldData = [];
            $fieldData['name'] = $this->getFieldName();

            $type = $this->getFieldType();

            if ($type === PublicationFieldTypes::Tag) {
                $this->comment('Tip: Hyde will look for tags matching the name of the publication!');
            }

            if ($this->option('use-defaults') === true) {
                $addAnother = false;
            } else {
                $addAnother = $this->confirm("Field #$this->count added! Add another field?");
            }

            $fieldData['type'] = $type;

            $this->fields->add(PublicationField::fromArray($fieldData));
            $this->count++;
        } while ($addAnother);

        return $this->fields;
    }

    protected function getFieldName(?string $message = null): string
    {
        $selected = Str::kebab(trim($this->askWithValidation('name', $message ?? "Enter name for field #$this->count", ['required'])));

        if ($this->checkIfFieldIsDuplicate($selected)) {
            return $this->getFieldName("Try again: Enter name for field #$this->count");
        }

        return $selected;
    }

    protected function getFieldType(): PublicationFieldTypes
    {
        $options = ['String', 'Datetime', 'Boolean', 'Integer', 'Float', 'Image', 'Array', 'Text', 'Url', 'Tag'];

        $choice = $this->choice("Enter type for field #$this->count", $options, 'String');

        return PublicationFieldTypes::from(strtolower($choice));
    }

    protected function getCanonicalField(): PublicationField
    {
        $selectableFields = $this->fields->reject(function (PublicationField $field): bool {
            return in_array($field, PublicationFieldTypes::canonicable());
        });

        if ($this->option('use-defaults')) {
            return $selectableFields->first();
        }

        $options = $selectableFields->pluck('name');

        return $this->fields->firstWhere('name',
            $this->choice('Choose a canonical name field (this will be used to generate filenames, so the values need to be unique)',
                $options->toArray(),
                $options->first()
            ));
    }

    protected function checkIfFieldIsDuplicate($name): bool
    {
        if ($this->fields->where('name', $name)->count() > 0) {
            $this->error("Field name [$name] already exists!");

            return true;
        }

        return false;
    }

    protected function getPaginationSettings(): array
    {
        if ($this->option('use-defaults') || ! $this->confirm('Do you want to configure pagination settings?')) {
            return ['__createdAt', true, 25, true];
        }

        return [$this->getSortField(), $this->getSortDirection(), $this->getPageSize(), $this->getPrevNextLinks()];
    }

    protected function getSortField(): string
    {
        return $this->choice('Choose the default field you wish to sort by', $this->fields->pluck('name')->toArray(), '__dateCreated');
    }

    protected function getSortDirection(): bool
    {
        $options = [
            'Ascending'  => true,
            'Descending' => false,
        ];

        return $options[$this->choice('Choose the default sort direction', array_keys($options), 'Ascending')];
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
