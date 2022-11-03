<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Framework\Actions\CreatesNewPublicationFile;
use LaravelZero\Framework\Commands\Command;

/**
 * Hyde Command to scaffold a new Markdown or Blade page file.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\MakePageCommandTest
 */
class MakePublicationCommand extends Command
{
    /** @var string */
    protected $signature = 'make:publication
		{publicationType? : The name of the PublicationType to create a publication for}';

    /** @var string */
    protected $description = 'Create a new publication item';

    /**
     * The page title.
     */
    protected string $title;

    /**
     * The page title.
     */
    protected \stdClass $pubType;

    /**
     * The default field to sort by
     */
    protected array $fieldData = [];


    public function handle(): int
    {
        $this->title('Creating a new Publication Item!');

        $pubType  = $this->argument('publicationType');
        $pubTypes = $this->getPublicationTypes();
        if (!$pubType) {
            $this->output->writeln('<bg=magenta;fg=white>Now please choose the Publication Type to create an item for:</>');
            foreach ($pubTypes as $k => $pubType) {
                $humanCount = $k + 1;
                $this->line("  $humanCount: $pubType->name");
            }
            $selected      = $this->ask("Publication type: (1-$humanCount)");
            $this->pubType = $pubTypes[((int)$selected) - 1];
        }

        $this->output->writeln('<bg=magenta;fg=white>You now need to enter the fields data:</>');
        foreach ($this->pubType->fields as $field) {
            $this->fieldData[$field->name] = $this->ask("  $field->name ($field->type, min=$field->min, max=$field->max): ");
        }

        $creator = new CreatesNewPublicationFile($this->pubType, $this->fieldData);

        return Command::SUCCESS;
    }

    // Fixme: this should probably be moved to a generic util/helper class
    private function getPublicationTypes(): array
    {
        $root = base_path();

        $data        = [];
        $schemaFiles = glob("$root/*/schema.json", GLOB_BRACE);
        foreach ($schemaFiles as $schemaFile) {
            $schema       = json_decode(file_get_contents($schemaFile));
            $schema->file = $schemaFile;
            $data[]       = $schema;
        }

        return $data;
    }
}
