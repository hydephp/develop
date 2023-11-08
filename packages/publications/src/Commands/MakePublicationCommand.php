<?php

declare(strict_types=1);

namespace Hyde\Publications\Commands;

use Hyde\Hyde;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use Hyde\Publications\Publications;
use LaravelZero\Framework\Commands\Command;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\Models\PublicationFieldValue;
use Hyde\Publications\Concerns\PublicationFieldTypes;
use Hyde\Publications\Actions\CreatesNewPublicationPage;
use Hyde\Publications\Models\PublicationFieldDefinition;
use Hyde\Publications\Commands\Helpers\InputStreamHandler;

use function array_map;
use function array_merge;
use function explode;
use function implode;
use function sprintf;
use function in_array;
use function str_starts_with;

/**
 * Hyde Command to create a new publication for a given publication type.
 *
 * @see \Hyde\Publications\Actions\CreatesNewPublicationPage
 * @see \Hyde\Publications\Testing\Feature\MakePublicationCommandTest
 */
class MakePublicationCommand extends ValidatingCommand
{
    /** @var string */
    protected $signature = 'make:publication
		{publicationType? : The name of the publication type to create a publication for}
        {--force : Should the generated file overwrite existing publications with the same filename?}';

    /** @var string */
    protected $description = 'Create a new publication item';

    protected PublicationType $publicationType;

    /** @var \Illuminate\Support\Collection<string, \Hyde\Publications\Models\PublicationType> */
    protected Collection $fieldData;

    public function safeHandle(): int
    {
        $this->title('Creating a new publication!');

        $this->publicationType = $this->getPublicationTypeSelection();
        $this->fieldData = new Collection();

        $this->collectFieldData();

        $creator = new CreatesNewPublicationPage($this->publicationType, $this->fieldData, (bool) $this->option('force'));
        if ($creator->hasFileConflict()) {
            $this->error('Error: A publication already exists with the same canonical field value');
            if ($this->confirm('Do you wish to overwrite the existing file?')) {
                $creator->force();
            } else {
                $this->info('Exiting without overwriting existing publication file!');

                return ValidatingCommand::USER_EXIT;
            }
        }
        $creator->create();

        $this->infoComment("All done! Created file [{$creator->getOutputPath()}]");

        return Command::SUCCESS;
    }

    protected function getPublicationTypeSelection(): PublicationType
    {
        $publicationTypes = Publications::getPublicationTypes();
        if ($this->argument('publicationType')) {
            $publicationTypeSelection = $this->argument('publicationType');
        } else {
            if ($publicationTypes->isEmpty()) {
                throw new InvalidArgumentException('Unable to locate any publication types. Did you create any?');
            }

            $publicationTypeSelection = $this->choice(
                'Which publication type would you like to create a publication item for?',
                $publicationTypes->keys()->toArray()
            );
        }

        if ($publicationTypes->has($publicationTypeSelection)) {
            $this->line("<info>Creating a new publication of type</info> [<comment>$publicationTypeSelection</comment>]");

            return $publicationTypes->get($publicationTypeSelection);
        }

        throw new InvalidArgumentException("Unable to locate publication type [$publicationTypeSelection]");
    }

    protected function collectFieldData(): void
    {
        $this->newLine();
        $this->info('Now please enter the field data:');

        /** @var \Hyde\Publications\Models\PublicationFieldDefinition $field */
        foreach ($this->publicationType->getFields() as $field) {
            if (str_starts_with($field->name, '__')) {
                continue;
            }

            $this->newLine();
            $fieldInput = $this->captureFieldInput($field);
            if (empty($fieldInput)) {
                $this->line("<fg=gray> > Skipping field $field->name</>");
            } else {
                $this->fieldData->put($field->name, $fieldInput);
            }
        }

        $this->newLine();
    }

