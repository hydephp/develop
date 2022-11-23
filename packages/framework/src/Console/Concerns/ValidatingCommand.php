<?php

declare(strict_types=1);

namespace Hyde\Console\Concerns;

use Hyde\Framework\Features\Publications\PublicationService;
use LaravelZero\Framework\Commands\Command;
use Rgasch\Collection\Collection;
use RuntimeException;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use function ucfirst;

/**
 * An extended Command class that provides validation methods.
 */
class ValidatingCommand extends Command
{
    /** @var int How many times can the validation loop run? Guards against infinite loops. */
    protected final const RETRY_COUNT = 10;

    /**
     * Ask for a CLI input value until we pass validation rules.
     *
     * @param  \LaravelZero\Framework\Commands\Command  $command
     * @param  string  $name
     * @param  string  $message
     * @param  \Rgasch\Collection\Collection|array  $rules
     * @param  mixed|null  $default
     * @param  bool  $isBeingRetried
     * @return mixed
     *
     * @throws RuntimeException
     */
    public static function askWithValidation(
        Command $command,
        string $name,
        string $message,
        Collection|array $rules = [],
        mixed $default = null,
        bool $isBeingRetried = false
    ): mixed {
        static $tries = 0;
        if (!$isBeingRetried) {
            $tries = 0;
        }

        if ($rules instanceof Collection) {
            $rules = $rules->toArray();
        }

        $answer    = $command->ask(ucfirst($message), $default);
        $factory   = app(ValidationFactory::class);
        $validator = $factory->make([$name => $answer], [$name => $rules]);

        if ($validator->passes()) {
            return $answer;
        }

        foreach ($validator->errors()->all() as $error) {
            $command->error($error);
        }

        $tries++;

        if ($tries >= PublicationService::RETRY_COUNT) {
            throw new RuntimeException(sprintf("Too many validation errors trying to validate '$name' with rules: [%s]", implode(', ', $rules)));
        }

        return self::askWithValidation($command, $name, $message, $rules, isBeingRetried: true);
    }
}
