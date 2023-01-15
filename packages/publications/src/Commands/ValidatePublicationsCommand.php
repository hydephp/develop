<?php

declare(strict_types=1);

namespace Hyde\Publications\Commands;

use Hyde\Publications\Models\PublicationPage;
use Hyde\Publications\Models\PublicationType;
use Illuminate\Support\Collection;
use Exception;
use Hyde\Publications\Actions\ValidatesPublicationField;
use Hyde\Publications\Models\PublicationFieldDefinition;
use Hyde\Publications\PublicationService;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;

use function collect;
use function filled;
use function str_repeat;
use function strlen;

/**
 * Hyde Command to validate one or all publications.
 *
 * @see \Hyde\Publications\Testing\Feature\ValidatePublicationsCommandTest
 *
 * @todo Add JSON output option?
 */
class ValidatePublicationsCommand extends ValidatingCommand
{
    protected const CHECKMARK = "\u{2713}";
    protected const CROSS_MARK = "\u{2717}";

    /** @var string */
    protected $signature = 'validate:publications
		{publicationType? : The name of the publication type to validate.}';

    /** @var string */
    protected $description = 'Validate all or the specified publication type(s)';

    protected bool $verbose;
    protected int $countErrors = 0;
    protected int $countWarnings = 0;

    protected array $results = [];

    public function safeHandle(): int
    {
        $this->title('Validating publications!');

        $this->verbose = $this->option('verbose');

        $publicationTypesToValidate = $this->getPublicationTypesToValidate();

        foreach ($publicationTypesToValidate as $name => $publicationType) {
            $this->validatePublicationType($publicationType, $name);
        }

        $warnColor = $this->countWarnings ? 'yellow' : 'green';
        $errorColor = $this->countErrors ? 'red' : 'green';

        $this->subtitle('Summary:');

        $this->output->writeln("<fg=green>Validated {$this->countPublicationTypes()} Publication Types, {$this->countPublications()} Publications, {$this->countFields()} Fields</>");
        $this->output->writeln("<fg=$warnColor>Found $this->countWarnings Warnings</>");
        $this->output->writeln("<fg=$errorColor>Found $this->countErrors Errors</>");

        if ($this->countErrors) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function validatePublicationType(PublicationType $publicationType, string $name): void
    {
        $publications = PublicationService::getPublicationsForPubType($publicationType);
        $this->output->write("<fg=yellow>Validating publication type [$name]</>");

        foreach ($publications as $publication) {
            $this->validatePublication($publication, $publicationType);
        }
        $this->output->newLine();
    }

    protected function validatePublication(PublicationPage $publication, PublicationType $publicationType): void
    {
        $this->results['$publicationTypes'][$publicationType->getIdentifier()]['$publications'][$publication->getIdentifier()] = [];
        $indentation = $this->indent(1);

        if ($this->verbose) {
            $this->output->write("\n<fg=cyan>{$indentation}Validating publication [$publication->identifier]</>");
        }
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
        $publicationTypeField = new PublicationFieldDefinition($field->type, $fieldName);
        $indentation = $this->indent(2);

        try {
            if ($this->verbose) {
                $this->output->write("\n<fg=gray>{$indentation}Validating field [$fieldName]</>");
            }

            if (!$publication->matter->has($fieldName)) {
                throw new Exception("Field [$fieldName] is missing from publication");
            }

            $validator = new ValidatesPublicationField($publicationType, $publicationTypeField);
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

    protected function indent(int $levels): string
    {
        return str_repeat(' ', $levels * 2);
    }

    protected function getPublicationTypesToValidate(): Collection
    {
        $publicationTypesToValidate = PublicationService::getPublicationTypes();
        $name = $this->argument('publicationType');

        if (filled($name)) {
            if (!$publicationTypesToValidate->has($name)) {
                throw new InvalidArgumentException("Publication type [$name] does not exist");
            }
            $publicationTypesToValidate = collect([$name => $publicationTypesToValidate->get($name)]);
        }

        if ($publicationTypesToValidate->isEmpty()) {
            throw new InvalidArgumentException('No publication types to validate!');
        }
        return $publicationTypesToValidate;
    }

    protected function countPublicationTypes(): ?int
    {
        return count($this->results);
    }

    private function countPublications(): int
    {
        $count = 0;
        foreach ($this->results['$publicationTypes'] as $publicationType) {
            $count += count($publicationType['$publications']);
        }
        return $count;
    }

    private function countFields(): int
    {
        $count = 0;
        foreach ($this->results['$publicationTypes'] as $publicationType) {
            foreach ($publicationType['$publications'] as $publication) {
                $count += count($publication['$fields']);
            }
        }
        return $count;
    }
}
