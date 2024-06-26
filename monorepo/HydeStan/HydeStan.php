<?php

declare(strict_types=1);

use Desilva\Console\Console;

/**
 * @internal
 */
final class HydeStan
{
    const VERSION = '0.0.0-dev';

    private array $files;
    private array $errors = [];
    private int $scannedLines = 0;
    private int $aggregateLines = 0;
    private Console $console;
    private static array $warnings = [];
    private static self $instance;

    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function __construct(private readonly bool $debug = false)
    {
        self::$instance = $this;

        $this->console = new Console();

        $this->console->info(sprintf('HydeStan v%s is running!', self::VERSION));
        $this->console->newline();
    }

    public function __destruct()
    {
        $this->console->newline();
        $this->console->info(sprintf('HydeStan has exited after scanning %s total (and %s aggregate) lines in %s files. Total expressions analysed: %s',
            number_format($this->scannedLines),
            number_format($this->aggregateLines),
            number_format(count($this->files)),
            number_format(AnalysisStatisticsContainer::getExpressionsAnalysed()),
        ));

        if (count(self::$warnings) > 0) {
            // Forward warnings to GitHub Actions
            $this->console->line(sprintf("\n%s", implode("\n", self::$warnings)));
        }
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
            number_format(memory_get_peak_usage(true) / 1024, 2)
        ));

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

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    public function addErrors(array $errors): void
    {
        $this->errors = array_merge($this->errors, $errors);
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
        $fileAnalysers = [
            new NoFixMeAnalyser($file, $contents),
            new UnImportedFunctionAnalyser($file, $contents),
        ];

        foreach ($fileAnalysers as $analyser) {
            if ($this->debug) {
                $this->console->debugComment('Running  '.$analyser::class);
            }

            $analyser->run($file, $contents);
            AnalysisStatisticsContainer::countedLines(substr_count($contents, "\n"));

            foreach (explode("\n", $contents) as $lineNumber => $line) {
                $lineAnalysers = [
                    new NoTestReferenceAnalyser($file, $lineNumber, $line),
                ];

                foreach ($lineAnalysers as $analyser) {
                    AnalysisStatisticsContainer::countedLine();
                    $analyser->run($file, $lineNumber, $line);
                    $this->aggregateLines++;
                }
            }
        }

        $this->scannedLines += substr_count($contents, "\n");
        $this->aggregateLines += (substr_count($contents, "\n") * count($fileAnalysers));
    }

    private function getFileContents(string $file): string
    {
        return file_get_contents(BASE_PATH.'/'.$file);
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

abstract class Analyser
{
    protected function fail(string $error): void
    {
        HydeStan::getInstance()->addError($error);
    }
}

abstract class FileAnalyser extends Analyser implements FileAnalyserContract
{
    public function __construct(protected string $file, protected string $contents)
    {
        //
    }
}

abstract class LineAnalyser extends Analyser implements LineAnalyserContract
{
    public function __construct(protected string $file, protected int $lineNumber, protected string $line)
    {
        //
    }
}

class NoFixMeAnalyser extends FileAnalyser
{
    public function run(string $file, string $contents): void
    {
        $searches = [
            'fixme',
            'fix me',
            'fix-me',
        ];

        $contents = strtolower($contents);

        foreach ($searches as $search) {
            AnalysisStatisticsContainer::analysedExpression();
            if (str_contains($contents, $search)) {
                // Get line number of marker by counting new \n tags before it
                $stringBeforeMarker = substr($contents, 0, strpos($contents, $search));
                $lineNumber = substr_count($stringBeforeMarker, "\n") + 1;

                $this->fail("Found $search in $file on line $lineNumber");

                HydeStan::addActionsMessage('warning', $file, $lineNumber, 'HydeStan: NoFixMeError', 'This line has been marked as needing fixing. Please fix it before merging.');

                // Todo we might want to check for more errors after the first marker
            }
        }
    }
}

class UnImportedFunctionAnalyser extends FileAnalyser
{
    public function run(string $file, string $contents): void
    {
        $lines = explode("\n", $contents);

        $functionImports = [];
        foreach ($lines as $line) {
            AnalysisStatisticsContainer::analysedExpression();
            if (str_starts_with($line, 'use function ')) {
                $functionImports[] = rtrim(substr($line, 13), ';');
            }
        }

        $calledFunctions = [];
        foreach ($lines as $line) {
            // Find all function calls
            preg_match_all('/([a-zA-Z0-9_]+)\(/', $line, $matches);
            AnalysisStatisticsContainer::analysedExpressions(count($matches[1]));

            foreach ($matches[1] as $match) {
                AnalysisStatisticsContainer::analysedExpression();
                if (! str_contains($line, '->')) {
                    $calledFunctions[] = $match;
                }
            }
        }

        // Filter out everything that is not global function
        $calledFunctions = array_filter($calledFunctions, fn ($calledFunction) => function_exists($calledFunction));
        $calledFunctions = array_unique($calledFunctions);

        foreach ($calledFunctions as $calledFunction) {
            AnalysisStatisticsContainer::analysedExpression();
            if (! in_array($calledFunction, $functionImports)) {
                echo("Found unimported function '$calledFunction' in ".realpath(__DIR__.'/../../packages/framework/'.$file))."\n";
            }
        }
    }
}

class NoTestReferenceAnalyser extends LineAnalyser
{
    public function run(string $file, int $lineNumber, string $line): void
    {
        AnalysisStatisticsContainer::analysedExpressions(1);

        if (str_starts_with($line, ' * @see') && str_ends_with($line, 'Test')) {
            AnalysisStatisticsContainer::analysedExpressions(1);
            $this->fail(sprintf('Test class %s is referenced in %s:%s', trim(substr($line, 7)),
                realpath(__DIR__.'/../../packages/framework/'.$file) ?: $file, $lineNumber + 1));
        }
    }
}

class AnalysisStatisticsContainer
{
    private static int $linesCounted = 0;
    private static float $expressionsAnalysed = 0;

    public static function countedLine(): void
    {
        self::$linesCounted++;
    }

    public static function countedLines(int $count): void
    {
        self::$linesCounted += $count;
    }

    public static function analysedExpression(): void
    {
        self::$expressionsAnalysed++;
    }

    public static function analysedExpressions(float $countOrEstimate): void
    {
        self::$expressionsAnalysed += $countOrEstimate;
    }

    public static function getLinesCounted(): int
    {
        return self::$linesCounted;
    }

    public static function getExpressionsAnalysed(): int
    {
        return (int) round(self::$expressionsAnalysed);
    }
}

interface FileAnalyserContract
{
    public function __construct(string $file, string $contents);

    public function run(string $file, string $contents): void;
}

interface LineAnalyserContract
{
    public function __construct(string $file, int $lineNumber, string $line);

    public function run(string $file, int $lineNumber, string $line): void;
}
