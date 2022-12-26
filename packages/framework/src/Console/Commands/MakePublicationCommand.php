<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

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

    protected function captureFieldInput(PublicationField $field): string|array
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
        $this->output->writeln($field->name.' (end with an empty line)');

        return implode("\n", InputStreamHandler::call());
    }

    protected function captureArrayFieldInput(PublicationField $field): array
    {
        $this->output->writeln($field->name.' (end with an empty line)');

        return InputStreamHandler::call();
    }

    protected function captureImageFieldInput(PublicationField $field): string
    {
        $this->infoComment('Select file for image field', $field->name);

        $mediaFiles = PublicationService::getMediaForPubType($this->publicationType);
        if ($mediaFiles->isEmpty()) {
            $this->warn("\nWarning: No media files found in directory _media/{$this->publicationType->getIdentifier()}/");
            if ($this->confirm('Would you like to skip this field?', true)) {
                return '';
            } else {
                throw new InvalidArgumentException('Unable to locate any media files for this publication type');
            }
        }

        $filesArray = $mediaFiles->toArray();
        $selection = (int) $this->choice('Which file would you like to use?', $filesArray);

        return $filesArray[$selection];
    }

    protected function captureTagFieldInput(PublicationField $field)
    {
        // Todo support multiple tags?
        $this->infoComment('Select a tag for field', $field->name);
        $this->tip("Pick tag from the {$this->publicationType->getIdentifier()} group");
        $this->tip("Enter '0' to reload tag definitions");

        $options = PublicationService::getValuesForTagName($this->publicationType->getIdentifier());
        $selection = $this->choice('Which tag would you like to use?', $options->toArray());

        return $selection;
    }

    // Get rules for fields which are not of type array, text or image
    protected function generateFieldRules(PublicationField $field): Collection
    {
        $fieldRules = Collection::make($field->type->rules());
        if ($fieldRules->contains('between')) {
            $fieldRules->forget($fieldRules->search('between'));
        }

        return $fieldRules;
    }

    protected function tip(string $message): void
    {
        $this->output->writeln("<fg=bright-blue>Tip:</> $message");
    }
}
