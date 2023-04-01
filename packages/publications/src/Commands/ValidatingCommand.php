<?php

declare(strict_types=1);

namespace Hyde\Publications\Commands;

use Hyde\Console\Concerns\Command;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

use function __;
use function array_merge;
use function implode;
use function in_array;
use function sprintf;
use function trim;
use function ucfirst;

/**
 * @internal An extended Command class that provides validation methods.
 *
 * @see \Hyde\Framework\Testing\Feature\ValidatingCommandTest
 */
class ValidatingCommand extends Command
{
    /** @var int How many times can the validation loop run? It is high enough to not affect normal usage. */
    protected final const MAX_RETRIES = 30;

    /**
     * Ask for a CLI input value until we pass validation rules.
     *
     * @param  int  $retryCount  How many times has the question been asked?
     *
     * @throws RuntimeException If the validation fails after MAX_RETRIES attempts.
     */
    public function askWithValidation(
        string $name,
        string $question,
        array $rules = [],
        mixed $default = null,
        int $retryCount = 0
    ): string {
        if ($retryCount >= self::MAX_RETRIES) {
            // Prevent infinite loops that may happen due to the method's recursion.
            // For example when running a command in tests or without interaction.
            throw new RuntimeException(sprintf("Too many validation errors trying to validate '$name' with rules: [%s]", implode(', ', $rules)));
        }

        $answer = trim((string) $this->ask(ucfirst($question), $default));
        $validator = Validator::make([$name => self::normalizeInput($answer, $rules)], [$name => $rules]);

        if ($validator->passes()) {
            return $answer;
        }

        foreach ($validator->errors()->all() as $error) {
            $this->error($this->translate($name, $error));
        }

        return $this->askWithValidation($name, $question, $rules, $default, $retryCount + 1);
    }

    /** @param  callable<array<string>>  $options A function that returns an array of options. It will be re-run if the user hits selects the added 'reload' option. */
    public function reloadableChoice(callable $options, string $question, string $reloadMessage = 'Reload options', bool $multiple = false): string|array
    {
        $reloadMessage = "<fg=bright-blue>[$reloadMessage]</>";
        do {
            $selection = $this->choice($question, array_merge([$reloadMessage], $options()), multiple: $multiple);
        } while (in_array($reloadMessage, (array) $selection));

        return $selection;
    }

    protected function translate(string $name, string $error): string
    {
        return __($error, [
            'attribute' => $name,
        ]);
    }

    protected static function normalizeInput(string $value, array $rules): bool|string
    {
        if (in_array('boolean', $rules)) {
            // Since the Laravel validation rule requires booleans to be boolean, but the Symfony
            // console input is a string, so we need to convert it so that it can be validated.
            if ($value === 'true') {
                return true;
            }
            if ($value === 'false') {
                return false;
            }
        }

        return $value;
    }
}
