<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Exception;
use Hyde\Console\Commands\Interfaces\CommandHandleInterface;
use Hyde\Console\Concerns\ValidatingCommand;
use Hyde\Framework\Actions\CreatesNewPublicationFile;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationService;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use Rgasch\Collection\Collection;
use function strtolower;

/**
 * Hyde Command to create a new publication for a given publication type.
 *
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
            $pubTypes = PublicationService::getPublicationTypes();
            if ($pubTypes->isEmpty()) {
                throw new InvalidArgumentException('Unable to locate any publication types. Did you create any?');
            }

            $pubType = $this->getPubTypeSelection($pubTypes);

            $mediaFiles = PublicationService::getMediaForPubType($pubType);
            $fieldData = Collection::create();
            $this->output->writeln('<bg=magenta;fg=white>Now please enter the field data:</>');
            foreach ($pubType->fields as $field) {
                $fieldData->{$field['name']} = $this->captureFieldInput((object) $field, $mediaFiles);
            }

            try {
                $creator = new CreatesNewPublicationFile($pubType, $fieldData, output: $this->output);
                $creator->create();
            } catch (InvalidArgumentException $exception) { // FIXME: provide a properly typed exception
                $msg = $exception->getMessage();
                // Useful for debugging
                //$this->output->writeln("xxx " . $exception->getTraceAsString());
                $this->output->writeln("<bg=red;fg=white>$msg</>");
                $overwrite = $this->askWithValidation(
                    'overwrite',
                    'Do you wish to overwrite the existing file (y/n)',
                    ['required', 'string', 'in:y,n'],
                    'n'
                );
                if (strtolower($overwrite) == 'y') {
                    $creator = new CreatesNewPublicationFile($pubType, $fieldData, true, $this->output);
                    $creator->create();
                } else {
                    $this->output->writeln('<bg=magenta;fg=white>Exiting without overwriting existing publication file!</>');
                }
            } catch (Exception $exception) {
                // FIXME: This is probably redundant and can likely be removed after it's covered by tests
                $this->error("Error: {$exception->getMessage()} at {$exception->getFile()}:{$exception->getLine()}");

                throw $exception;
            }
        } catch (Exception $exception) {
            $this->error("Error: {$exception->getMessage()} at {$exception->getFile()}:{$exception->getLine()}");

            return Command::FAILURE;
        }

        $this->info('Publication created successfully!');

        return Command::SUCCESS;
    }

    protected function captureFieldInput(object $field, Collection $mediaFiles): string|array
    {
        $rulesPerType = $this->getValidationRulesPerType();

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
        $fieldRules = $rulesPerType->{$field->type};
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

    protected function getValidationRulesPerType(): Collection
    {
        return Collection::create([
            'string'   => ['required', 'string', 'between'],
            'boolean'  => ['required', 'boolean'],
            'integer'  => ['required', 'integer', 'between'],
            'float'    => ['required', 'numeric', 'between'],
            'datetime' => ['required', 'datetime', 'between'],
            'url'      => ['required', 'url'],
            'text'     => ['required', 'string', 'between'],
        ]);
    }

    protected function getPubTypeSelection(Collection $pubTypes): PublicationType
    {
        if ($this->argument('publicationType')) {
            $pubTypeSelection = $this->argument('publicationType');
        } else {
            $choice = (int) $this->choice(
                'Which publication type would you like to create a publication item for?',
                $pubTypes->keys()->toArray(),
            );
            $pubTypeSelection = $pubTypes->keys()->get($choice);
        }
        $pubType = $pubTypes->get($pubTypeSelection);
        if (! $pubType) {
            throw new InvalidArgumentException('Unable to locate the publication type you selected.');
        }

        $this->line("<info>Creating a new publication of type</info> [<comment>$pubTypeSelection</comment>]");
        return $pubType;
    }
}
