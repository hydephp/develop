<?php

declare(strict_types=1);

namespace Hyde\Publications\Commands;

use Hyde\Hyde;
use Hyde\Publications\Actions\PublicationPageValidator;
use function array_filter;
use function collect;
use function filled;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\PublicationService;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use function microtime;
use function str_repeat;
use function strlen;

/**
 * Hyde Command to validate one or all publications.
 *
 * @see \Hyde\Publications\Testing\Feature\ValidatePublicationsCommandTest
 *
 * @internal This command is not part of the public API and may change without notice.
 */
class ValidatePublicationsCommand extends ValidatingCommand
{
    protected const CHECKMARK = "\u{2713}";
    protected const CROSS_MARK = 'x';
    protected const WARNING = "\u{26A0}";

    /** @var string */
    protected $signature = 'validate:publications
		{publicationType? : The name of the publication type to validate.}
		{--json : Display results as JSON.}';

    /** @var string */
    protected $description = 'Validate all or the specified publication type(s)';

    protected bool $verbose;
    protected bool $json;

    protected array $results = [];

    public function safeHandle(): int
    {
        $timeStart = microtime(true);

        $this->verbose = $this->option('verbose');
        $this->json = $this->option('json');

        if (! $this->json) {
            $this->title('Validating publications!');
        }

        $publicationTypesToValidate = $this->getPublicationTypesToValidate();

        foreach ($publicationTypesToValidate as $publicationType) {
            $this->validatePublicationType($publicationType);
        }

        if ($this->json) {
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

    protected function validatePublicationType(PublicationType $publicationType): void
    {
        foreach (glob(Hyde::path("{$publicationType->getDirectory()}/*.md")) as $publicationFile) {
            $this->results[$publicationType->getIdentifier()][basename($publicationFile, '.md')] = PublicationPageValidator::call($publicationType, basename($publicationFile, '.md'))->errors();

            // TODO Port this
            // Check for extra fields that are not defined in the publication type (we'll add a warning for each one)
            // foreach ($publication->matter->data as $key => $value) {
            //     $this->results[$publicationType->getIdentifier()][][$publication->getIdentifier()]['warnings'][] = "Field [$key] is not defined in publication type";
            //     $this->countWarnings++;
            // }
        }
    }

    /*
     * Displays the given string as subtitle.
     */
    protected function subtitle(string $title): Command
    {
        $size = strlen($title);
        $spaces = str_repeat(' ', $size);

        $this->output->newLine();
        $this->output->writeln("<bg=blue;fg=white>$spaces$title$spaces</>");
        $this->output->newLine();

        return $this;
    }

    protected function getPublicationTypesToValidate(): Collection
    {
        $publicationTypes = PublicationService::getPublicationTypes();
        $name = $this->argument('publicationType');

        if (filled($name)) {
            if (! $publicationTypes->has($name)) {
                throw new InvalidArgumentException("Publication type [$name] does not exist");
            }
            $publicationTypes = collect([$name => $publicationTypes->get($name)]);
        }

        if ($publicationTypes->isEmpty()) {
            throw new InvalidArgumentException('No publication types to validate!');
        }

        return $publicationTypes;
    }

    protected function countPublicationTypes(): ?int
    {
        return count($this->results);
    }

    private function countPublications(): int
    {
        $count = 0;
        foreach ($this->results as $publicationType) {
            $count += count($publicationType ?? []);
        }

        return $count;
    }

    private function countFields(): int
    {
        $count = 0;
        foreach ($this->results as $publicationType) {
            foreach ($publicationType ?? [] as $publication) {
                $count += count($publication['$fields']);
            }
        }

        return $count;
    }

    protected function displayResults(): void
    {
        foreach ($this->results as $publicationTypeName => $publicationType) {
            $this->infoComment('Validating publication type', $publicationTypeName);
            foreach ($publicationType ?? [] as $publicationName => $publication) {
                $hasErrors = false;
                $hasWarnings = isset($publication['warnings']);
                foreach ($publication['$fields'] ?? [] as $field) {
                    if (isset($field['errors'])) {
                        $hasErrors = true;
                    }
                }
                $icon = $hasErrors ? sprintf('<fg=red>%s</>', self::CROSS_MARK) : sprintf('<info>%s</info>', self::CHECKMARK);
                if ($hasWarnings && ! $hasErrors) {
                    $icon = sprintf('<fg=yellow>%s</>', self::WARNING);
                }
                $this->line(sprintf('  <fg=cyan>%s %s.md</> %s', $this->verbose ? 'File' : "<fg=gray>\u{2010}</>", $publicationName, $icon));
                foreach ($publication['warnings'] ?? [] as $warning) {
                    $this->line("      <fg=yellow>Warning: $warning</>");
                }
                foreach ($publication['$fields'] ?? [] as $fieldName => $field) {
                    if ($this->verbose) {
                        $hasErrors = isset($field['errors']);
                        $this->line(sprintf('    <fg=bright-cyan>Field [%s]</>%s', $fieldName,
                            $hasErrors ? sprintf(' <fg=red>%s</>', self::CROSS_MARK) : sprintf(' <info>%s</info>', self::CHECKMARK)));
                    }
                    foreach ($field['errors'] ?? [] as $error) {
                        $this->line("      <fg=red>Error: $error</>");
                    }
                }
            }

            if ($publicationTypeName !== array_key_last($this->results)) {
                $this->output->newLine();
            }
        }
    }

    protected function outputSummary($timeStart): void
    {
        $warnColor = $this->countWarnings() ? 'yellow' : 'green';
        $errorColor = $this->countErrors() ? 'red' : 'green';

        $this->subtitle('Summary:');

        $this->output->writeln(sprintf('<fg=green>Validated %d publication types, %d publications, %d fields</><fg=gray> in %sms using %sMB peak memory</>',
            $this->countPublicationTypes(), $this->countPublications(), $this->countFields(),
            round((microtime(true) - $timeStart) * 1000),
            round(memory_get_peak_usage() / 1024 / 1024)
        ));

        $this->output->writeln("<fg=$warnColor>Found {$this->countWarnings()} Warnings</>");
        $this->output->writeln("<fg=$errorColor>Found {$this->countErrors()} Errors</>");
    }

    protected function outputJson(): void
    {
        $this->output->writeln(json_encode($this->results, JSON_PRETTY_PRINT));
    }

    protected function countWarnings(): int
    {
        // FIXME Implement this
        return 0;
    }

    protected function countErrors(): int
    {
        $errors = 0;

        foreach ($this->results as $publication => $results) {
            $errors += count($results, COUNT_RECURSIVE) - count($results);
        }

        return $errors;
    }
}
