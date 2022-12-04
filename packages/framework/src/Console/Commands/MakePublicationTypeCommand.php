<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Exception;
use Hyde\Console\Commands\Interfaces\CommandHandleInterface;
use Hyde\Console\Concerns\ValidatingCommand;
use Hyde\Framework\Actions\CreatesNewPublicationType;
use Hyde\Framework\Features\Publications\PublicationService;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use Rgasch\Collection\Collection;

/**
 * Hyde Command to create a new publication type.
 *
 * @see \Hyde\Framework\Actions\CreatesNewPublicationType
 * @see \Hyde\Framework\Testing\Feature\Commands\MakePublicationTypeCommandTest
 */
class MakePublicationTypeCommand extends ValidatingCommand implements CommandHandleInterface
{
    /** @var string */
    protected $signature = 'make:publicationType
		{title? : The name of the Publication Type to create. Will be used to generate the storage directory}';

    /** @var string */
    protected $description = 'Create a new publication type definition';

    public function safeHandle(): int
    {
        $this->title('Creating a new Publication Type!');

        $title = $this->argument('title');
        if (! $title) {
            $title = trim($this->askWithValidation('name', 'Publication type name', ['required', 'string']));
            $dirname = Str::slug($title);
            if (file_exists($dirname) && is_dir($dirname) && count(scandir($dirname)) > 2) {
                throw new InvalidArgumentException("Storage path [$dirname] already exists");
            }
        }

        $fields = $this->captureFieldsDefinitions();

        $this->output->writeln('<bg=magenta;fg=white>Choose the default field you wish to sort by:</>');
        $this->line('  0: dateCreated (meta field)');
        $offset = 0;
        foreach ($fields as $k => $v) {
            $offset = $k + 1;
            $this->line("  $offset: $v[name]");
        }
        $selected = (int) $this->askWithValidation('selected', "Sort field (0-$offset)", ['required', 'integer', "between:0,$offset"], 0);
        $sortField = $selected ? $fields[$selected - 1]['name'] : '__createdAt';

        $this->output->writeln('<bg=magenta;fg=white>Choose the default sort direction:</>');
        $this->line('  1 - Ascending (oldest items first if sorting by dateCreated)');
        $this->line('  2 - Descending (newest items first if sorting by dateCreated)');
        $selected = (int) $this->askWithValidation('selected', 'Sort field (1-2)', ['required', 'integer', 'between:1,2'], 2);
        $sortDirection = match ($selected) {
            1 => 'ASC',
            2 => 'DESC',
        };

        $pageSize = (int) $this->askWithValidation(
            'pageSize',
            'Enter the pageSize (0 for no limit)',
            ['required', 'integer', 'between:0,100'],
            25
        );
        $prevNextLinks = (bool) $this->askWithValidation(
            'prevNextLinks',
            'Generate previous/next links in detail view (y/n)',
            ['required', 'string', 'in:y,n'],
            'y'
        );

        $this->output->writeln('<bg=magenta;fg=white>Choose a canonical name field (the values of this field have to be unique!):</>');
        $fieldNames = [];
        foreach ($fields as $k => $v) {
            if ($v->type != 'image' && $v->type != 'tag') {
                $fieldNames[] = $v->name;
                $offset = $k + 1;
                $this->line("  $offset: $v->name");
            }
        }
        $selected = (int) $this->askWithValidation('selected', "Canonical field (1-$offset)", ['required', 'integer', "between:1,$offset"], 1);
        $canonicalField = $fieldNames[$selected - 1];

        try {
            $creator = new CreatesNewPublicationType($title, $fields, $canonicalField, $sortField, $sortDirection, $pageSize, $prevNextLinks, $this->output);
            $creator->create();
        } catch (Exception $e) {
            $this->error('Error: '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine());

            return Command::FAILURE;
        }

        $this->info('Publication type created successfully!');

        return Command::SUCCESS;
    }

    protected function captureFieldsDefinitions(): Collection
    {
        $this->output->writeln('<bg=magenta;fg=white>You now need to define the fields in your publication type:</>');
        $count = 1;
        $fields = Collection::create();
        do {
            $this->line('');
            $this->output->writeln("<bg=cyan;fg=white>Field #$count:</>");

            $field = Collection::create();
            do {
                $field->name = Str::kebab(trim($this->askWithValidation('name', 'Field name', ['required'])));
                $duplicate = $fields->where('name', $field->name)->count();
                if ($duplicate) {
                    $this->error("Field name [$field->name] already exists!");
                }
            } while ($duplicate);

            $this->line('Field type:');
            $this->line('  1 - String');
            $this->line('  2 - Boolean ');
            $this->line('  3 - Integer');
            $this->line('  4 - Float');
            $this->line('  5 - Datetime (YYYY-MM-DD (HH:MM:SS))');
            $this->line('  6 - URL');
            $this->line('  7 - Array');
            $this->line('  8 - Text');
            $this->line('  9 - Local Image');
            $this->line('  10 - Tag (select value from list)');
            $type = (int) $this->askWithValidation('type', 'Field type (1-10)', ['required', 'integer', 'between:1,10'], 1);

            if ($type < 10) {
                do {
                    $field->min = trim($this->askWithValidation('min', 'Min value (for strings, this refers to string length)', ['required', 'string'], 0));
                    $field->max = trim($this->askWithValidation('max', 'Max value (for strings, this refers to string length)', ['required', 'string'], 0));
                    $lengthsValid = true;
                    if ($field->max < $field->min) {
                        $lengthsValid = false;
                        $this->output->warning('Field length [max] must be [>=] than [min]');
                    }
                } while (! $lengthsValid);
            } else {
                $allTags = PublicationService::getAllTags();
                $offset = 1;
                foreach ($allTags as $k=>$v) {
                    $this->line("  $offset - $k");
                    $offset++;
                }
                $offset--; // The above loop overcounts by 1
                $selected = $this->askWithValidation('tagGroup', 'Tag Group', ['required', 'integer', "between:1,$offset"], 0);
                $field->tagGroup = $allTags->keys()->{$selected - 1};
                $field->min = 0;
                $field->max = 0;
            }
            $addAnother = $this->askWithValidation('addAnother', '<bg=magenta;fg=white>Add another field (y/n)</>', ['required', 'string', 'in:y,n'], 'y');

            // map field choice to actual field type
            $field->type = match ($type) {
                1  => 'string',
                2  => 'boolean',
                3  => 'integer',
                4  => 'float',
                5  => 'datetime',
                6  => 'url',
                7  => 'array',
                8  => 'text',
                9  => 'image',
                10 => 'tag',
            };

            $fields->add($field);
            $count++;
        } while (strtolower($addAnother) != 'n');

        return $fields;
    }
}
