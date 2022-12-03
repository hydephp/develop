<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Commands\Helpers\InputStreamHandler;
use function array_merge;
use Hyde\Console\Commands\Interfaces\CommandHandleInterface;
use Hyde\Console\Concerns\ValidatingCommand;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Hyde;
use function implode;
use LaravelZero\Framework\Commands\Command;
use function Safe\file_put_contents;
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

    public function handle(): int
    {
        $this->title('Creating a new Publication Type Tag!');

        $tagName = $this->getTagName();
        $existingTags = PublicationService::getAllTags()->toArray();
        if (isset($existingTags[$tagName])) {
            $this->error("Tag [$tagName] already exists");

            return Command::FAILURE;
        }

        $this->line('<bg=magenta;fg=white>Enter the tag values (end with an empty line):</>');
        $lines = InputStreamHandler::call();
        $tags[$tagName] = $lines;

        $this->line('<bg=magenta;fg=white>Adding the following tags:</>');
        foreach ($tags as $tag => $values) {
            $this->line(sprintf('  <comment>%s</comment>: %s', $tag, implode(', ', $values)));
        }

        $this->newLine();

        $filename = Hyde::path('tags.json');
        $this->infoComment('Saving tag data to', DiscoveryService::createClickableFilepath($filename));

        $tags = array_merge($existingTags, $tags);
        file_put_contents($filename, json_encode($tags, JSON_PRETTY_PRINT));

        return Command::SUCCESS;
    }

    protected function getTagName(): string
    {
        if ($this->argument('tagName')) {
            $value = $this->argument('tagName');
            $this->line("<info>Using tag name</info> [<comment>$value</comment>] <info>from command line argument</info>");
            $this->newLine();

            return $value;
        }

        return $this->askWithValidation('name', 'Tag name', ['required', 'string']);
    }
}
