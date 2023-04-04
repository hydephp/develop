<?php

declare(strict_types=1);

namespace Hyde\Publications\Commands;

use Hyde\Hyde;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;
use Hyde\Publications\Concerns\PublicationFieldTypes;
use Hyde\Publications\Actions\CreatesNewPublicationType;
use Hyde\Publications\Models\PublicationFieldDefinition;

use function trim;
use function count;
use function is_dir;
use function is_file;
use function scandir;
use function sprintf;
use function in_array;
use function array_keys;
use function strtolower;

/**
 * Hyde Command to create a new publication type.
 *
 * @see \Hyde\Publications\Actions\CreatesNewPublicationType
 * @see \Hyde\Publications\Testing\Feature\MakePublicationTypeCommandTest
 */
class MakePublicationTypeCommand extends ValidatingCommand
{
    /** @var string */
    protected $signature = 'make:publicationType {name? : The name of the publication type to create}';

    /** @var string */
    protected $description = 'Create a new publication type';

    protected Collection $fields;

    public function safeHandle(): int
    {
        $this->title('Creating a new Publication Type!');

        $title = $this->getTitle();
        $this->validateStorageDirectory(Str::slug($title));

        $this->captureFieldsDefinitions();

        $canonicalField = $this->getCanonicalField();
        $sortField = $this->getSortField();
        $sortAscending = $this->getSortDirection();
        $pageSize = $this->getPageSize();

        if ($this->fields->first()->name === '__createdAt') {
            $this->fields->shift();
        }

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
        $path = Hyde::path($directoryName);
        if (is_file($path) || (is_dir($path) && (count(scandir($path)) > 2))) {
            throw new InvalidArgumentException("Storage path [$directoryName] already exists");
        }
    }

    protected function captureFieldsDefinitions(): void
    {
        $this->line('Now please define the fields for your publication type:');

        $this->fields = Collection::make([
            new PublicationFieldDefinition(PublicationFieldTypes::Datetime, '__createdAt'),
        ]);

        do {
            $this->fields->add($this->captureFieldDefinition());

            $addAnother = $this->confirm(sprintf('Field #%d added! Add another field?', $this->getCount() - 1), $this->input->isInteractive());
        } while ($addAnother);
    }

    protected function captureFieldDefinition(): PublicationFieldDefinition
    {
        $this->line('');

        $fieldName = $this->getFieldName();

        $fieldType = $this->getFieldType();

        if ($fieldType === PublicationFieldTypes::Tag) {
            return new PublicationFieldDefinition($fieldType, $fieldName);
        }

        return new PublicationFieldDefinition($fieldType, $fieldName);
    }

    protected function getFieldName(?string $message = null): string
    {
        $message ??= "Enter name for field #{$this->getCount()}";
        $default = $this->input->isInteractive() ? null : 'Example Field';

        $selected = Str::kebab(trim($this->askWithValidation(
            'name', $message, ['required'], default: $default
        )));

        if ($this->checkIfFieldIsDuplicate($selected)) {
            return $this->getFieldName("Try again: Enter name for field #{$this->getCount()}");
        }

        return $selected;
    }

    protected function getFieldType(): PublicationFieldTypes
    {
        return PublicationFieldTypes::from(strtolower($this->choice(
            "Enter type for field #{$this->getCount()}",
            PublicationFieldTypes::names(),
            'String'
        )));
    }

    protected function getCanonicalField(): PublicationFieldDefinition
    {
        $options = $this->availableCanonicableFieldNames();

        return $this->fields->firstWhere('name', $this->choice(
            'Choose a canonical name field <fg=gray>(this will be used to generate filenames, so the values need to be unique)</>',
            $options->toArray(),
            $options->first()
        ));
    }

    protected function getSortField(): string
    {
        return $this->choice('Choose the field you wish to sort by', $this->availableCanonicableFieldNames()->toArray(), 0);
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

    protected function checkIfFieldIsDuplicate($name): bool
    {
        if ($this->fields->where('name', $name)->count() > 0) {
            $this->error("Field name [$name] already exists!");

            return true;
        }

        return false;
    }

    protected function getCount(): int
    {
        return $this->fields->count();
    }

    protected function availableCanonicableFieldNames(): Collection
    {
        return $this->fields->reject(function (PublicationFieldDefinition $field): bool {
            return ! in_array($field->type, PublicationFieldTypes::canonicable());
        })->pluck('name');
    }
}
