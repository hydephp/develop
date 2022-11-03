<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Framework\Actions\CreatesNewPublicationTypeSchema;
use LaravelZero\Framework\Commands\Command;

/**
 * Hyde Command to scaffold a new Markdown or Blade page file.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\MakePageCommandTest
 */
class MakePublicationTypeCommand extends Command
{
    /** @var string */
    protected $signature = 'make:publicationType
		{title? : The name of the Publication Type to create. Will be used to generate the storage directory}';

    /** @var string */
    protected $description = 'Create a new publication type definition';

    /**
     * The page title.
     */
    protected string $title;

    /**
     * The default field to sort by
     */
    protected string $canonicalField;

    /**
     * The default field to sort by
     */
    protected string $sortField;

    /**
     * The default sort direction
     */
    protected string $sortDirection;


    public function handle(): int
    {
        $this->title('Creating a new Publication Type!');

        $this->title = $this->argument('title')
            ?? $this->ask('What is the name of the Publication Type?')
            ?? 'My new Publication Type';

        $this->fields = $this->getFieldsDefinitions();

        $this->output->writeln('<bg=magenta;fg=white>Now please choose the default field you wish to sort by:</>');
        foreach ($this->fields as $k => $v) {
            $humanCount = $k + 1;
            $this->line("  $humanCount: $v[name]");
        }
        $this->sortField = $this->ask("Sort field: (1-$humanCount)");
        $this->sortField = $this->fields[((int)$this->sortField) - 1]['name'];

        $this->output->writeln('<bg=magenta;fg=white>Now please choose the default sort direction:</>');
        $this->line('  1 - Ascending (ASC)');
        $this->line('  2 - Descending (DESC)');
        $this->sortDirection = $this->ask('Sort direction (1-2)');
        $this->sortDirection = match ((int)$this->sortDirection) {
            1 => 'ASC',
            2 => 'DESC',
        };

        $this->output->writeln('<bg=magenta;fg=white>Choose a canonical name field (the values of this field have to be unique!):</>');
        foreach ($this->fields as $k => $v) {
            $humanCount = $k + 1;
            $this->line("  $humanCount: $v[name]");
        }
        $this->canonicalField = $this->ask("Canonical field: (1-$humanCount)");
        $this->canonicalField = $this->fields[((int)$this->canonicalField) - 1]['name'];

        $creator = new CreatesNewPublicationTypeSchema($this->title, $this->fields, $this->canonicalField, $this->sortField, $this->sortDirection);

        return Command::SUCCESS;
    }

    private function getFieldsDefinitions(): array
    {
        $this->output->writeln('<bg=magenta;fg=white>You now need to define the fields in your publication type:</>');
        $count  = 1;
        $fields = [];
        do {
            $this->line('');
            $this->output->writeln("<bg=cyan;fg=white>Field #$count:</>");

            $field         = [];
            $field['name'] = $this->ask('Field name');
            $this->line('Field type:');
            $this->line('  1 - String');
            $this->line('  2 - Integer');
            $this->line('  3 - Float');
            $this->line('  4 - Datetime');
            $field['type'] = $this->ask('Field type (1-4)');
            $field['min']  = $this->ask('Min value (for strings, this refers to string length)');
            $field['max']  = $this->ask('Max value (for strings, this refers to string length)');
            $addAnother    = $this->ask('Add another field (y/n)');

            // map field choice to actual field type
            $field['type'] = match ((int)$field['type']) {
                1 => 'string',
                2 => 'integer',
                3 => 'float',
                4 => 'datetime',
            };

            $fields[] = $field;
            $count++;
        } while ($addAnother && strtolower($addAnother) != 'n');

        return $fields;
    }
}
