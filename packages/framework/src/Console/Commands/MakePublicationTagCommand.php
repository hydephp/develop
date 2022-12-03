<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Commands\Interfaces\CommandHandleInterface;
use Hyde\Console\Concerns\ValidatingCommand;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Hyde;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

use function array_merge;
use function explode;
use function implode;
use function Safe\file_put_contents;
use function Safe\json_encode;
use function sprintf;

/**
 * Hyde Command to create a new publication type.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\MakePublicationTagCommandTest
 *
 * @todo Add dynamic support for detecting and using comma separated values?
 */
class MakePublicationTagCommand extends ValidatingCommand implements CommandHandleInterface
{
    /** @var string */
    protected $signature = 'make:publicationTag {tagName? : The name of the tag to create}';

    /** @var string */
    protected $description = 'Create a new publication type tag definition';

    /** @internal Allows for mocking of the standard input stream */
    private static ?array $streamBuffer = null;

    public function handle(): int
    {
        $this->title('Creating a new Publication Type Tag!');

        $tagName = $this->getTagName();
        $existingTags = PublicationService::getAllTags()->toArray();
        if (isset($existingTags[$tagName])) {
            $this->error("Tag [$tagName] already exists");

            return Command::FAILURE;
        }

        $lines = [];
        $this->line('<bg=magenta;fg=white>Enter the tag values (end with an empty line):</>');
        $lines          = $this->getLinesFromInputStream($lines);
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

    protected function getLinesFromInputStream(array $lines): array
    {
        do {
            $line = Str::replace(["\n", "\r"], '', $this->readInputStream());
            if ($line === '') {
                break;
            }
            $lines[] = trim($line);
        } while (true);
        return $lines;
    }

    /** @codeCoverageIgnore Allows for mocking of the standard input stream */
    protected function readInputStream(): array|string|false
    {
        if (self::$streamBuffer)
        {
            return array_shift(self::$streamBuffer);
        }
        return fgets(STDIN);
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

    /** @internal Allows for mocking of the standard input stream */
    public static function mockInput(string $input): void
    {
        self::$streamBuffer = explode("\n", $input);
    }
}
