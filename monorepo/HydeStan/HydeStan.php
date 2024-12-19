<?php

declare(strict_types=1);

use Desilva\Console\Console;

require_once __DIR__.'/includes/contracts.php';
require_once __DIR__.'/includes/helpers.php';

/**
 * @internal Custom static analysis tool for the HydePHP Development Monorepo.
 */
final class HydeStan
{
    private const FILE_ANALYSERS = [
        NoFixMeAnalyser::class,
        UnImportedFunctionAnalyser::class,
        NoGlobBraceAnalyser::class,
    ];

    private const TEST_FILE_ANALYSERS = [
        NoFixMeAnalyser::class,
        NoUsingAssertEqualsForScalarTypesTestAnalyser::class,
        NoParentSetUpTearDownInUnitTestCaseAnalyser::class,
        UnitTestCaseExtensionAnalyzer::class,
    ];

    private const LINE_ANALYSERS = [
        NoTestReferenceAnalyser::class,
        NoHtmlExtensionInHydePHPLinksAnalyser::class,
        NoExtraWhitespaceInCompressedPhpDocAnalyser::class,
    ];

    private array $files;
    private array $testFiles;
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

        $this->console->info('HydeStan is running!');
        $this->console->newline();
    }

    public function __destruct()
    {
        $this->console->newline();
        $this->console->info(sprintf('HydeStan has exited after scanning %s total (and %s aggregate) lines in %s files.',
            number_format($this->scannedLines),
            number_format($this->aggregateLines),
            number_format(count($this->files) + count($this->testFiles)),
        ));

        $this->console->info(sprintf('Total expressions analysed: %s',
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

        $this->console->info('Finished analyzing files!');

        $this->runTestStan();

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
        return recursiveFileFinder('src');
    }

    private function getTestFiles(): array
    {
        return recursiveFileFinder('tests');
    }

    private function analyseFile(string $file, string $contents): void
    {
        foreach (self::FILE_ANALYSERS as $fileAnalyserClass) {
            $fileAnalyser = new $fileAnalyserClass($file, $contents);

            if ($this->debug) {
                $this->console->debugComment('Running  '.$fileAnalyser::class);
            }

            $fileAnalyser->run($file, $contents);
            AnalysisStatisticsContainer::countedLines(substr_count($contents, "\n"));

            foreach (explode("\n", $contents) as $lineNumber => $line) {
                foreach (self::LINE_ANALYSERS as $lineAnalyserClass) {
                    $lineAnalyser = new $lineAnalyserClass($file, $lineNumber, $line);
                    AnalysisStatisticsContainer::countedLine();
                    $lineAnalyser->run($file, $lineNumber, $line);
                    $this->aggregateLines++;
                }
            }
        }

        $this->scannedLines += substr_count($contents, "\n");
        $this->aggregateLines += (substr_count($contents, "\n") * count(self::FILE_ANALYSERS));
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

    protected function runTestStan(): void
    {
        $this->console->info('TestStan: Analyzing test files...');

        $this->testFiles = $this->getTestFiles();

        foreach ($this->testFiles as $file) {
            $this->analyseTestFile($file, $this->getFileContents($file));
        }

        $this->console->info('TestStan: Finished analyzing test files!');
    }

    private function analyseTestFile(string $file, string $contents): void
    {
        foreach (self::TEST_FILE_ANALYSERS as $fileAnalyserClass) {
            $fileAnalyser = new $fileAnalyserClass($file, $contents);

            if ($this->debug) {
                $this->console->debugComment('Running  '.$fileAnalyser::class);
            }

            $fileAnalyser->run($file, $contents);
            AnalysisStatisticsContainer::countedLines(substr_count($contents, "\n"));

            foreach (explode("\n", $contents) as $lineNumber => $line) {
                // No line analysers defined for test files in the original code
            }
        }

        $this->scannedLines += substr_count($contents, "\n");
        $this->aggregateLines += (substr_count($contents, "\n") * count(self::TEST_FILE_ANALYSERS));
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

class NoHtmlExtensionInHydePHPLinksAnalyser extends LineAnalyser
{
    public function run(string $file, int $lineNumber, string $line): void
    {
        AnalysisStatisticsContainer::analysedExpressions(1);

        if (str_contains($line, 'https://hydephp.com/') && str_contains($line, '.html')) {
            AnalysisStatisticsContainer::analysedExpressions(1);

            $this->fail(sprintf('HTML extension used in URL at %s',
                fileLink(BASE_PATH.'/packages/framework/'.$file, $lineNumber + 1)
            ));

            HydeStan::addActionsMessage('warning', $file, $lineNumber + 1, 'HydeStan: NoHtmlExtensionError', 'URL contains .html extension. Consider removing it.');
        }
    }
}

class NoExtraWhitespaceInCompressedPhpDocAnalyser extends LineAnalyser
{
    public function run(string $file, int $lineNumber, string $line): void
    {
        AnalysisStatisticsContainer::analysedExpressions(1);

        if (str_contains($line, '/**  ')) {
            $this->fail(sprintf('Extra whitespace in compressed PHPDoc comment at %s',
                fileLink(BASE_PATH.'/packages/framework/'.$file, $lineNumber + 1)
            ));

            HydeStan::addActionsMessage('warning', $file, $lineNumber + 1, 'HydeStan: ExtraWhitespaceInPhpDocError', 'Extra whitespace found in compressed PHPDoc comment.');
        }
    }
}

class NoUsingAssertEqualsForScalarTypesTestAnalyser extends FileAnalyser // Todo: Extend line analyser instead? Would allow for checking for more errors after the first error
{
    public function run(string $file, string $contents): void
    {
        $searches = [
            "assertEquals('",
            'assertEquals("',
            'assertEquals(null,',
            'assertSame(null,',
            'assertEquals(true,',
            'assertSame(true,',
            'assertEquals(false,',
            'assertSame(false,',
        ];

        foreach ($searches as $search) {
            AnalysisStatisticsContainer::analysedExpression();

            if (str_contains($contents, $search)) {
                // Get line number of marker by counting new \n tags before it
                $stringBeforeMarker = substr($contents, 0, strpos($contents, $search));
                $lineNumber = substr_count($stringBeforeMarker, "\n") + 1;

                // Get the line contents
                $line = explode("\n", $contents)[$lineNumber - 1];

                // Check for false positives
                $commonlyStringCastables = ['$article', '$document', 'getXmlElement()', '$url->loc', '$page->markdown', '$post->data(\'author\')'];

                if (check_str_contains_any($commonlyStringCastables, $line)) {
                    continue;
                }

                if (str_contains($search, 'null')) {
                    $call = rtrim($search, ',').')';
                    $message = 'Found '.$call.' instead of assertNull in %s.';
                    $this->fail(sprintf($message, fileLink($file, $lineNumber)));
                } elseif (str_contains($search, 'true')) {
                    $call = rtrim($search, ',').')';
                    $message = 'Found '.$call.' instead of assertTrue in %s.';
                    $this->fail(sprintf($message, fileLink($file, $lineNumber)));
                } elseif (str_contains($search, 'false')) {
                    $call = rtrim($search, ',').')';
                    $message = 'Found '.$call.' instead of assertFalse in %s.';
                    $this->fail(sprintf($message, fileLink($file, $lineNumber)));
                } else {
                    $message = 'Found %s instead assertSame for scalar type in %s';
                    $this->fail(sprintf($message, trim($search, "()'"), fileLink($file, $lineNumber)));
                }
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
                echo sprintf("Found unimported function '$calledFunction' in %s\n", realpath(__DIR__.'/../../packages/framework/'.$file));
            }
        }
    }
}

class NoGlobBraceAnalyser extends FileAnalyser
{
    public function run(string $file, string $contents): void
    {
        $lines = explode("\n", $contents);

        foreach ($lines as $lineNumber => $line) {
            AnalysisStatisticsContainer::analysedExpression();

            if (str_contains($line, 'GLOB_BRACE')) {
                $this->fail(sprintf('Usage of `GLOB_BRACE` found in %s at line %d. This feature is not supported on all systems and should be avoided.',
                    realpath(BASE_PATH.'/'.$file),
                    $lineNumber + 1
                ));

                HydeStan::addActionsMessage('error', $file, $lineNumber + 1, 'HydeStan: NoGlobBraceError', '`GLOB_BRACE` is not supported on all systems. Consider refactoring to avoid it.');
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
            $this->fail(sprintf('Test class %s is referenced in %s:%s',
                trim(substr($line, 7)),
                realpath(__DIR__.'/../../packages/framework/'.$file) ?: $file,
                $lineNumber + 1
            ));
        }
    }
}

class NoParentSetUpTearDownInUnitTestCaseAnalyser extends FileAnalyser
{
    public function run(string $file, string $contents): void
    {
        if (! str_contains($contents, 'extends UnitTestCase')) {
            return;
        }

        $methods = ['setUp', 'tearDown'];

        foreach ($methods as $method) {
            AnalysisStatisticsContainer::analysedExpression();
            if (str_contains($contents, "parent::$method()")) {
                $lineNumber = substr_count(substr($contents, 0, strpos($contents, $method)), "\n") + 1;
                $this->fail(sprintf("Found '%s' method in UnitTestCase at %s", "parent::$method()", fileLink($file, $lineNumber, false)));
                HydeStan::addActionsMessage('error', $file, $lineNumber, "HydeStan: UnnecessaryParent{$method}MethodError", "{$method} method in UnitTestCase performs no operation and should be removed.");
            }
        }
    }
}

class UnitTestCaseExtensionAnalyzer extends FileAnalyser
{
    public function run(string $file, string $contents): void
    {
        // Check if the file is in the unit namespace
        if (! str_contains($file, 'Unit')) {
            AnalysisStatisticsContainer::analysedExpression();

            return;
        }

        AnalysisStatisticsContainer::analysedExpression();

        // Unit view tests are allowed to extend TestCase
        if (str_contains($file, 'ViewTest')) {
            AnalysisStatisticsContainer::analysedExpression();

            return;
        }

        AnalysisStatisticsContainer::analysedExpression();

        // Check if the class extends TestCase but not UnitTestCase
        if (str_contains($contents, 'extends TestCase') && ! str_contains($contents, 'extends UnitTestCase')) {
            AnalysisStatisticsContainer::analysedExpressions(2);

            $lineNumber = substr_count(substr($contents, 0, strpos($contents, 'extends TestCase')), "\n") + 1;

            todo(realpath(__DIR__.'/../../packages/framework/'.$file), $lineNumber, 'Refactor unit test to extend UnitTestCase instead of TestCase');

            echo sprintf('Test in unit namespace extends TestCase instead of UnitTestCase at %s', fileLink($file, $lineNumber, false))."\n";
        }
    }
}
