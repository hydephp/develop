<?php

declare(strict_types=1);

namespace Hyde\Publications\Commands;

use Hyde\Publications\Models\PublicationPage;
use Hyde\Publications\Models\PublicationType;
use function count;
use Exception;
use Hyde\Publications\Actions\ValidatesPublicationField;
use Hyde\Publications\Models\PublicationFieldDefinition;
use Hyde\Publications\PublicationService;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
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
    protected int $countPubTypes = 0;
    protected int $countPubs = 0;
    protected int $countFields = 0;
    protected int $countErrors = 0;
    protected int $countWarnings = 0;

    public function safeHandle(): int
    {
        $this->title('Validating publications!');

        $pubTypesToValidate = PublicationService::getPublicationTypes();
        $this->verbose = $this->option('verbose');
        $name = $this->argument('publicationType');
        if ($name) {
            if (! $pubTypesToValidate->has($name)) {
                throw new InvalidArgumentException("Publication type [$name] does not exist");
            }
            $pubTypesToValidate = [$name => $pubTypesToValidate->get($name)];
        }

        if (count($pubTypesToValidate) === 0) {
            throw new InvalidArgumentException('No publication types to validate!');
        }

        foreach ($pubTypesToValidate as $name=>$pubType) {
            $this->validatePublicationType($pubType, $name);
        }

        $warnColor = $this->countWarnings ? 'yellow' : 'green';
        $errorColor = $this->countErrors ? 'red' : 'green';
        $this->subtitle('Summary:');
        $this->output->writeln("<fg=green>Validated $this->countPubTypes Publication Types, $this->countPubs Publications, $this->countFields Fields</>");
        $this->output->writeln("<fg=$warnColor>Found $this->countWarnings Warnings</>");
        $this->output->writeln("<fg=$errorColor>Found $this->countErrors Errors</>");
        if ($this->countErrors) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /*
     * Displays the given string as subtitle.
     */
    public function subtitle(string $title): Command
    {
        $size = strlen($title);
        $spaces = str_repeat(' ', $size);

        $this->output->newLine();
        $this->output->writeln("<bg=blue;fg=white>$spaces$title$spaces</>");
        $this->output->newLine();

        return $this;
    }

    protected function validatePublicationType(PublicationType $pubType, string $name): void
    {
        $this->countPubTypes++;
        $publications = PublicationService::getPublicationsForPubType($pubType);
        $this->output->write("<fg=yellow>Validating publication type [$name]</>");

        /** @var \Hyde\Publications\Models\PublicationPage $publication */
        foreach ($publications as $publication) {
            $this->validatePublication($publication, $pubType);
        }
        $this->output->newLine();
    }

    protected function validatePublication(PublicationPage $publication, PublicationType $pubType): void
    {
        $this->countPubs++;
        $this->output->write("\n<fg=cyan>    Validating publication [$publication->title]</>");
        unset($publication->matter->data['__createdAt']);

        foreach ($publication->type->getFields() as $field) {
            $this->validatePublicationField($field, $publication, $pubType);
        }

        foreach ($publication->matter->data as $k => $v) {
            $this->countWarnings++;
            $this->output->writeln("<fg=yellow>        Field [$k] is not defined in publication type</>");
        }
    }

    protected function validatePublicationField(mixed $field, PublicationPage $publication, PublicationType $pubType): void
    {
        $this->countFields++;
        $fieldName = $field->name;
        $pubTypeField = new PublicationFieldDefinition($field->type, $fieldName);

        try {
            if ($this->verbose) {
                $this->output->write("\n<fg=gray>        Validating field [$fieldName]</>");
            }

            if (!$publication->matter->has($fieldName)) {
                throw new Exception("Field [$fieldName] is missing from publication");
            }

            (new ValidatesPublicationField($pubType,
                $pubTypeField))->validate($publication->matter->get($fieldName));
            $this->output->writeln(" <fg=green>".(self::CHECKMARK)."</>");
        } catch (Exception $e) {
            $this->countErrors++;
            $this->output->writeln(" <fg=red>".(self::CROSS_MARK)."\n        {$e->getMessage()}</>");
        }
        unset($publication->matter->data[$fieldName]);
    }
}
