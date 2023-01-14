<?php

declare(strict_types=1);

namespace Hyde\Publications\Commands;

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

    public function safeHandle(): int
    {
        $this->title('Validating publications!');

        $pubTypesToValidate = PublicationService::getPublicationTypes();
        $verbose = $this->option('verbose');
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

        $countPubTypes = 0;
        $countPubs = 0;
        $countFields = 0;
        $countErrors = 0;
        $countWarnings = 0;

        foreach ($pubTypesToValidate as $name=>$pubType) {
            $countPubTypes++;
            $publications = PublicationService::getPublicationsForPubType($pubType);
            $this->output->write("<fg=yellow>Validating publication type [$name]</>");

            /** @var \Hyde\Publications\Models\PublicationPage $publication */
            foreach ($publications as $publication) {
                $countPubs++;
                $this->output->write("\n<fg=cyan>    Validating publication [$publication->title]</>");
                unset($publication->matter->data['__createdAt']);

                foreach ($publication->type->getFields() as $field) {
                    $countFields++;
                    $fieldName = $field->name;
                    $pubTypeField = new PublicationFieldDefinition($field->type, $fieldName);

                    try {
                        if ($verbose) {
                            $this->output->write("\n<fg=gray>        Validating field [$fieldName]</>");
                        }

                        if (! $publication->matter->has($fieldName)) {
                            throw new Exception("Field [$fieldName] is missing from publication");
                        }

                        (new ValidatesPublicationField($pubType, $pubTypeField))->validate($publication->matter->get($fieldName));
                        $this->output->writeln(" <fg=green>".(self::CHECKMARK)."</>");
                    } catch (Exception $e) {
                        $countErrors++;
                        $this->output->writeln(" <fg=red>".(self::CROSS_MARK)."\n        {$e->getMessage()}</>");
                    }
                    unset($publication->matter->data[$fieldName]);
                }

                foreach ($publication->matter->data as $k=>$v) {
                    $countWarnings++;
                    $this->output->writeln("<fg=yellow>        Field [$k] is not defined in publication type</>");
                }
            }
            $this->output->newLine();
        }

        $warnColor = $countWarnings ? 'yellow' : 'green';
        $errorColor = $countErrors ? 'red' : 'green';
        $this->subtitle('Summary:');
        $this->output->writeln("<fg=green>Validated $countPubTypes Publication Types, $countPubs Publications, $countFields Fields</>");
        $this->output->writeln("<fg=$warnColor>Found $countWarnings Warnings</>");
        $this->output->writeln("<fg=$errorColor>Found $countErrors Errors</>");
        if ($countErrors) {
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
}
