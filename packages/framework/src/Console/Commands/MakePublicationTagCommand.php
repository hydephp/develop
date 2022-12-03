<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use function array_merge;
use Hyde\Console\Commands\Helpers\InputStreamHandler;
use Hyde\Console\Commands\Interfaces\CommandHandleInterface;
use Hyde\Console\Concerns\ValidatingCommand;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Hyde;
use function implode;
use LaravelZero\Framework\Commands\Command;
use function Safe\json_encode;
use function sprintf;

/**
 * Hyde Command to create a new publication type.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\MakePublicationTagCommandTest
 */
class MakePublicationTagCommand extends ValidatingCommand implements CommandHandleInterface
{
    /** @var string */
    protected $signature = 'make:publicationTag {tagName? : The name of the tag to create}';

    /** @var string */
    protected $description = 'Create a new publication type tag definition';

    protected array $tags;
    protected string $tagName;

    public function handle(): int
    {
        $this->title('Creating a new Publication Type Tag!');

        $this->tagName = $this->getTagName();

        if (PublicationService::getAllTags()->has($this->tagName)) {
            $this->error("Tag [$this->tagName] already exists");

            return Command::FAILURE;
        }

        $this->collectTags();

        $this->printSelectionInformation();

        $this->saveTagsToDisk();

        return Command::SUCCESS;
    }

    protected function getTagName(): string
    {
        if ($this->argument('tagName')) {
            $value = $this->argument('tagName');
            $this->infoComment('Using tag name', $value, 'from command line argument');
            $this->newLine();

            return $value;
        }

        return $this->askWithValidation('name', 'Tag name', ['required', 'string']);
    }

    protected function collectTags(): void
    {
        $this->info('Enter the tag values: (end with an empty line)');
        $lines = InputStreamHandler::call();
        $tags[$this->tagName] = $lines;
        $this->tags = $tags;
    }

    protected function printSelectionInformation(): void
    {
        $this->line('Adding the following tags:');
        foreach ($this->tags as $tag => $values) {
            $this->line(sprintf('  <comment>%s</comment>: %s', $tag, implode(', ', $values)));
        }
        $this->newLine();
    }

    protected function saveTagsToDisk(): void
    {
        $this->infoComment('Saving tag data to',
            DiscoveryService::createClickableFilepath(Hyde::path('tags.json'))
        );

        Filesystem::putContents('tags.json', json_encode(array_merge(
            PublicationService::getAllTags()->toArray(), $this->tags
        ), JSON_PRETTY_PRINT));
    }
}
