<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use function array_flip;
use Closure;
use Hyde\Console\Commands\Helpers\InputStreamHandler;
use Hyde\Console\Concerns\ValidatingCommand;
use Hyde\Framework\Actions\CreatesNewPublicationPage;
use Hyde\Framework\Features\Publications\Models\PublicationField;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Framework\Features\Publications\PublicationService;
use Illuminate\Support\Collection;
use function implode;
use function in_array;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use function str_starts_with;

/**
 * Hyde Command to create a new publication for a given publication type.
 *
 * @see \Hyde\Framework\Actions\CreatesNewPublicationPage
 * @see \Hyde\Framework\Testing\Feature\Commands\MakePublicationCommandTest
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

    public function safeHandle(): int
    {
        $this->title('Creating a new publication!');

        $this->publicationType = $this->getPublicationTypeSelection();

        $fieldData = $this->collectFieldData();

        $creator = new CreatesNewPublicationPage($this->publicationType, $fieldData, (bool) $this->option('force'));
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

        $this->info("Created file {$creator->getOutputPath()}");

        return Command::SUCCESS;
    }

    protected function getPublicationTypeSelection(): PublicationType
    {
        $publicationTypes = $this->getPublicationTypes();

        $publicationTypeSelection = $this->argument('publicationType') ?? $publicationTypes->keys()->get(
            (int) $this->choice(
                'Which publication type would you like to create a publication item for?',
                $publicationTypes->keys()->toArray()
            )
        );

        if ($publicationTypes->has($publicationTypeSelection)) {
            $this->line("<info>Creating a new publication of type</info> [<comment>$publicationTypeSelection</comment>]");

            return $publicationTypes->get($publicationTypeSelection);
        }

        throw new InvalidArgumentException("Unable to locate publication type [$publicationTypeSelection]");
    }

    /** @return \Illuminate\Support\Collection<string, PublicationType> */
    protected function getPublicationTypes(): Collection
    {
        $publicationTypes = PublicationService::getPublicationTypes();
        if ($publicationTypes->isEmpty()) {
            throw new InvalidArgumentException('Unable to locate any publication types. Did you create any?');
        }

        return $publicationTypes;
    }

    /** @return \Illuminate\Support\Collection<string, string|array|null> */
    protected function collectFieldData(): Collection
    {
        $this->newLine();
        $this->info('Now please enter the field data:');
        $data = new Collection();

        /** @var PublicationField $field */
        foreach ($this->publicationType->getFields() as $field) {
            if (str_starts_with($field->name, '__')) {
                continue;
            }
            $this->newLine();
            $data->put($field->name, $this->captureFieldInput($field));
        }

        return $data;
    }

    protected function captureFieldInput(PublicationField $field): bool|string|array|null
    {
        $selection = match ($field->type) {
            PublicationFieldTypes::Text => $this->captureTextFieldInput($field),
            PublicationFieldTypes::Array => $this->captureArrayFieldInput($field),
            PublicationFieldTypes::Image => $this->captureImageFieldInput($field),
            PublicationFieldTypes::Tag => $this->captureTagFieldInput($field),
            PublicationFieldTypes::Boolean => $this->captureBooleanFieldInput($field),
            default => $this->askWithValidation($field->name, "Enter data for field </>[<comment>$field->name</comment>]", $field->getValidationRules()->toArray()),
        };

        if (empty($selection)) {
            $this->line("<fg=gray> > Skipping field $field->name</>");

            return null;
        }

        return $selection;
    }

    protected function captureTextFieldInput(PublicationField $field): string
    {
        $this->line(InputStreamHandler::formatMessage($field->name, 'lines'));

        return implode("\n", InputStreamHandler::call());
    }

    protected function captureArrayFieldInput(PublicationField $field): array
    {
        $this->line(InputStreamHandler::formatMessage($field->name));

        return InputStreamHandler::call();
    }

    protected function captureImageFieldInput(PublicationField $field): string|null
    {
        $this->infoComment('Select file for image field', $field->name);

        $mediaFiles = PublicationService::getMediaForPubType($this->publicationType);
        if ($mediaFiles->isEmpty()) {
            return $this->handleEmptyOptionsCollection($field, 'media file', "No media files found in directory _media/{$this->publicationType->getIdentifier()}/");
        }

        $filesArray = $mediaFiles->toArray();
        $selection = (int) $this->choice('Which file would you like to use?', $filesArray);

        return $filesArray[$selection];
    }

    protected function captureTagFieldInput(PublicationField $field): array|string|null
    {
        $this->infoComment('Select a tag for field', $field->name, "from the {$this->publicationType->getIdentifier()} group");

        $options = PublicationService::getValuesForTagName($this->publicationType->getIdentifier());
        if ($options->isEmpty()) {
            return $this->handleEmptyOptionsCollection($field, 'tag', 'No tags for this publication type found in tags.json');
        }

        $this->tip('You can enter multiple tags separated by commas');

        return $this->reloadableChoice($this->getReloadableTagValuesArrayClosure(),
            'Which tag would you like to use?',
            'Reload tags.json',
            true
        );
    }

    /**
     * @deprecated Will be refactored into a dedicated rule
     */
    protected function captureBooleanFieldInput(PublicationField $field, $retryCount = 1): ?bool
    {
        // Return null when retry count is exceeded to prevent infinite loop
        if ($retryCount > 30) {
            return null;
        }

        // Since the Laravel validation rule for booleans doesn't accept the string input provided by the console,
        // we need to do some logic of our own to support validating booleans through the console.

        $rules = $field->type->rules();
        $rules = array_flip($rules);
        unset($rules['boolean']);
        $rules = array_flip($rules);

        $selection = $this->askWithValidation($field->name, "Enter data for field </>[<comment>$field->name</comment>]", $rules);

        if (empty($selection)) {
            return null;
        }

        $acceptable = ['true', 'false', true, false, 0, 1, '0', '1'];

        // Strict parameter is needed as for some reason `in_array($selection, [true])` is always true no matter what the value of $selection is.
        if (in_array($selection, $acceptable, true)) {
            return (bool) $selection;
        } else {
            // Match the formatting of the standard Laravel validation error message.
            $this->error("The $field->name field must be true or false.");

            return $this->captureBooleanFieldInput($field, $retryCount + 1);
        }
    }

    /** @return null */
    protected function handleEmptyOptionsCollection(PublicationField $field, string $type, string $message)
    {
        if (in_array('required', $field->rules)) {
            throw new InvalidArgumentException("Unable to create publication: $message");
        }

        $this->newLine();
        $this->warn(" <fg=red>Warning:</> $message");
        if ($this->confirm('Would you like to skip this field?', true)) {
            return null;
        } else {
            throw new InvalidArgumentException("Unable to locate any {$type}s for this publication type");
        }
    }

    protected function tip(string $message): void
    {
        $this->line("<fg=bright-blue>Tip:</> $message");
    }

    /** @return Closure<array<string>> */
    protected function getReloadableTagValuesArrayClosure(): Closure
    {
        return function (): array {
            return PublicationService::getValuesForTagName($this->publicationType->getIdentifier())->toArray();
        };
    }
}
