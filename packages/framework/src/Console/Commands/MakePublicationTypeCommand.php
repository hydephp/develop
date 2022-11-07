<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Commands\Interfaces\CommandHandleInterface;
use Hyde\Framework\Actions\CreatesNewPublicationTypeSchema;
use LaravelZero\Framework\Commands\Command;
use Rgasch\Collection;

/**
 * Hyde Command to scaffold a new Markdown or Blade page file.
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

        $title = $this->argument('title')
            ?? $this->ask('What is the name of the Publication Type?')
            ?? 'My new Publication Type';

        $fields = $this->getFieldsDefinitions();

        $this->output->writeln('<bg=magenta;fg=white>Now please choose the default field you wish to sort by:</>');
        foreach ($fields as $k => $v) {
            $humanCount = $k + 1;
            $this->line("  $humanCount: $v[name]");
        }
        $sortField = $this->ask("Sort field: (1-$humanCount)");
        $sortField = $fields[((int)$sortField) - 1]['name'];

        $this->output->writeln('<bg=magenta;fg=white>Now please choose the default sort direction:</>');
        $this->line('  1 - Ascending (ASC)');
        $this->line('  2 - Descending (DESC)');
        $sortDirection = $this->ask('Sort direction (1-2)');
        $sortDirection = match ((int)$sortDirection) {
            1 => 'ASC',
            2 => 'DESC',
        };

        $this->output->writeln('<bg=magenta;fg=white>Choose a canonical name field (the values of this field have to be unique!):</>');
        foreach ($fields as $k => $v) {
            $humanCount = $k + 1;
            $this->line("  $humanCount: $v[name]");
        }
        $canonicalField = $this->ask("Canonical field: (1-$humanCount)");
        $canonicalField = $fields[((int)$canonicalField) - 1]['name'];

        $creator = new CreatesNewPublicationTypeSchema($title, $fields, $canonicalField, $sortField, $sortDirection);
        if (!$creator->create()) {
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
            $field->name = $this->ask('Field name');
            $this->line('Field type:');
            $this->line('  1 - String');
            $this->line('  2 - Integer');
            $this->line('  3 - Float');
            $this->line('  4 - Datetime');
            $type       = (int)$this->ask('Field type (1-4)');
            $field->min = (int)$this->ask('Min value (for strings, this refers to string length)');
            $field->max = (int)$this->ask('Max value (for strings, this refers to string length)');
            $addAnother = (string)$this->ask('Add another field (y/n)');

            // map field choice to actual field type
            $field->type = match ($type) {
                0, 1 => 'string',
                2 => 'integer',
                3 => 'float',
                4 => 'datetime',
            };

            $fields->add($field);
            $count++;
        } while (strtolower($addAnother) != 'n');

        return $fields;
    }
}
