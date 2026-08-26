<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Console\Commands\PublishCommand;
use Hyde\Console\Helpers\ConsoleHelper;
use Hyde\Console\Helpers\PublisherConsole;
use Hyde\Testing\TestCase;
use Illuminate\Console\OutputStyle;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(PublisherConsole::class)]
class PublisherConsoleTest extends TestCase
{
    protected function tearDown(): void
    {
        ConsoleHelper::clearMocks();
        PublisherConsolePromptReset::resetFallbacks();

        parent::tearDown();
    }

    public function testPublishingContinuesWhenNoSelectedFilesAreModified(): void
    {
        [$console, $output] = $this->makeConsole();

        $this->assertSame([], $console->resolveBlocked([], 'Cancelled. No files were published.'));
        $this->assertSame('', $output->fetch());
    }

    public function testForceAllowsModifiedFilesToBeOverwrittenWithoutPrompting(): void
    {
        $blocked = [$this->blockedFile('resources/views/vendor/hyde/layouts/app.blade.php')];
        [$console, $output] = $this->makeConsole(['--force' => true]);

        $this->assertSame($blocked, $console->resolveBlocked($blocked, 'Cancelled. No files were published.'));
        $this->assertSame('', $output->fetch());
    }

    public function testNonInteractiveRunRefusesToOverwriteModifiedFilesWithoutForce(): void
    {
        $blocked = [
            $this->blockedFile('resources/views/vendor/hyde/layouts/app.blade.php'),
            $this->blockedFile('_pages/index.blade.php'),
        ];
        [$console, $output] = $this->makeConsole();

        $this->assertNull($console->resolveBlocked($blocked, 'Cancelled. No files were published.'));

        $contents = $output->fetch();
        $this->assertStringContainsString('Cannot overwrite modified files without --force:', $contents);
        $this->assertStringContainsString('resources/views/vendor/hyde/layouts/app.blade.php', $contents);
        $this->assertStringContainsString('_pages/index.blade.php', $contents);
        $this->assertStringContainsString('Run again with --force to overwrite.', $contents);
    }

    public function testInteractiveRunCanSkipModifiedFiles(): void
    {
        $blocked = [$this->blockedFile('_pages/index.blade.php')];
        [$console, $output] = $this->makeInteractiveConsole([Key::ENTER]);

        $this->assertSame([], $console->resolveBlocked($blocked, 'Cancelled. No pages were published.'));
        $this->assertSame('', $output->fetch());

        Prompt::assertOutputContains('1 selected files already exist and appear modified.');
        Prompt::assertOutputContains('Skip modified files');
    }

    public function testInteractiveRunCanOverwriteModifiedFiles(): void
    {
        $blocked = [$this->blockedFile('resources/views/vendor/hyde/layouts/app.blade.php')];
        [$console, $output] = $this->makeInteractiveConsole([Key::DOWN, Key::ENTER]);

        $this->assertSame($blocked, $console->resolveBlocked($blocked, 'Cancelled. No views were published.'));
        $this->assertSame('', $output->fetch());
    }

    public function testInteractiveRunCanCancelBeforeOverwritingModifiedFiles(): void
    {
        $blocked = [$this->blockedFile('_pages/index.blade.php')];
        [$console, $output] = $this->makeInteractiveConsole([Key::DOWN, Key::DOWN, Key::ENTER]);

        $this->assertNull($console->resolveBlocked($blocked, 'Cancelled. No pages were published.'));
        $this->assertStringContainsString('Cancelled. No pages were published.', $output->fetch());
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{PublisherConsole, BufferedOutput}
     */
    protected function makeConsole(array $options = [], bool $interactive = false): array
    {
        $command = $this->app->make(PublishCommand::class);
        $input = new ArrayInput($options, $command->getDefinition());
        $input->setInteractive($interactive);
        $output = new BufferedOutput();

        $command->setLaravel($this->app);
        $command->setInput($input);
        $command->setOutput(new OutputStyle($input, $output));

        return [new PublisherConsole($command, $input), $output];
    }

    /**
     * @param  array<string>  $keys
     * @return array{PublisherConsole, BufferedOutput}
     */
    protected function makeInteractiveConsole(array $keys): array
    {
        ConsoleHelper::mockWindowsOs(false);
        PublisherConsolePromptReset::resetFallbacks();
        Prompt::fake($keys);

        return $this->makeConsole(interactive: true);
    }

    /** @return array{source: string, target: string, absolute: string} */
    protected function blockedFile(string $target): array
    {
        return [
            'source' => 'vendor/hyde/framework/resources/views/source.blade.php',
            'target' => $target,
            'absolute' => base_path($target),
        ];
    }
}

abstract class PublisherConsolePromptReset extends Prompt
{
    public static function resetFallbacks(): void
    {
        static::$shouldFallback = false;
    }
}
