<?php

declare(strict_types=1);

namespace Hyde\Publications\Commands;

use function basename;
use function collect;
use function filled;
use Hyde\Hyde;
use Hyde\Publications\Actions\PublicationPageValidator;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\PublicationService;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use function glob;
use function json_encode;
use function memory_get_peak_usage;
use function microtime;
use function round;
use function sprintf;
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
    private const CHECKMARK = "\u{2713}";
    private const CROSS_MARK = 'x';
    private const WARNING = "\u{26A0}";

    /** @var string */
    protected $signature = 'validate:publications
		{publicationType? : The name of the publication type to validate.}
		{--json : Display results as JSON.}';

    /** @var string */
    protected $description = 'Validate all or the specified publication type(s)';

    private bool $verbose;

    private array $results = [];

    private int $countedPublicationTypes = 0;
    private int $countedPublications = 0;
    private int $countedFields = 0;
    private int $countedErrors = 0;
    private int $countedWarnings = 0;

    public function safeHandle(): int
    {
        $timeStart = microtime(true);

        $this->verbose = $this->option('verbose');

        if (! $this->option('json')) {
            $this->title('Validating publications!');
        }

        $publicationTypesToValidate = $this->getPublicationTypesToValidate();

        foreach ($publicationTypesToValidate as $publicationType) {
            $this->validatePublicationType($publicationType);
        }

        if ($this->option('json')) {
            $this->outputJson();
        } else {
            $this->displayResults();

            $this->outputSummary($timeStart);
        }

        if ($this->countedErrors > 0) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function validatePublicationType(PublicationType $publicationType): void
    {
        foreach (glob(Hyde::path("{$publicationType->getDirectory()}/*.md")) as $publicationFile) {
            $identifier = basename($publicationFile, '.md');
            $validator = PublicationPageValidator::call($publicationType, $identifier);
            $this->results[$publicationType->getIdentifier()][$identifier] = [
                'errors' => $validator->errors(),
                'warnings' => $validator->warnings(),
            ];
            $this->countedPublications++;
            $this->countedFields += count($validator->fields());
            $this->countedErrors += count($validator->errors());
            $this->countedWarnings += count($validator->warnings());
        }

        $this->countedPublicationTypes++;
    }

    private function getPublicationTypesToValidate(): Collection
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

    private function displayResults(): void
    {
        foreach ($this->results as $publicationTypeName => $publicationType) {
            $this->infoComment('Validating publication type', $publicationTypeName);
            foreach ($publicationType ?? [] as $publicationName => $publication) {
                $hasErrors = false;
                $hasWarnings = isset($publication['warnings']);
                foreach ($publication ?? [] as $field) {
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
                foreach ($publication ?? [] as $fieldName => $field) {
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

    private function outputSummary($timeStart): void
    {
        $warnColor = $this->countedWarnings ? 'yellow' : 'green';
        $errorColor = $this->countedErrors ? 'red' : 'green';

        $this->subtitle();

        $this->output->writeln(sprintf('<fg=green>Validated %d publication types, %d publications, %d fields</><fg=gray> in %sms using %sMB peak memory</>',
            $this->countedPublicationTypes, $this->countedPublications, $this->countedFields,
            round((microtime(true) - $timeStart) * 1000),
            round(memory_get_peak_usage() / 1024 / 1024)
        ));

        $this->output->writeln("<fg=$warnColor>Found $this->countedWarnings Warnings</>");
        $this->output->writeln("<fg=$errorColor>Found $this->countedErrors Errors</>");
    }

    private function outputJson(): void
    {
        $this->output->writeln(json_encode($this->results, JSON_PRETTY_PRINT));
    }

    private function subtitle(): void
    {
        $size = strlen('Summary:');
        $spaces = str_repeat(' ', $size);

        $this->output->newLine();
        $this->output->writeln("<bg=blue;fg=white>{$spaces}Summary:$spaces</>");
        $this->output->newLine();
    }
}
