<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Commands\Interfaces\CommandHandleInterface;
use Hyde\Console\Concerns\ValidatingCommand;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Hyde;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use function Safe\json_decode;
use function Safe\json_encode;
use function Safe\file_put_contents;

/**
 * Hyde Command to create a new publication type.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\MakePublicationTypeCommandTest
 */
class MakePublicationTagCommand extends ValidatingCommand implements CommandHandleInterface
{
    /** @var string */
    protected $signature = 'make:publicationTag';

    /** @var string */
    protected $description = 'Create a new publication type tag definition';

    public function handle(): int
    {
        $this->title('Creating a new Publication Type Tag!');

        $filename = Hyde::pathToRelative('tags.json');
        $tags     = PublicationService::getAllTags();
        $tagName  = $this->askWithValidation('name', 'Tag name', ['required', 'string']);
        if (isset($tags[$tagName])) {
            $this->output->error("Tag [$tagName] already exists");
            return Command::FAILURE;
        }

        $lines = [];
        $this->output->writeln('<bg=magenta;fg=white>Enter the tag values (end with an empty line):</>');
        do {
            $line = Str::replace("\n", '', fgets(STDIN));
            if ($line === '') {
                break;
            }
            $lines[] = trim($line);
        } while (true);
        $tags[$tagName] = $lines;

        $this->output->writeln(sprintf('Saving tag data to [%s]', $filename));
        file_put_contents($filename, json_encode($tags, JSON_PRETTY_PRINT));

        return Command::SUCCESS;
    }
}
