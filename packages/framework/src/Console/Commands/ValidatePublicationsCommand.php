<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Exception;
use Hyde\Console\Commands\Interfaces\CommandHandleInterface;
use Hyde\Console\Concerns\ValidatingCommand;
use Hyde\Framework\Features\Publications\Models\PublicationFieldType;
use Hyde\Framework\Features\Publications\PublicationService;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;

/**
 * Hyde Command to validate one or all publications.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\ValidatePublicationTypeCommandTest
 *
 * @todo Add JSON output option?
 */
class ValidatePublicationsCommand extends ValidatingCommand implements CommandHandleInterface
{
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
            $pubTypesToValidate = [$name => $pubTypesToValidate->{$name}];
        }

        if (count($pubTypesToValidate) === 0) {
            throw new InvalidArgumentException('No publication types to validate!');
        }

        $checkmark = "\u{2713}";
        $xmark = "\u{2717}";
        $countPubTypes = 0;
        $countPubs = 0;
        $countFields = 0;
        $countErrors = 0;
        $countWarnings = 0;

        foreach ($pubTypesToValidate as $name=>$pubType) {
            $countPubTypes++;
            $publications = PublicationService::getPublicationsForPubType($pubType);
            $this->output->write("<fg=yellow>Validating publication type [$name]</>");
            $publicationFieldRules = $pubType->getFieldRules(false);

            /** @var \Hyde\Pages\PublicationPage $publication */
            foreach ($publications as $publication) {
                $countPubs++;
                $this->output->write("\n<fg=cyan>    Validating publication [$publication->title]</>");
                $publication->matter->forget('__createdAt');

                foreach ($publication->type->fields as $field) {
                    $countFields++;
                    $fieldName = $field['name'];
                    $pubTypeField = new PublicationFieldType($field['type'], $fieldName, $field['min'], $field['max'], $field['tagGroup'] ?? null, $pubType);

                    try {
                        if ($verbose) {
                            $this->output->write("\n<fg=gray>        Validating field [$fieldName]</>");
                        }

                        if (! $publication->matter->has($fieldName)) {
                            throw new Exception("Field [$fieldName] is missing from publication");
                        }

                        $pubTypeField->validate($publication->matter->{$fieldName} ?? null,
                                                $publicationFieldRules->{$fieldName} ?? null);
                        $this->output->writeln(" <fg=green>$checkmark</>");
                    } catch (Exception $e) {
                        $countErrors++;
                        $this->output->writeln(" <fg=red>$xmark\n        {$e->getMessage()}</>");
                    }
                    $publication->matter->forget($fieldName);
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
        $this->title('Summary:');
        $this->output->writeln("<fg=green>Validated $countPubTypes Publication Types, $countPubs Publications, $countFields Fields</>");
        $this->output->writeln("<fg=$warnColor>Found $countWarnings Warnings</>");
        $this->output->writeln("<fg=$errorColor>Found $countErrors Errors</>");
        if ($countErrors) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
