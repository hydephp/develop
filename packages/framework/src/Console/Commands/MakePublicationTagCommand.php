<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use function array_merge;
use Hyde\Console\Commands\Helpers\InputStreamHandler;
use Hyde\Console\Concerns\ValidatingCommand;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Hyde;
use function implode;
use LaravelZero\Framework\Commands\Command;
use RuntimeException;
use function Safe\json_encode;
use function sprintf;

/**
 * Hyde Command to create a new publication type.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\MakePublicationTagCommandTest
 */
class MakePublicationTagCommand extends ValidatingCommand
{
    /** @var string */
    protected $signature = 'make:publicationTag {tagName? : The name of the tag to create}';

    /** @var string */
    protected $description = 'Create a new publication type tag definition';

    protected array $tags;
    protected string $tagName;

    public function safeHandle(): int
    {
        $this->title('Creating a new Publication Type Tag!');

        $this->getTagName();

        $this->validateTagName();

        $this->collectTags();

        $this->printSelectionInformation();

        $this->saveTagsToDisk();

        return Command::SUCCESS;
    }

    protected function getTagName(): void
    {
        $this->tagName = $this->getTagNameFromArgument($this->argument('tagName'))
            ?? $this->askWithValidation('name', 'Tag name', ['required', 'string']);
    }

    protected function getTagNameFromArgument(?string $value): ?string
    {
        if ($value) {
            $this->infoComment('Using tag name', $value, 'from command line argument');
            $this->newLine();

            return $value;
        }

        return null;
    }

    protected function validateTagName(): void
    {
        if (PublicationService::getAllTags()->has($this->tagName)) {
            throw new RuntimeException("Tag [$this->tagName] already exists");
        }
    }

    protected function collectTags(): void
    {
        $this->info('Enter the tag values: (end with an empty line)');
        $this->tags = [$this->tagName => InputStreamHandler::call()];
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
