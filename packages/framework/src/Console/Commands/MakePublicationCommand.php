<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Commands\Interfaces\CommandHandleInterface;
use Hyde\Framework\Actions\CreatesNewPublicationFile;
use Hyde\HydeHelper;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Rgasch\Collection\Collection;

/**
 * Hyde Command to create a new publication for a given publication type
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\MakePageCommandTest
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
        $this->title('Creating a new Publication Item!');

        $pubTypes = HydeHelper::getPublicationTypes();
        if ($pubTypes->isEmpty()) {
            $this->output->error("Unable to locate any publication-types ... did you create any?");
            return Command::FAILURE;
        }

        $pubType = $this->argument('publicationType');
        if (!$pubType) {
            $this->output->writeln('<bg=magenta;fg=white>Now please choose the Publication Type to create an item for:</>');
            $offset = 0;
            foreach ($pubTypes as $pubType) {
                $offset++;
                $this->line("  $offset: $pubType->name");
            }
            $selected = (int)HydeHelper::askWithValidation($this, 'selected', "Publication type (1-$offset)", ['required', 'integer', "between:1,$offset"]);
            $pubType  = $pubTypes->{$pubTypes->keys()[$selected - 1]};
        }

        $rulesPerType = Collection::create(
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

        $fieldData = Collection::create();
        $this->output->writeln('<bg=magenta;fg=white>Now please enter the field data:</>');
        foreach ($pubType->fields as $field) {
            // Need to capture text line-by-line
            if ($field->type === 'text') {
                $lines = [];
                $this->output->writeln($field->name . " (end with a line containing only '<<<')");
                do {
                    $line    = Str::replace("\n", '', fgets(STDIN));
                    $lines[] = $line;
                } while ($line != '<<<');

                $fieldData->{$field->name} = implode("\n", $lines);
                continue;
            }

            // Non-text block fields
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
            $fieldData->{$field->name} = HydeHelper::askWithValidation($this, $field->name, $field->name, $fieldRules);
        }

        try {
            $creator = new CreatesNewPublicationFile($pubType, $fieldData);
            $creator->create();
        } catch (\InvalidArgumentException $e) { // FIXME: provide a properly typed exception
            $this->output->writeln('<bg=red;fg=white>A file for this set of data already exists!</>');
            $overwrite = HydeHelper::askWithValidation(
                $this,
                'overwrite',
                'Do you wish to overwrite the existing file (y/n)',
                ['required', 'string', "in:y,n"],
                'n'
            );
            if (strtolower($overwrite) == 'y') {
                $creator = new CreatesNewPublicationFile($pubType, $fieldData, true);
                $creator->create();
            } else {
                $this->output->writeln('<bg=magenta;fg=white>Existing without overwriting existing publication file!</>');
            }
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage() . " at " . $e->getFile() . ':' . $e->getLine());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
