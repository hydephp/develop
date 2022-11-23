<?php

declare(strict_types=1);

namespace Hyde\Console\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use LaravelZero\Framework\Commands\Command;
use RuntimeException;
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
     * @param  string  $message
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $rules
     * @param  mixed|null  $default
     * @param int $retryCount How many times has the question been asked?
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function askWithValidation(
        string $name,
        string $message,
        Arrayable|array $rules = [],
        mixed $default = null,
        int $retryCount = 0
    ): mixed {
        if ($rules instanceof Arrayable) {
            $rules = $rules->toArray();
        }

        $answer = $this->ask(ucfirst($message), $default);
        $factory = app(ValidationFactory::class);
        $validator = $factory->make([$name => $answer], [$name => $rules]);

        if ($validator->passes()) {
            return $answer;
        }

        foreach ($validator->errors()->all() as $error) {
            $this->error($error);
        }

        $retryCount++;

        if ($retryCount >= self::MAX_RETRIES) {
            // Prevent infinite loops that may happen, for example when testing. The retry count is high enough to not affect normal usage.
            throw new RuntimeException(sprintf("Too many validation errors trying to validate '$name' with rules: [%s]", implode(', ', $rules)));
        }

        return $this->askWithValidation($name, $message, $rules, $retryCount);
    }
}
