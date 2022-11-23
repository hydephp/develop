<?php

declare(strict_types=1);

namespace Hyde\Console\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use RuntimeException;

use function array_keys;
use function array_values;
use function str_replace;
use function ucfirst;

/**
 * An extended Command class that provides validation methods.
 *
 * @see \Hyde\Framework\Testing\Feature\ValidatingCommandTest
 */
class ValidatingCommand extends Command
{
    /** @var int How many times can the validation loop run? Guards against infinite loops. */
    protected final const MAX_RETRIES = 30;

    /**
     * Ask for a CLI input value until we pass validation rules.
     *
     * @param  string  $name
     * @param  string  $question
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $rules
     * @param  mixed|null  $default
     * @param  int  $retryCount  How many times has the question been asked?
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function askWithValidation(
        string $name,
        string $question,
        Arrayable|array $rules = [],
        mixed $default = null,
        int $retryCount = 0
    ): mixed {
        if ($rules instanceof Arrayable) {
            $rules = $rules->toArray();
        }

        $answer = $this->ask(ucfirst($question), $default);
        $validator = Validator::make([$name => $answer], [$name => $rules]);

        if ($validator->passes()) {
            return $answer;
        }

        foreach ($validator->errors()->all() as $error) {
            $this->error($this->translate($name, $error));
        }

        $retryCount++;

        if ($retryCount >= self::MAX_RETRIES) {
            // Prevent infinite loops that may happen, for example when testing. The retry count is high enough to not affect normal usage.
            throw new RuntimeException(sprintf("Too many validation errors trying to validate '$name' with rules: [%s]", implode(', ', $rules)));
        }

        return $this->askWithValidation($name, $question, $rules, null, $retryCount);
    }

    protected function translate($name, string $error): string
    {
        $lines = require __DIR__ . '/../../../resources/lang/en/validation.php';
        return ($this->makeReplacements($name, Str::after($error, 'validation.'), $lines));
    }

    protected function makeReplacements(string $name, string $line, array $replace): string
    {
       return str_replace(':attribute', $name, str_replace(array_keys($replace), array_values($replace), $line));
    }
}
