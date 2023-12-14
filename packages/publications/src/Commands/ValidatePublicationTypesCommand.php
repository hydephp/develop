<?php

declare(strict_types=1);

namespace Hyde\Publications\Commands;

use Hyde\Hyde;
use Hyde\Publications\Actions\PublicationSchemaValidator;
use Hyde\Publications\Publications;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;

use function array_filter;
use function basename;
use function count;
use function dirname;
use function glob;
use function json_encode;
use function memory_get_peak_usage;
use function microtime;
use function next;
use function round;
use function sprintf;

/**
 * Hyde command to validate all publication schema file..
 *
 * @see \Hyde\Publications\Testing\Feature\ValidatePublicationTypesCommandTest
 *
 * @internal This command is not part of the public API and may change without notice.
 */
class ValidatePublicationTypesCommand extends ValidatingCommand
{
    protected const CROSS_MARK = 'x';

    /** @var string */
    protected $signature = 'validate:publicationTypes {--json : Display results as JSON.}';

    /** @var string */
    protected $description = 'Validate all publication schema files.';

    protected array $results = [];

    public function safeHandle(): int
    {
        $timeStart = microtime(true);

        if (! $this->option('json')) {
            $this->title('Validating publication schemas!');
        }

        $this->validateSchemaFiles();

        if ($this->option('json')) {
            $this->outputJson();
        } else {
            $this->displayResults();
            $this->outputSummary($timeStart);
        }

        if ($this->countErrors() > 0) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function validateSchemaFiles(): void
    {
        /** Uses the same glob pattern as {@see Publications::getSchemaFiles()} */
        $schemaFiles = glob(Hyde::path(Hyde::getSourceRoot()).'/*/schema.json');

        if (empty($schemaFiles)) {
            throw new InvalidArgumentException('No publication types to validate!');
        }

        foreach ($schemaFiles as $schemaFile) {
            $publicationName = basename(dirname($schemaFile));
            $this->results[$publicationName] = PublicationSchemaValidator::call($publicationName, false)->errors();
        }
    }

    protected function displayResults(): void
    {
        foreach ($this->results as $name => $errors) {
            $this->infoComment("Validating schema file for [$name]");

            $schemaErrors = $errors['schema'];
            if (empty($schemaErrors)) {
                $this->line('<info>  No top-level schema errors found</info>');
            } else {
                $this->displayTopLevelSchemaErrors($schemaErrors);
            }

            $schemaFields = $errors['fields'];
            if (empty(array_filter($schemaFields))) {
                $this->line('<info>  No field-level schema errors found</info>');
            } else {
                $this->newLine();
                $this->displayFieldDefinitionErrors($schemaFields);
            }

            if (next($this->results)) {
                $this->newLine();
            }
        }
    }

    protected function displayTopLevelSchemaErrors(array $schemaErrors): void
    {
        $this->line(sprintf('  <fg=red>Found %s top-level schema errors:</>', count($schemaErrors)));
        foreach ($schemaErrors as $error) {
            $this->line(sprintf('    <fg=red>%s</> <comment>%s</comment>', self::CROSS_MARK, $error));
        }
    }

    protected function displayFieldDefinitionErrors(array $schemaFields): void
    {
        $this->line(sprintf('  <fg=red>Found errors in %s field definitions:</>', count($schemaFields)));
        foreach ($schemaFields as $fieldNumber => $fieldErrors) {
            $this->line(sprintf('    <fg=cyan>Field #%s:</>', $fieldNumber + 1));
            foreach ($fieldErrors as $error) {
                $this->line(sprintf('      <fg=red>%s</> <comment>%s</comment>', self::CROSS_MARK, $error));
            }
        }
    }

    protected function outputSummary($timeStart): void
    {
        $this->newLine();
        $this->info(sprintf('All done in %sms using %sMB peak memory!',
            round((microtime(true) - $timeStart) * 1000),
            round(memory_get_peak_usage() / 1024 / 1024)
        ));
    }

    protected function outputJson(): void
    {
        $this->output->writeln(json_encode($this->results, JSON_PRETTY_PRINT));
    }

    protected function countErrors(): int
    {
        $errors = 0;

        foreach ($this->results as $results) {
            $errors += count($results['schema']);
            $errors += count(array_filter($results['fields']));
        }

        return $errors;
    }
}
