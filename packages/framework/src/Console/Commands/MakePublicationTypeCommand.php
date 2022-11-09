<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Commands\Interfaces\CommandHandleInterface;
use Hyde\Framework\Actions\CreatesNewPublicationTypeSchema;
use Hyde\HydeHelper;
use LaravelZero\Framework\Commands\Command;
use Rgasch\Collection\Collection;

/**
 * Hyde Command to create a new publication type
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\MakePageCommandTest
 */
class MakePublicationTypeCommand extends Command implements CommandHandleInterface
{
    /** @var string */
    protected $signature = 'make:publicationType
		{title? : The name of the Publication Type to create. Will be used to generate the storage directory}';

    /** @var string */
    protected $description = 'Create a new publication type definition';


    public function handle(): int
    {
        $this->title('Creating a new Publication Type!');

        $title = $this->argument('title');
        if (!$title) {
            $title   = trim(HydeHelper::askWithValidation($this, 'nanme', 'Publication type name', ['required', 'string']));
            $dirname = HydeHelper::formatNameForStorage($title);
            if (file_exists($dirname)) {
                throw new \InvalidArgumentException("Storage path [$dirname] already exists");
            }
        }

        $fields = $this->getFieldsDefinitions();

        $this->output->writeln('<bg=magenta;fg=white>Choose the default field you wish to sort by:</>');
        $this->line("  0: dateCreated (meta field)");
        foreach ($fields as $k => $v) {
            $offset = $k + 1;
            $this->line("  $offset: $v[name]");
        }
        $selected  = (int)HydeHelper::askWithValidation($this, 'selected', "Sort field (0-$offset)", ['required', 'integer', "between:0,$offset"], 0);
        $sortField = $selected ? $fields[$selected - 1]['name'] : '__createdAt';

        $this->output->writeln('<bg=magenta;fg=white>Choose the default sort direction:</>');
        $this->line('  1 - Ascending (ASC)');
        $this->line('  2 - Descending (DESC)');
        $selected      = (int)HydeHelper::askWithValidation($this, 'selected', "Sort field (1-2)", ['required', 'integer', "between:1,2"], 2);
        $sortDirection = match ($selected) {
            1 => 'ASC',
            2 => 'DESC',
        };

        $pagesize = (int)HydeHelper::askWithValidation($this, 'pagesize', "Enter the pagesize (0 for no limit)", ['required', 'integer', 'between:0,100'], 25);

        $this->output->writeln('<bg=magenta;fg=white>Choose a canonical name field (the values of this field have to be unique!):</>');
        foreach ($fields as $k => $v) {
            $offset = $k + 1;
            $this->line("  $offset: $v[name]");
        }
        $selected       = (int)HydeHelper::askWithValidation($this, 'selected', "Canonical field (1-$offset)", ['required', 'integer', "between:1,$offset"], 1);
        $canonicalField = $fields[$selected - 1]['name'];

        try {
            $creator = new CreatesNewPublicationTypeSchema($title, $fields, $canonicalField, $sortField, $sortDirection, $pagesize);
            $creator->create();
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage() . " at " . $e->getFile() . ':' . $e->getLine());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }


    private function getFieldsDefinitions(): Collection
    {
        $this->output->writeln('<bg=magenta;fg=white>You now need to define the fields in your publication type:</>');
        $count  = 1;
        $fields = Collection::create();
        do {
            $this->line('');
            $this->output->writeln("<bg=cyan;fg=white>Field #$count:</>");

            $field       = Collection::create();
            $field->name = HydeHelper::askWithValidation($this, 'name', 'Field name', ['required']);
            $this->line('Field type:');
            $this->line('  1 - String');
            $this->line('  2 - Boolean ');
            $this->line('  3 - Integer');
            $this->line('  4 - Float');
            $this->line('  5 - Datetime');
            $this->line('  6 - URL');
            $this->line('  7 - Text');
            $type = (int)HydeHelper::askWithValidation($this, 'type', 'Field type (1-7)', ['required', 'integer', 'between:1,7'], 1);
            do {
                $field->min   = HydeHelper::askWithValidation($this, 'min', 'Min value (for strings, this refers to string length)', ['required', 'string'], 0);
                $field->max   = HydeHelper::askWithValidation($this, 'max', 'Max value (for strings, this refers to string length)', ['required', 'string'], 0);
                $lengthsValid = true;
                if ($field->max < $field->min) {
                    $lengthsValid = false;
                    $this->output->warning("Field length [max] must be [>=] than [min]");
                }
            } while (!$lengthsValid);
            $addAnother = HydeHelper::askWithValidation($this, 'addAnother', 'Add another field (y/n)', ['required', 'string', "in:y,n"], 'y');

            // map field choice to actual field type
            $field->type = match ($type) {
                1 => 'string',
                2 => 'boolean',
                3 => 'integer',
                4 => 'float',
                5 => 'datetime',
                6 => 'url',
                7 => 'text',
            };

            $fields->add($field);
            $count++;
        } while (strtolower($addAnother) != 'n');

        return $fields;
    }
}
