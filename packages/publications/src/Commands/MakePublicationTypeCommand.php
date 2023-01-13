<?php

declare(strict_types=1);

namespace Hyde\Publications\Commands;

use function array_keys;
use function count;
use Hyde\Hyde;
use Hyde\Publications\Actions\CreatesNewPublicationType;
use Hyde\Publications\Models\PublicationFieldDefinition;
use Hyde\Publications\Models\PublicationTags;
use Hyde\Publications\PublicationFieldTypes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use function is_dir;
use function is_file;
use LaravelZero\Framework\Commands\Command;
use function scandir;
use function strtolower;
use function trim;

/**
 * Hyde Command to create a new publication type.
 *
 * @see \Hyde\Publications\Actions\CreatesNewPublicationType
 * @see \Hyde\Publications\Testing\Feature\MakePublicationTypeCommandTest
 */
class MakePublicationTypeCommand extends ValidatingCommand
{
    /** @var string */
    protected $signature = 'make:publicationType
		{name? : The name of the publication type to create}';

    /** @var string */
    protected $description = 'Create a new publication type definition';

    protected Collection $fields;

    public function safeHandle(): int
    {
        $this->title('Creating a new Publication Type!');

        $title = $this->getTitle();

        $this->validateStorageDirectory(Str::slug($title));

        $this->fields = $this->captureFieldsDefinitions();

        $canonicalField = $this->getCanonicalField();

        $sortField = $this->getSortField();
        $sortAscending = $this->getSortDirection();
        $pageSize = $this->getPageSize();

        $creator = new CreatesNewPublicationType($title, $this->fields, $canonicalField->name, $sortField, $sortAscending, $pageSize);
        $this->output->writeln("Saving publication data to [{$creator->getOutputPath()}]");
        $creator->create();

        $this->info('Publication type created successfully!');

        return Command::SUCCESS;
    }

    protected function getTitle(): string
    {
        return $this->argument('name') ?: trim($this->askWithValidation('name', 'Publication type name', ['required', 'string']));
    }

    protected function validateStorageDirectory(string $directoryName): void
    {
        if (is_file(Hyde::path($directoryName)) || (is_dir(Hyde::path($directoryName)) && (count(scandir($directoryName)) > 2))) {
            throw new InvalidArgumentException("Storage path [$directoryName] already exists");
        }
    }

    protected function captureFieldsDefinitions(): Collection
    {
        $this->line('You now need to define the fields in your publication type:');
        $this->fields = Collection::make();

        $this->addCreatedAtMetaField();

        do {
            $this->fields->add($this->captureFieldDefinition());

            $addAnother = $this->confirm("Field #{$this->getCount(-1)} added! Add another field?");
        } while ($addAnother);

        return $this->fields;
    }

    protected function captureFieldDefinition(): PublicationFieldDefinition
    {
        $this->line('');

        $fieldName = $this->getFieldName();

        $fieldType = $this->getFieldType();

        // TODO: Here we could collect other data like the "rules" array for the field.

        if ($fieldType === PublicationFieldTypes::Tag) {
            $tagGroup = $this->getTagGroup();

            return new PublicationFieldDefinition($fieldType, $fieldName, tagGroup: $tagGroup);
        }

        return new PublicationFieldDefinition($fieldType, $fieldName);
    }

    protected function getFieldName(?string $message = null): string
    {
        $selected = Str::kebab(trim($this->askWithValidation('name', $message ?? "Enter name for field #{$this->getCount()}", ['required'])));

        if ($this->checkIfFieldIsDuplicate($selected)) {
            return $this->getFieldName("Try again: Enter name for field #{$this->getCount()}");
        }

        return $selected;
    }

    protected function getFieldType(): PublicationFieldTypes
    {
        $options = PublicationFieldTypes::names();

        $choice = $this->choice("Enter type for field #{$this->getCount()}", $options, 'String');

        return PublicationFieldTypes::from(strtolower($choice));
    }

    protected function getTagGroup(): string
    {
        if (empty(PublicationTags::getTagGroups())) {
            $this->error('No tag groups have been added to tags.json');
            if ($this->confirm('Would you like to add some tags now?')) {
                $this->call('make:publicationTag');

                $this->newLine();
                $this->comment("Okay, we're back on track!");
            } else {
                throw new InvalidArgumentException('Can not create a tag field without any tag groups defined in tags.json');
            }
        }

        return $this->choice("Enter tag group for field #{$this->getCount()}", PublicationTags::getTagGroups());
    }

    protected function getCanonicalField(): PublicationFieldDefinition
    {
        $selectableFields = $this->fields->reject(function (PublicationFieldDefinition $field): bool {
            return ! in_array($field->type, PublicationFieldTypes::canonicable());
        });

        $options = $selectableFields->pluck('name');

        $selected = $this->choice('Choose a canonical name field <fg=gray>(this will be used to generate filenames, so the values need to be unique)</>',
            $options->toArray(),
            $options->first()
        );

        return $this->fields->firstWhere('name', $selected);
    }

    protected function checkIfFieldIsDuplicate($name): bool
    {
        if ($this->fields->where('name', $name)->count() > 0) {
            $this->error("Field name [$name] already exists!");

            return true;
        }

        return false;
    }

    protected function addCreatedAtMetaField(): void
    {
        $this->fields->add(new PublicationFieldDefinition(PublicationFieldTypes::Datetime, '__createdAt'));
    }

    protected function getSortField(): string
    {
        return $this->choice('Choose the field you wish to sort by', $this->fields->pluck('name')->toArray(), 0);
    }

    protected function getSortDirection(): bool
    {
        $options = ['Ascending' => true, 'Descending' => false];

        return $options[$this->choice('Choose the sort direction', array_keys($options), 'Ascending')];
    }

    protected function getPageSize(): int
    {
        return (int) $this->askWithValidation('pageSize',
            'How many links should be shown on the listing page? <fg=gray>(any value above 0 will enable <href=https://docs.hydephp.com/search?query=pagination>pagination</>)</>',
            ['required', 'integer', 'between:0,100'],
            0
        );
    }

    protected function getCount(int $offset = 0): int
    {
        return $this->fields->count() + $offset;
    }
}
