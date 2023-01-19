<?php

declare(strict_types=1);

namespace Hyde\Publications\Commands;

use function basename;
use function collect;
use function filled;
use function glob;
use Hyde\Hyde;
use Hyde\Publications\Actions\PublicationPageValidator;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\PublicationService;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use function json_encode;
use LaravelZero\Framework\Commands\Command;
use function memory_get_peak_usage;
use function microtime;
use function round;
use function sprintf;
use function str_repeat;
use function str_starts_with;
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
    protected const CROSS_MARK = '<fg=red>x</>';
    protected const WARNING = "<fg=yellow>\u{26A0}</>";

    /** @var string */
    protected $signature = 'validate:publications
		{publicationType? : The name of the publication type to validate.}
		{--json : Display results as JSON.}';

    /** @var string */
    protected $description = 'Validate all or the specified publication type(s)';

    protected float $timeStart;

    protected array $results = [];

    protected int $countedPublicationTypes = 0;
    protected int $countedPublications = 0;
    protected int $countedFields = 0;
    protected int $countedErrors = 0;
    protected int $countedWarnings = 0;

    public function safeHandle(): int
    {
        $this->timeStart = microtime(true);

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

            $this->outputSummary();
        }

        if ($this->countedErrors > 0) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function validatePublicationType(PublicationType $publicationType): void
    {
        $typeResults = [];

        foreach (glob(Hyde::path("{$publicationType->getDirectory()}/*.md")) as $publicationFile) {
            $identifier = basename($publicationFile, '.md');
            $typeResults[$identifier] = $this->validatePublicationPage($publicationType, $identifier);
        }

        $this->results[$publicationType->getIdentifier()] = $typeResults;

        $this->countedPublicationTypes++;
    }

    protected function validatePublicationPage(PublicationType $publicationType, string $identifier): array
    {
        /** @var PublicationPageValidator $validator */
        $validator = PublicationPageValidator::call($publicationType, $identifier);
        $this->incrementCountersForPublicationPage($validator);
        return $validator->getResults();
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

    protected function displayResults(): void
    {
        foreach ($this->results as $publicationTypeName => $publications) {
            $this->infoComment('Validating publication type', $publicationTypeName);
            foreach ($publications ?? [] as $publicationName => $errors) {
                $this->displayPublicationResults($publicationName, $errors);
            }

            if ($publicationTypeName !== array_key_last($this->results)) {
                $this->output->newLine();
            }
        }
    }

    protected function displayPublicationResults(string $publicationName, array $results): void
    {
        $errors = [];
        $warnings = [];

        foreach ($results ?? [] as $result) {
            if (str_starts_with($result, 'Warning: ')) {
                $warnings[] = $result;
            } else {
                $errors[] = $result;
            }
        }

        $hasErrors = count($errors);
        $hasWarnings = count($warnings);
        $icon = $hasErrors ? sprintf('<fg=red>%s</>', self::CROSS_MARK) : sprintf('<info>%s</info>', self::CHECKMARK);
        if ($hasWarnings && !$hasErrors) {
            $icon = self::WARNING;
        }
        $this->line(sprintf('  <fg=cyan>File %s.md</> %s', $publicationName, $icon));

        foreach ($results as $fieldName => $message) {
            $this->displayPublicationFieldResults($fieldName, $message);
        }
    }

    protected function displayPublicationFieldResults(string $fieldName, string $message): void
    {
        $isWarning = str_starts_with($message, 'Warning: ');
        $icon = $isWarning
            ? sprintf(' %s', self::WARNING)
            : sprintf(' %s', self::CROSS_MARK);
        $this->line(sprintf('    <fg=bright-cyan>Field [%s]</>%s', $fieldName, $icon));

        $this->line($isWarning
            ? "      <fg=yellow>$message</>"
            : "      <fg=red>Error: $message</>"
        );
    }

    protected function outputSummary(): void
    {
        $warnColor = $this->countedWarnings ? 'yellow' : 'green';
        $errorColor = $this->countedErrors ? 'red' : 'green';

        $this->subtitle();

        $this->output->writeln(sprintf('<fg=green>Validated %d publication types, %d publications, %d fields</><fg=gray> in %sms using %sMB peak memory</>',
            $this->countedPublicationTypes, $this->countedPublications, $this->countedFields,
            round((microtime(true) - $this->timeStart) * 1000),
            round(memory_get_peak_usage() / 1024 / 1024)
        ));

        $this->output->writeln("<fg=$warnColor>Found $this->countedWarnings Warnings</>");
        $this->output->writeln("<fg=$errorColor>Found $this->countedErrors Errors</>");
    }

    protected function outputJson(): void
    {
        $this->output->writeln(json_encode($this->results, JSON_PRETTY_PRINT));
    }

    protected function subtitle(): void
    {
        $size = strlen('Summary:');
        $spaces = str_repeat(' ', $size);

        $this->output->newLine();
        $this->output->writeln("<bg=blue;fg=white>{$spaces}Summary:$spaces</>");
        $this->output->newLine();
    }

    protected function incrementCountersForPublicationPage(PublicationPageValidator $validator): void
    {
        $this->countedPublications++;
        $this->countedFields += count($validator->fields());
        $this->countedErrors += count($validator->errors());
        $this->countedWarnings += count($validator->warnings());
    }
}