    protected function captureFieldInput(PublicationFieldDefinition $field): ?PublicationFieldValue
    {
        return match ($field->type) {
            PublicationFieldTypes::Text => $this->captureTextFieldInput($field),
            PublicationFieldTypes::Array => $this->captureArrayFieldInput($field),
            PublicationFieldTypes::Media => $this->captureMediaFieldInput($field),
            PublicationFieldTypes::Tag => $this->captureTagFieldInput($field),
            default => $this->captureOtherFieldInput($field),
        };
    }

    protected function captureTextFieldInput(PublicationFieldDefinition $field): PublicationFieldValue
    {
        $this->infoComment(sprintf("Enter lines for field [$field->name] (%s)", InputStreamHandler::terminationMessage()));

        return new PublicationFieldValue(PublicationFieldTypes::Text, implode("\n", InputStreamHandler::call()));
    }

    protected function captureArrayFieldInput(PublicationFieldDefinition $field): PublicationFieldValue
    {
        $this->infoComment(sprintf("Enter values for field [$field->name] (%s)", InputStreamHandler::terminationMessage()));

        return new PublicationFieldValue(PublicationFieldTypes::Array, InputStreamHandler::call());
    }

    protected function captureMediaFieldInput(PublicationFieldDefinition $field): ?PublicationFieldValue
    {
        $this->infoComment("Select file for image field [$field->name]");

        $mediaFiles = Publications::getMediaForType($this->publicationType);
        if ($mediaFiles->isEmpty()) {
            return $this->handleEmptyMediaFilesCollection($field);
        }

        return new PublicationFieldValue(PublicationFieldTypes::Media, $this->choice('Which file would you like to use?', $mediaFiles->toArray()));
    }

    /** @return null */
    protected function handleEmptyMediaFilesCollection(PublicationFieldDefinition $field)
    {
        $directory = sprintf('directory %s/%s/', Hyde::getMediaDirectory(),
            $this->publicationType->getIdentifier()
        );

        if (in_array('required', $field->rules)) {
            throw new InvalidArgumentException("Unable to create publication as no media files were found in $directory");
        }

        $this->newLine();
        $this->warn("<fg=red>Warning:</> No media files found in $directory");
        if ($this->confirm('Would you like to skip this field?', true)) {
            // We could have a choice here, where 0 skips, and 1 reloads the media files
            return null;
        } else {
            throw new InvalidArgumentException('Unable to locate any media files for this publication type');
        }
    }

    protected function captureTagFieldInput(PublicationFieldDefinition $field): ?PublicationFieldValue
    {
        $this->infoComment("Select one or more tags for field [$field->name]");

        $existingTags = Publications::getPublicationTags();

        if ($existingTags) {
            $choice = $this->choice(/** @lang Text */ 'Select from existing or', array_merge([
                '<comment>Add new tag</comment>',
            ], $existingTags), 0, multiple: true);

            $addNew = (is_array($choice) ? $choice[0] : $choice) === '<comment>Add new tag</comment>';
        } else {
            $addNew = true;
        }

        if ($addNew) {
            $choice = $this->askWithCompletion('Enter tag(s) <fg=gray>(multiple tags separated by commas)</>',
                $existingTags);

            $choice = $this->parseCommaSeparatedValues($choice);
        }

        return new PublicationFieldValue(PublicationFieldTypes::Tag, $choice);
    }

    protected function captureOtherFieldInput(PublicationFieldDefinition $field): ?PublicationFieldValue
    {
        $selection = $this->askForFieldData($field->name, $field->getRules());
        if (empty($selection)) {
            return null;
        }

        return new PublicationFieldValue($field->type, $selection);
    }

    protected function askForFieldData(string $name, array $rules): string
    {
        return $this->askWithValidation($name, "Enter data for field </>[<comment>$name</comment>]", $rules);
    }

    protected function parseCommaSeparatedValues(string $choice): array
    {
        return array_map('trim', explode(',', $choice));
    }
}
