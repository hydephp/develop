<?php

declare(strict_types=1);

/**
 * @internal
 */
class HydeStan
{
    const VERSION = '0.0.0-dev';
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
}

class NoFixMeAnalyser
{
    public function run(string $file, string $contents): array
    {
        $errors = [];

        $searches = [
            'fixme',
            'fix me',
        ];

        $contents = strtolower($contents);

        foreach ($searches as $search) {
            if (str_contains($contents, $search)) {
                $errors[] = 'Found '.$search.' in '.$file;
            }
        }

        return $errors;
    }
}

function dd(...$args): never
{
    foreach ($args as $arg) {
        var_dump($arg);
    }

    exit(1);
}
