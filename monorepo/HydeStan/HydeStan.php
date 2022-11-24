<?php

declare(strict_types=1);

/**
 * @internal
 */
class HydeStan
{
    const VERSION = '0.0.0-dev';
    protected static array $warnings;
    protected array $errors = [];
    protected array $files;
    protected Console $console;

    public function __construct(protected bool $debug = false)
    {
        $this->console = new Console();

        $this->console->info(sprintf('HydeStan v%s is running!', self::VERSION));
        $this->console->newline();
    }

    public function __destruct()
    {
        $this->console->newline();
        $this->console->info('HydeStan has exited.');

        // Forward warnings to GitHub Actions
        echo "\n".implode("\n", self::$warnings)."\n";
    }

    public function run(): void
    {
        $time = microtime(true);

        $this->files = $this->getFiles();

        foreach ($this->files as $file) {
            if ($this->debug) {
                $this->console->debug('Analysing file: '.$file);
            }

            $this->analyseFile($file, $this->getFileContents($file));
        }

        $endTime = microtime(true) - $time;
        $this->console->info(sprintf('HydeStan has finished in %s seconds (%sms) using %s KB RAM',
            number_format($endTime, 2),
            number_format($endTime * 1000, 2),
            number_format(memory_get_peak_usage(true) / 1024, 2))
        );

        if ($this->hasErrors()) {
            $this->console->error(sprintf('HydeStan has found %s errors!', count($this->errors)));

            foreach ($this->errors as $error) {
                $this->console->warn($error);
            }
        } else {
            $this->console->info('HydeStan has found no errors!');
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function getFiles(): array
    {
        $files = [];

        $directory = new RecursiveDirectoryIterator(BASE_PATH.'/src');
        $iterator = new RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

        foreach ($regex as $file) {
            $files[] = substr($file[0], strlen(BASE_PATH) + 1);
        }

        return $files;
    }

    private function analyseFile(string $file, string $contents): void
    {
        foreach ($this->analysers() as $analyser) {
            if ($this->debug) {
                $this->console->debugComment('Running  '.$analyser::class);
            }

            $result = $analyser->run($file, $contents);
            foreach ($result as $error) {
                if ($this->debug) {
                    $this->console->debugComment('Adding error: '.$error);
                }
                $this->errors[] = $error;
            }
        }
    }

    private function getFileContents(string $file): string
    {
        return file_get_contents(BASE_PATH.'/'.$file);
    }

    private function analysers(): array
    {
        return [
            new NoFixMeAnalyser(),
        ];
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public static function addActionsMessage(string $level, string $file, int $lineNumber, string $title, string $message): void
    {
        // https://docs.github.com/en/actions/using-workflows/workflow-commands-for-github-actions#setting-a-warning-message
        // $template = '::warning file={name},line={line},endLine={endLine},title={title}::{message}';
        self::$warnings[] = sprintf("::$level file=%s,line=%s,endLine=%s,title=%s::%s", 'packages/framework/'.str_replace('\\', '/', $file), $lineNumber, $lineNumber, $title, $message);
    }
}

class NoFixMeAnalyser
{
    public function run(string $file, string $contents): array
    {
        $errors = [];

        $searches = [
            'fixme',
            'fix me',
            'fix-me',
        ];

        $contents = strtolower($contents);

        foreach ($searches as $search) {
            if (str_contains($contents, $search)) {
                // Get line number of marker by counting new \n tags before it
                $stringBeforeMarker = substr($contents, 0, strpos($contents, $search));
                $lineNumber = substr_count($stringBeforeMarker, "\n") + 1;

                $errors[] = "Found $search in $file on line $lineNumber";

                HydeStan::addActionsMessage('warning', $file, $lineNumber, 'HydeStan: NoFixMeError', 'This line has been marked as needing fixing. Please fix it before merging.');

                // Todo we might want to check for more errors after the first marker
            }
        }

        return $errors;
    }
}
