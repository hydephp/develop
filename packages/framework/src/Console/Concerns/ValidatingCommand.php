<?php

declare(strict_types=1);

namespace Hyde\Console\Concerns;

use function array_keys;
use function array_values;
use function debug_backtrace;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use RuntimeException;
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
     * @param  int  $retryCount  How many times has the question been asked?
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

    /**
     * Handle an exception that occurred during command execution.
     *
     * @return int The exit code
     */
    public function handleException(Exception $exception): int
    {
        if ($exception->getFile() === debug_backtrace()[0]['file']) {
            // If the exception was thrown from the same file as the command, then we don't need to show which file it was thrown from.
            $this->error("Error: {$exception->getMessage()}");
        } else {
            $this->error("Error: {$exception->getMessage()} at {$exception->getFile()}:{$exception->getLine()}");
        }

        return Command::FAILURE;
    }

    protected function translate($name, string $error): string
    {
        return $this->makeReplacements($name, Str::after($error, 'validation.'), $this->getTranslationLines());
    }

    protected function makeReplacements(string $name, string $line, array $replace): string
    {
        return str_replace(':attribute', $name, str_replace(array_keys($replace), array_values($replace), $line));
    }

    protected function getTranslationLines(): array
    {
        return [
            'accepted' => 'The :attribute must be accepted.',
            'accepted_if' => 'The :attribute must be accepted when :other is :value.',
            'active_url' => 'The :attribute is not a valid URL.',
            'after' => 'The :attribute must be a date after :date.',
            'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
            'alpha' => 'The :attribute must only contain letters.',
            'alpha_dash' => 'The :attribute must only contain letters, numbers, dashes and underscores.',
            'alpha_num' => 'The :attribute must only contain letters and numbers.',
            'array' => 'The :attribute must be an array.',
            'before' => 'The :attribute must be a date before :date.',
            'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
            'boolean' => 'The :attribute field must be true or false.',
            'confirmed' => 'The :attribute confirmation does not match.',
            'current_password' => 'The password is incorrect.',
            'date' => 'The :attribute is not a valid date.',
            'date_equals' => 'The :attribute must be a date equal to :date.',
            'date_format' => 'The :attribute does not match the format :format.',
            'declined' => 'The :attribute must be declined.',
            'declined_if' => 'The :attribute must be declined when :other is :value.',
            'different' => 'The :attribute and :other must be different.',
            'digits' => 'The :attribute must be :digits digits.',
            'digits_between' => 'The :attribute must be between :min and :max digits.',
            'dimensions' => 'The :attribute has invalid image dimensions.',
            'distinct' => 'The :attribute field has a duplicate value.',
            'doesnt_end_with' => 'The :attribute may not end with one of the following: :values.',
            'doesnt_start_with' => 'The :attribute may not start with one of the following: :values.',
            'email' => 'The :attribute must be a valid email address.',
            'ends_with' => 'The :attribute must end with one of the following: :values.',
            'enum' => 'The selected :attribute is invalid.',
            'exists' => 'The selected :attribute is invalid.',
            'file' => 'The :attribute must be a file.',
            'filled' => 'The :attribute field must have a value.',
            'image' => 'The :attribute must be an image.',
            'in' => 'The selected :attribute is invalid.',
            'in_array' => 'The :attribute field does not exist in :other.',
            'integer' => 'The :attribute must be an integer.',
            'ip' => 'The :attribute must be a valid IP address.',
            'ipv4' => 'The :attribute must be a valid IPv4 address.',
            'ipv6' => 'The :attribute must be a valid IPv6 address.',
            'json' => 'The :attribute must be a valid JSON string.',
            'lowercase' => 'The :attribute must be lowercase.',
            'mac_address' => 'The :attribute must be a valid MAC address.',
            'max_digits' => 'The :attribute must not have more than :max digits.',
            'mimes' => 'The :attribute must be a file of type: :values.',
            'mimetypes' => 'The :attribute must be a file of type: :values.',
            'min_digits' => 'The :attribute must have at least :min digits.',
            'multiple_of' => 'The :attribute must be a multiple of :value.',
            'not_in' => 'The selected :attribute is invalid.',
            'not_regex' => 'The :attribute format is invalid.',
            'numeric' => 'The :attribute must be a number.',
            'present' => 'The :attribute field must be present.',
            'prohibited' => 'The :attribute field is prohibited.',
            'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
            'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
            'prohibits' => 'The :attribute field prohibits :other from being present.',
            'regex' => 'The :attribute format is invalid.',
            'required' => 'The :attribute field is required.',
            'required_array_keys' => 'The :attribute field must contain entries for: :values.',
            'required_if' => 'The :attribute field is required when :other is :value.',
            'required_if_accepted' => 'The :attribute field is required when :other is accepted.',
            'required_unless' => 'The :attribute field is required unless :other is in :values.',
            'required_with' => 'The :attribute field is required when :values is present.',
            'required_with_all' => 'The :attribute field is required when :values are present.',
            'required_without' => 'The :attribute field is required when :values is not present.',
            'required_without_all' => 'The :attribute field is required when none of :values are present.',
            'same' => 'The :attribute and :other must match.',
            'starts_with' => 'The :attribute must start with one of the following: :values.',
            'string' => 'The :attribute must be a string.',
            'timezone' => 'The :attribute must be a valid timezone.',
            'unique' => 'The :attribute has already been taken.',
            'uploaded' => 'The :attribute failed to upload.',
            'uppercase' => 'The :attribute must be uppercase.',
            'url' => 'The :attribute must be a valid URL.',
            'uuid' => 'The :attribute must be a valid UUID.',
        ];
    }
}
