<?php

declare(strict_types=1);

namespace Hyde\Publications\Commands;

use Exception;
use Hyde\Hyde;
use Hyde\Publications\Actions\ValidatesPublicationField;
use Hyde\Publications\Models\PublicationFieldDefinition;
use Hyde\Publications\Models\PublicationPage;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\PublicationService;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use function array_filter;
use function basename;
use function collect;
use function filled;
use function glob;
use function memory_get_peak_usage;
use function microtime;
use function round;
use function sprintf;
use function str_repeat;
use function strlen;

/**
 * Hyde Command to validate all publication schema file..
 *
 * @see \Hyde\Publications\Testing\Feature\ValidatePublicationTypesCommandTest
 *
 * @internal This command is not part of the public API and may change without notice.
 */
class ValidatePublicationTypesCommand extends ValidatingCommand
{
    protected const CHECKMARK = "\u{2713}";
    protected const CROSS_MARK = 'x';
    protected const WARNING = "\u{26A0}";

    /** @var string */
    protected $signature = 'validate:publicationTypes {--json : Display results as JSON.}';

    /** @var string */
    protected $description = 'Validate all publication schema files.';

    protected bool $json;

    protected array $results = [];

    public function safeHandle(): int
    {
        $timeStart = microtime(true);

        $this->json = $this->option('json');

        if (! $this->json) {
            $this->title('Validating publications!');
        }

        $this->validateSchemaFiles();

        if ($this->json) {
            $this->outputJson();
        } else {
            $this->displayResults();

            $this->outputSummary($timeStart);
        }

        if (count(array_filter($this->results))) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function displayResults(): void
    {
       // TODO: Split out display logic 
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

    protected function validateSchemaFiles(): void
    {
        /** @see PublicationService::getSchemaFiles() */
        $schemaFiles = glob(Hyde::path(Hyde::getSourceRoot()).'/*/schema.json');

        foreach ($schemaFiles as $number => $schemaFile) {
            $name = basename(dirname($schemaFile));
            $this->infoComment('Validating schema file for', $name);

            $errors = PublicationService::validateSchemaFile($schemaFile, false);

            if (empty($errors['schema'])) {
                $this->line('<info>  No top-level schema errors found</info>');
            } else {
                $this->line(sprintf("  <fg=red>Found %s top-level schema errors:</>", count($errors['schema'])));
                foreach ($errors['schema'] as $error) {
                    $this->line(sprintf("    <fg=red>%s</> <comment>%s</comment>", self::CROSS_MARK, implode(' ', $error)));
                }
            }

            if (empty(array_filter($errors['fields']))) {
                $this->line('<info>  No field-level schema errors found</info>');
            } else {
                $this->newLine();
                $this->line(sprintf("  <fg=red>Found errors in %s field definitions:</>", count($errors['fields'])));
                foreach ($errors['fields'] as $fieldNumber => $fieldErrors) {
                    $this->line(sprintf("    <fg=cyan>Field #%s:</>", $fieldNumber+1));
                    foreach ($fieldErrors as $error) {
                        $this->line(sprintf("      <fg=red>%s</> <comment>%s</comment>", self::CROSS_MARK, implode(' ', $error)));
                    }
                }
            }

            if ($number !== count($schemaFiles) - 1) {
                $this->newLine();
            }
        }
    }
}
