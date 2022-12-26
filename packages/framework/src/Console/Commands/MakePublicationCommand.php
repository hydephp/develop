<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use function array_merge;
use Hyde\Console\Commands\Helpers\InputStreamHandler;
use Hyde\Console\Concerns\ValidatingCommand;
use Hyde\Framework\Actions\CreatesNewPublicationPage;
use Hyde\Framework\Features\Publications\Models\PublicationField;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Framework\Features\Publications\PublicationService;
use Illuminate\Support\Collection;
use function implode;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;

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
		{publicationType? : The name of the PublicationType to create a publication for}
        {--force : Should the generated file overwrite existing publications with the same filename?}';

    /** @var string */
    protected $description = 'Create a new publication item';

    protected PublicationType $publicationType;

    public function safeHandle(): int
    {
        $this->title('Creating a new Publication!');

        $this->publicationType = $this->getPubTypeSelection();
        $fieldData = $this->collectFieldData();

        $creator = new CreatesNewPublicationPage($this->publicationType, $fieldData, $this->hasForceOption(), $this->output);
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

        $this->info('Publication created successfully!');

        return Command::SUCCESS;
    }

    protected function captureFieldInput(PublicationField $field): string|array|null
    {
        return match ($field->type) {
            PublicationFieldTypes::Text => $this->captureTextFieldInput($field),
            PublicationFieldTypes::Array => $this->captureArrayFieldInput($field),
            PublicationFieldTypes::Image => $this->captureImageFieldInput($field),
            PublicationFieldTypes::Tag => $this->captureTagFieldInput($field),
            default => $this->askWithValidation($field->name, $field->name, $this->generateFieldRules($field)->toArray()),
        };
    }

    protected function getPubTypeSelection(): PublicationType
    {
        $publicationTypes = $this->getPublicationTypes();

        $publicationTypeSelection = $this->argument('publicationType') ?? $publicationTypes->keys()->get(
            (int) $this->choice('Which publication type would you like to create a publication item for?',
                $publicationTypes->keys()->toArray()
            )
        );

        if ($publicationTypes->has($publicationTypeSelection)) {
            $this->line("<info>Creating a new publication of type</info> [<comment>$publicationTypeSelection</comment>]");

            return $publicationTypes->get($publicationTypeSelection);
        }

        throw new InvalidArgumentException("Unable to locate publication type [$publicationTypeSelection]");
    }

    /**
     * @return \Illuminate\Support\Collection<string, string|array>
     */
    protected function collectFieldData(): Collection
    {
        $this->info("\nNow please enter the field data:");
        $data = new Collection();

        /** @var PublicationField $field */
        foreach ($this->publicationType->getFields() as $field) {
            $this->newLine();
            $data->put($field->name, $this->captureFieldInput($field));
        }

        return $data;
    }

    /**
     * @return \Illuminate\Support\Collection<string, PublicationType>
     *
     * @throws \InvalidArgumentException
     */
    protected function getPublicationTypes(): Collection
    {
        $publicationTypes = PublicationService::getPublicationTypes();
        if ($publicationTypes->isEmpty()) {
            throw new InvalidArgumentException('Unable to locate any publication types. Did you create any?');
        }

        return $publicationTypes;
    }

    protected function hasForceOption(): bool
    {
        return (bool) $this->option('force');
    }

    protected function captureTextFieldInput(PublicationField $field): string
    {
        $this->line(InputStreamHandler::formatMessage($field->name));

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
            $this->newLine();
            $this->warn("Warning: No media files found in directory _media/{$this->publicationType->getIdentifier()}/");
            return $this->handleEmptyCollection("media file");
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
            $this->newLine();
            $this->warn('Warning: No tags for this publication type found in tags.json');
            return $this->handleEmptyCollection("tag");
        }

        $this->tip('You can enter multiple tags separated by commas');

        do {
            $options = PublicationService::getValuesForTagName($this->publicationType->getIdentifier());
            $selection = $this->choice('Which tag would you like to use?', array_merge([0 => '<fg=bright-blue>[Reload tags.json]</>'], $options->toArray()), multiple: true);
        } while ($selection === '<fg=bright-blue>[Reload tags.json]</>');

        return $selection;
    }

    // Get rules for fields which are not of type array, text or image
    protected function generateFieldRules(PublicationField $field): Collection
    {
        return Collection::make($field->type->rules());
    }

    protected function tip(string $message): void
    {
        $this->line("<fg=bright-blue>Tip:</> $message");
    }

    /** @return null */
    protected function handleEmptyCollection(string $type)
    {
        // TODO we might want to check if the field has a required rule which should jump straight to the exception
        if ($this->confirm('Would you like to skip this field?', true)) {
            return null;
        } else {
            throw new InvalidArgumentException("Unable to locate any {$type}s for this publication type");
        }
    }
}
