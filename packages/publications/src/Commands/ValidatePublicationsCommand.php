<?php

declare(strict_types=1);

namespace Hyde\Publications\Commands;

use Hyde\Hyde;
use function collect;
use Exception;
use function filled;
use Hyde\Publications\Actions\PublicationFieldValidator;
use Hyde\Publications\Models\PublicationFieldDefinition;
use Hyde\Publications\Models\PublicationPage;
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
        $this->results[$publicationType->getIdentifier()] = [];
        $publications = glob(Hyde::path("{$publicationType->getDirectory()}/*.md"));

        foreach ($publications as $publication) {
            $this->validatePublication($publication, $publicationType);
        }
    }

    protected function validatePublication(PublicationPage $publication, PublicationType $publicationType): void
    {
        $this->results['$publicationTypes'][$publicationType->getIdentifier()]['$publications'][$publication->getIdentifier()]['$fields'] = [];

        unset($publication->matter->data['__createdAt']);

        foreach ($publication->type->getFields() as $field) {
            $this->validatePublicationField($field, $publication, $publicationType);
        }

        // Check for extra fields that are not defined in the publication type (we'll add a warning for each one)
        foreach ($publication->matter->data as $key => $value) {
            $this->results['$publicationTypes'][$publicationType->getIdentifier()]['$publications'][$publication->getIdentifier()]['warnings'][] = "Field [$key] is not defined in publication type";
            $this->countWarnings++;
        }
    }

    protected function validatePublicationField(PublicationFieldDefinition $field, PublicationPage $publication, PublicationType $publicationType): void
    {
        $fieldName = $field->name;

        $this->results['$publicationTypes'][$publicationType->getIdentifier()]['$publications'][$publication->getIdentifier()]['$fields'][$fieldName] = [];

        try {
            if (! $publication->matter->has($fieldName)) {
                throw new Exception("Field [$fieldName] is missing from publication");
            }

            $validator = new PublicationFieldValidator($publicationType, $field);
            $validator->validate($publication->matter->get($fieldName));
        } catch (Exception $exception) {
            $this->results['$publicationTypes'][$publicationType->getIdentifier()]['$publications'][$publication->getIdentifier()]['$fields'][$fieldName]['errors'][] = $exception->getMessage();
            $this->countErrors++;
        }
        unset($publication->matter->data[$fieldName]);
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
        return count($this->results['$publicationTypes']);
    }

    private function countPublications(): int
    {
        $count = 0;
        foreach ($this->results['$publicationTypes'] as $publicationType) {
            $count += count($publicationType['$publications'] ?? []);
        }

        return $count;
    }

    private function countFields(): int
    {
        $count = 0;
        foreach ($this->results['$publicationTypes'] as $publicationType) {
            foreach ($publicationType['$publications'] ?? [] as $publication) {
                $count += count($publication['$fields']);
            }
        }

        return $count;
    }

    protected function displayResults(): void
    {
        foreach ($this->results['$publicationTypes'] as $publicationTypeName => $publicationType) {
            $this->infoComment('Validating publication type', $publicationTypeName);
            foreach ($publicationType['$publications'] ?? [] as $publicationName => $publication) {
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

            if ($publicationTypeName !== array_key_last($this->results['$publicationTypes'])) {
                $this->output->newLine();
            }
        }
    }

    protected function outputSummary($timeStart): void
    {
        $warnColor = $this->countWarnings ? 'yellow' : 'green';
        $errorColor = $this->countErrors ? 'red' : 'green';

        $this->subtitle('Summary:');

        $this->output->writeln(sprintf('<fg=green>Validated %d publication types, %d publications, %d fields</><fg=gray> in %sms using %sMB peak memory</>',
            $this->countPublicationTypes(), $this->countPublications(), $this->countFields(),
            round((microtime(true) - $timeStart) * 1000),
            round(memory_get_peak_usage() / 1024 / 1024)
        ));

        $this->output->writeln("<fg=$warnColor>Found $this->countWarnings Warnings</>");
        $this->output->writeln("<fg=$errorColor>Found $this->countErrors Errors</>");
    }

    protected function outputJson(): void
    {
        $this->output->writeln(json_encode($this->results, JSON_PRETTY_PRINT));
    }
}
