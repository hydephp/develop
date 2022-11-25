<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Exception;
use Hyde\Console\Commands\Interfaces\CommandHandleInterface;
use Hyde\Console\Concerns\ValidatingCommand;
use Hyde\Framework\Actions\CreatesNewPublicationFile;
use Hyde\Framework\Exceptions\FileConflictException;
use Hyde\Framework\Features\Publications\Models\PublicationFieldType;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationService;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use Rgasch\Collection\Collection;

/**
 * Hyde Command to create a new publication for a given publication type.
 *
 * @todo Add --force option?
 *
 * @see \Hyde\Framework\Actions\CreatesNewPublicationFile
 * @see \Hyde\Framework\Testing\Feature\Commands\MakePublicationCommandTest
 */
class MakePublicationCommand extends ValidatingCommand implements CommandHandleInterface
{
    /** @var string */
    protected $signature = 'make:publication
		{publicationType? : The name of the PublicationType to create a publication for}';

    /** @var string */
    protected $description = 'Create a new publication item';

    public function handle(): int
    {
        $this->title('Creating a new Publication!');

        try {
            $pubType = $this->getPubTypeSelection($this->getPublicationTypes());
            $fieldData = $this->collectFieldData($pubType);

            $creator = new CreatesNewPublicationFile($pubType, $fieldData, false, $this->output);
            if ($creator->fileConflicts()) {
                $this->error('Error: A publication already exists with the same canonical field value');
                if ($this->confirm('Do you wish to overwrite the existing file?')) {
                    $creator->force();
                } else {
                    $this->output->writeln('<bg=magenta;fg=white>Exiting without overwriting existing publication file!</>');

                    return ValidatingCommand::USER_EXIT;
                }
            }

            $creator->create();

        } catch (Exception $exception) {
            return $this->handleException($exception);
        }

        $this->info('Publication created successfully!');

        return Command::SUCCESS;
    }

    protected function captureFieldInput(PublicationFieldType $field, Collection $mediaFiles): string|array
    {
        if ($field->type === 'text') {
            $lines = [];
            $this->output->writeln($field->name." (end with a line containing only '<<<')");
            do {
                $line = Str::replace("\n", '', fgets(STDIN));
                if ($line === '<<<') {
                    break;
                }
                $lines[] = $line;
            } while (true);

            return implode("\n", $lines);
        }

        if ($field->type === 'array') {
            $lines = [];
            $this->output->writeln($field->name.' (end with an empty line)');
            do {
                $line = Str::replace("\n", '', fgets(STDIN));
                if ($line === '') {
                    break;
                }
                $lines[] = $line;
            } while (true);

            return $lines;
        }

        if ($field->type === 'image') {
            $this->output->writeln($field->name.' (end with an empty line)');
            $offset = 0;
            foreach ($mediaFiles as $index => $file) {
                $offset = $index + 1;
                $this->output->writeln("  $offset: $file");
            }
            $selected = $this->askWithValidation($field->name, $field->name, ['required', 'integer', "between:1,$offset"]);
            $file = $mediaFiles->{$selected - 1};

            return '_media/'.Str::of($file)->after('media/')->toString();
        }

        // Fields which are not of type array, text or image
        $fieldRules = Collection::create(PublicationFieldType::DEFAULT_RULES)->{$field->type};
        if ($fieldRules->contains('between')) {
            $fieldRules->forget($fieldRules->search('between'));
            if ($field->min && $field->max) {
                switch ($field->type) {
                    case 'string':
                    case 'integer':
                    case 'float':
                        $fieldRules->add("between:$field->min,$field->max");
                        break;
                    case 'datetime':
                        $fieldRules->add("after:$field->min");
                        $fieldRules->add("before:$field->max");
                        break;
                }
            }
        }

        return $this->askWithValidation($field->name, $field->name, $fieldRules);
    }

    /**
     * @param  \Rgasch\Collection\Collection<string, \Hyde\Framework\Features\Publications\Models\PublicationType>  $pubTypes
     * @return \Hyde\Framework\Features\Publications\Models\PublicationType
     */
    protected function getPubTypeSelection(Collection $pubTypes): PublicationType
    {
        $pubTypeSelection = $this->argument('publicationType') ?? $pubTypes->keys()->get(
            (int) $this->choice('Which publication type would you like to create a publication item for?',
                $pubTypes->keys()->toArray()
            )
        );

        if ($pubTypes->has($pubTypeSelection)) {
            $this->line("<info>Creating a new publication of type</info> [<comment>$pubTypeSelection</comment>]");

            return $pubTypes->get($pubTypeSelection);
        }

        throw new InvalidArgumentException("Unable to locate publication type [$pubTypeSelection]");
    }

    /**
     * @param  \Hyde\Framework\Features\Publications\Models\PublicationType  $pubType
     * @return \Rgasch\Collection\Collection<string, string|array>
     */
    protected function collectFieldData(PublicationType $pubType): Collection
    {
        $this->output->writeln("\n<bg=magenta;fg=white>Now please enter the field data:</>");

        $mediaFiles = PublicationService::getMediaForPubType($pubType);

        return Collection::make($pubType->fields)->mapWithKeys(function ($field) use ($mediaFiles) {
            return [$field['name'] => $this->captureFieldInput(PublicationFieldType::fromArray($field), $mediaFiles)];
        });
    }

    /**
     * @return \Rgasch\Collection\Collection<string, PublicationType>
     *
     * @throws \InvalidArgumentException
     */
    protected function getPublicationTypes(): Collection
    {
        $pubTypes = PublicationService::getPublicationTypes();
        if ($pubTypes->isEmpty()) {
            throw new InvalidArgumentException('Unable to locate any publication types. Did you create any?');
        }

        return $pubTypes;
    }
}
