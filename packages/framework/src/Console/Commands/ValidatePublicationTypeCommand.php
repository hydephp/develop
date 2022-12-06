<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Exception;
use Hyde\Console\Commands\Interfaces\CommandHandleInterface;
use Hyde\Console\Concerns\ValidatingCommand;
use Hyde\Framework\Actions\CreatesNewPublicationType;
use Hyde\Framework\Features\Publications\Models\PublicationFieldType;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use Rgasch\Collection\Collection;

/**
 * Hyde Command to validate one or all publications
 *
 * @see \Hyde\Framework\Actions\CreatesNewPublicationType
 * @see \Hyde\Framework\Testing\Feature\Commands\MakePublicationTypeCommandTest
 */
class ValidatePublicationTypeCommand extends ValidatingCommand implements CommandHandleInterface
{
    /** @var string */
    protected $signature = 'validate:publicationType
		{publicationType? : The name of the Publication Type to validate.}';

    /** @var string */
    protected $description = 'Validate all or the specified publication type(s)';

    public function handle(): int
    {
        $this->title('Validating PublicationType(s)!');

        $pubTypesToValidate = PublicationService::getPublicationTypes();
        $verbose = $this->option('verbose');
        $name = $this->argument('publicationType');
        if ($name) {
            if (!$pubTypesToValidate->has($name)) {
                throw new InvalidArgumentException("Publication type [$name] does not exist");
            }
            $pubTypesToValidate = [ $name => $pubTypesToValidate->{$name} ];
        }

        $checkmark     = "\u{2713}";
        $xmark         = "\u{2717}";
        $countPubTypes = 0;
        $countPubs     = 0;
        $countFields   = 0;
        $countErrors   = 0;
        $countWarnings = 0;

        foreach ($pubTypesToValidate as $name=>$pubType) {
            $countPubTypes++;
            $publications = PublicationService::getPublicationsForPubType($pubType);
            $this->output->writeln("<fg=magenta>Validating publicationType [{$name}] ...</>");
            $publicationFieldRules = $pubType->getFieldRules(false);

            foreach ($publications as $publication) {
                $countPubs++;
                $this->output->writeln("<fg=cyan>    Validating publication [{$publication->title}] ...</>");
                $publication->matter->forget("__createdAt");

                foreach ($publication->type->fields as $field) {
                    $countFields++;
                    $fieldName    = $field['name'];
                    $pubTypeField = new PublicationFieldType($field['type'], $fieldName, $field['min'], $field['max'], $field['tagGroup'] ?? null, $pubType);

                    try {
                        if ($verbose) {
                            $this->output->write("<fg=green>        Validating field [$fieldName] ... </>");
                        }

                        if (!$publication->matter->has($fieldName)) {
                            throw new Exception("Field [$fieldName] is missing from publication");
                        }

                        $pubTypeField->validate($publication->matter->{$fieldName} ?? null,
                                                $publicationFieldRules->{$fieldName} ?? null);
                        if ($verbose) {
                            $this->output->writeln("<fg=green>$checkmark</>");
                        }
                    } catch (Exception $e) {
                        $countErrors++;
                        if ($verbose) {
                            $this->output->writeln("<fg=red>$xmark: {$e->getMessage()}</>");
                        } else {
                            $this->output->writeln("<fg=green>$fieldName ... </><fg=red>$xmark: {$e->getMessage()}</>");
                            //dump($e->getTrace());
                        }
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

        $warnColor  = $countWarnings ? 'yellow' : 'green';
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
