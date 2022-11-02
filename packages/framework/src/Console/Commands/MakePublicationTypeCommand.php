<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Framework\Actions\CreatesNewPageSourceFile;
use Hyde\Framework\Actions\CreatesNewPublicationType;
use Hyde\Framework\Exceptions\UnsupportedPageTypeException;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
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
		{title? : The name of the publication tye to create. Will be used to generate the storage directory}
		{--force : Overwrite any existing files}';

    /** @var string */
    protected $description = 'Create a new publication type definition';

    /**
     * The page title.
     */
    protected string $title;

    /**
     * The page title.
     */
    protected string $sortField;

    public function handle(): int
    {
        $this->title('Creating a new Publication Type!');

        $this->title = $this->argument('title')
            ?? $this->ask('What is the name of the Publication Type?')
            ?? 'My new Publication Type';

        //$this->line('<info>Creating a new '.ucwords($this->selectedType).' page with title:</> '.$this->title."\n");

        //$this->force = $this->option('force') ?? false;

        $this->fields = $this->getFieldsDefinitions();

        $this->output->writeln('<bg=magenta;fg=white>Now please choose the field you wish to sort by:</>');
        foreach ($this->fields as $k=>$v) {
            $_k = $k+1;
            $this->line("  $_k: $v[name]");
        }
        $this->sortField = $this->ask("Sort field: (1-$_k)");
        $this->sortField = $this->fields[((int)$this->sortField)-1]['name'];

        $creator = new CreatesNewPublicationType($this->title, $this->fields, $this->sortField);

        return Command::SUCCESS;
    }

    private function getFieldsDefinitions(): array
    {
        $this->output->writeln('<bg=magenta;fg=white>You now need to define the fields in your publication type:</>');
        $count = 1;
        $fields = [];
        do {
            $this->line('');
            $this->output->writeln("<bg=cyan;fg=white>Field #$count:</>");

            $field = [];
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
            $field['type'] = match((int)$field['type']) {
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
