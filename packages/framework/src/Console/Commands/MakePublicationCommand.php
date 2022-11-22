<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Exception;
use Hyde\Console\Commands\Interfaces\CommandHandleInterface;
use Hyde\Framework\Actions\CreatesNewPublicationFile;
use Hyde\Framework\Features\Publications\PublicationHelper;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use Rgasch\Collection\Collection;

/**
 * Hyde Command to create a new publication for a given publication type.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\MakePublicationCommandTest
 */
class MakePublicationCommand extends Command implements CommandHandleInterface
{
    /** @var string */
    protected $signature = 'make:publication
		{publicationType? : The name of the PublicationType to create a publication for}';

    /** @var string */
    protected $description = 'Create a new publication item';

    public function handle(): int
    {
        $this->title('Creating a new Publication!');

        $pubTypes = PublicationHelper::getPublicationTypes();
        if ($pubTypes->isEmpty()) {
            $this->output->error('Unable to locate any publication-types ... did you create any?');

            return Command::FAILURE;
        }

        $pubType = $this->argument('publicationType');
        if (! $pubType) {
            $this->output->writeln('<bg=magenta;fg=white>Now please choose the Publication Type to create an item for:</>');
            $offset = 0;
            foreach ($pubTypes as $pubType) {
                $offset++;
                $this->line("  $offset: $pubType->name");
            }
            $selected = (int) PublicationHelper::askWithValidation($this, 'selected', "Publication type (1-$offset)", ['required', 'integer', "between:1,$offset"]);
            $pubType = $pubTypes->{$pubTypes->keys()[$selected - 1]};
        }

        $mediaFiles = PublicationHelper::getMediaForPubType($pubType);
        $fieldData = Collection::create();
        $this->output->writeln('<bg=magenta;fg=white>Now please enter the field data:</>');
        foreach ($pubType->fields as $field) {
            $fieldData->{$field['name']} = $this->captureFieldInput((object) $field, $mediaFiles);
        }

        try {
            $creator = new CreatesNewPublicationFile($pubType, $fieldData);
            $creator->create();
        } catch (InvalidArgumentException $exception) { // FIXME: provide a properly typed exception
            $msg = $exception->getMessage();
            // Useful for debugging
            //$this->output->writeln("xxx " . $exception->getTraceAsString());
            $this->output->writeln("<bg=red;fg=white>$msg</>");
            $overwrite = PublicationHelper::askWithValidation(
                $this,
                'overwrite',
                'Do you wish to overwrite the existing file (y/n)',
                ['required', 'string', 'in:y,n'],
                'n'
            );
            if (strtolower($overwrite) == 'y') {
                $creator = new CreatesNewPublicationFile($pubType, $fieldData, true);
                $creator->create();
            } else {
                $this->output->writeln('<bg=magenta;fg=white>Existing without overwriting existing publication file!</>');
            }
        } catch (Exception $exception) {
            $this->error('Error: '.$exception->getMessage().' at '.$exception->getFile().':'.$exception->getLine());

            return Command::FAILURE;
        }

        $this->info('Publication created successfully!');

        return Command::SUCCESS;
    }

    private function captureFieldInput(object $field, Collection $mediaFiles): string|array
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
            $selected = PublicationHelper::askWithValidation($this, $field->name, $field->name, ['required', 'integer', "between:1,$offset"]);
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

        return PublicationHelper::askWithValidation($this, $field->name, $field->name, $fieldRules);
    }

    private function getValidationRulesPerType(): Collection
    {
        return Collection::create(
            [
                'string'   => ['required', 'string', 'between'],
                'boolean'  => ['required', 'boolean'],
                'integer'  => ['required', 'integer', 'between'],
                'float'    => ['required', 'numeric', 'between'],
                'datetime' => ['required', 'datetime', 'between'],
                'url'      => ['required', 'url'],
                'text'     => ['required', 'string', 'between'],
            ]
        );
    }
}
