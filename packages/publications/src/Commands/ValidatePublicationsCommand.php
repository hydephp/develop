<?php

declare(strict_types=1);

namespace Hyde\Publications\Commands;

use function array_map;
use function array_values;
use function basename;
use function collect;
use function count;
use function explode;
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
use function substr_count;

/**
 * Hyde Command to validate one or all publications.
 *
 * @see \Hyde\Publications\Testing\Feature\ValidatePublicationsCommandTest
 *
 * @internal This command is not part of the public API and may change without notice.
 */
class ValidatePublicationsCommand extends ValidatingCommand
{
    /** @var string */
    protected $signature = 'validate:publications
		{publicationType? : The name of the publication type to validate.}
		{--json : Display results as JSON.}';

    /** @var string */
    protected $description = 'Validate all or the specified publication type(s)';

    protected float $timeStart;

    protected array $results = [];

    protected int $countedErrors = 0;
    protected int $countedWarnings = 0;

    protected string $passedIcon = "<fg=green>\u{2713}</>";
    protected string $failedIcon = "<fg=red>\u{2A2F}</>";
    protected string $warningIcon = "<fg=yellow>\u{0021}</>";

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

        $this->countedErrors = substr_count(json_encode($this->results), '":"Error: ');
        $this->countedWarnings = substr_count(json_encode($this->results), '":"Warning: ');

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

    protected function getPublicationTypesToValidate(): Collection
    {
        $publicationTypes = PublicationService::getPublicationTypes();
        $name = $this->argument('publicationType');

        if (filled($name)) {
            if (! $publicationTypes->has($name)) {
                throw new InvalidArgumentException("Publication type [$name] does not exist");
            }

            return collect([$name => PublicationType::get($name)]);
        }

        if ($publicationTypes->isEmpty()) {
            throw new InvalidArgumentException('No publication types to validate!');
        }

        return $publicationTypes;
    }

    protected function validatePublicationType(PublicationType $publicationType): void
    {
        $this->results[$publicationType->getIdentifier()] = [];

        foreach (glob(Hyde::path("{$publicationType->getDirectory()}/*.md")) as $publicationFile) {
            $identifier = basename($publicationFile, '.md');
            $this->results[$publicationType->getIdentifier()][$identifier] = PublicationPageValidator::call($publicationType, $identifier)->getResults();
        }
    }

    protected function displayResults(): void
    {
        foreach ($this->results as $publicationTypeName => $publications) {
            $this->infoComment("Validating publication type [$publicationTypeName]");
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
        $this->line(sprintf('  %s <fg=cyan>%s.md</>', $this->getPublicationResultsIcon(
            $this->getMessageTypesInResult($results)), $publicationName
        ));

        foreach ($results as $message) {
            $this->displayPublicationFieldResults($message);
        }
    }

    protected function displayPublicationFieldResults(string $message): void
    {
        $isWarning = str_starts_with($message, 'Warning: ');
        $isError = str_starts_with($message, 'Error: ');

        $message = str_replace(['Warning: ', 'Error: '], '', $message);

        if ($isWarning || $isError) {
            if ($isWarning) {
                $this->line(sprintf('    %s <comment>%s</comment>', $this->warningIcon, $message));
            } else {
                $this->line(sprintf('    %s <fg=red>%s</>', $this->failedIcon, $message));
            }
        } elseif ($this->output->isVerbose()) {
            $this->line(sprintf('    %s <fg=green>%s</>', $this->passedIcon, $message));
        }
    }

    protected function getPublicationResultsIcon(array $types): string
    {
        if (in_array('Error', $types)) {
            return $this->failedIcon;
        }

        if (in_array('Warning', $types)) {
            return $this->warningIcon;
        }

        return $this->passedIcon;
    }

    protected function getMessageTypesInResult(array $results): array
    {
        return array_map(function (string $result): string {
            return explode(':', $result)[0];
        }, array_values($results));
    }

    protected function outputSummary(): void
    {
        $size = strlen('Summary:');
        $spaces = str_repeat(' ', $size);

        $this->output->newLine();
        $this->output->writeln("<bg=blue;fg=white>{$spaces}Summary:$spaces</>");
        $this->output->newLine();

        $countPublicationTypes = count($this->results);
        $countPublications = self::countRecursive($this->results, 1);
        $countFields = self::countRecursive($this->results, 2);

        $this->output->writeln(sprintf('<fg=green>Validated %d publication types, %d publications, %d fields</><fg=gray> in %sms using %sMB peak memory</>',
            $countPublicationTypes, $countPublications, $countFields,
            round((microtime(true) - $this->timeStart) * 1000),
            round(memory_get_peak_usage() / 1024 / 1024)
        ));

        $this->output->writeln('<fg='.($this->countedWarnings ? 'yellow' : 'green').">Found $this->countedWarnings Warnings</>");
        $this->output->writeln('<fg='.($this->countedErrors ? 'red' : 'green').">Found $this->countedErrors Errors</>");
    }

    protected function outputJson(): void
    {
        $this->output->writeln(json_encode($this->results, JSON_PRETTY_PRINT));
    }

    protected static function countRecursive(array $array, int $limit): int
    {
        $count = 0;

        foreach ($array as $child) {
            if ($limit > 0) {
                $count += self::countRecursive($child, $limit - 1);
            } else {
                $count += 1;
            }
        }

        return $count;
    }
}
