<?php

declare(strict_types=1);

namespace Hyde\Publications\Commands;

use Hyde\Publications\Commands\Helpers\InputStreamHandler;
use Hyde\Publications\Models\PublicationTags;
use Hyde\Publications\Publications;
use LaravelZero\Framework\Commands\Command;
use RuntimeException;

use function implode;
use function sprintf;

/**
 * Hyde Command to create a new publication type.
 *
 * @see \Hyde\Publications\Testing\Feature\MakePublicationTagCommandTest
 */
class MakePublicationTagCommand extends ValidatingCommand
{
    /** @var string */
    protected $signature = 'make:publicationTag';

    /** @var string */
    protected $description = 'Create a new publication type tag definition';

    protected array $tags;

    public function safeHandle(): int
    {
        $this->title('Creating a new Publication Type Tag!');

        $this->collectTags();

        $this->printSelectionInformation();

        $this->saveTagsToDisk();

        return Command::SUCCESS;
    }

    protected function collectTags(): void
    {
        $this->info(sprintf('Enter the tag values: (%s)', InputStreamHandler::terminationMessage()));
        $this->tags = InputStreamHandler::call();
    }

    protected function printSelectionInformation(): void
    {
        $this->line(sprintf('<comment>Adding the following tags</comment>: %s', implode(', ', $this->tags)));
        $this->newLine();
    }

    protected function saveTagsToDisk(): void
    {
        $this->infoComment(sprintf('Saving tag data to [%s]',
            \Hyde\Console\Concerns\Command::fileLink('tags.yml')
        ));

        (new PublicationTags)->addTags($this->tags)->save();
    }
}
