<?php

declare(strict_types=1);

namespace Hyde\Publications\Commands;

use function __;
use function array_merge;
use Exception;
use Hyde\Publications\Validation\BooleanRule;
use Illuminate\Support\Facades\Validator;
use function in_array;
use LaravelZero\Framework\Commands\Command;
use RuntimeException;
use function str_ends_with;
use function ucfirst;

/**
 * @internal An extended Command class that provides validation methods.
 *
 * @see \Hyde\Framework\Testing\Feature\ValidatingCommandTest
 */
class ValidatingCommand extends Command
{
    public const USER_EXIT = 130;

    /** @var int How many times can the validation loop run? It is high enough to not affect normal usage. */
    protected final const MAX_RETRIES = 30;

    /**
     * @return int The exit code.
     */
    public function handle(): int
    {
        try {
            return $this->safeHandle();
        } catch (Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * This method can be overridden by child classes to provide automatic exception handling.
     * Existing code can be converted simply by renaming the handle() method to safeHandle().
     *
     * @return int The exit code.
     */
    protected function safeHandle(): int
    {
        return Command::SUCCESS;
    }

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

        $answer = self::normalizeInput((string) $this->ask(ucfirst($question), $default));
        $validator = Validator::make([$name => $answer], [$name => $rules]);

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

    /**
     * Handle an exception that occurred during command execution.
     *
     * @param  string|null  $file  The file where the exception occurred. Leave null to auto-detect.
     * @return int The exit code
     */
    public function handleException(Exception $exception, ?string $file = null, ?int $line = null): int
    {
        // When testing it might be more useful to see the full stack trace, so we have an option to actually throw the exception.
        if (config('app.throw_on_console_exception', false)) {
            throw $exception;
        }

        // If the exception was thrown from the same file as a command, then we don't need to show which file it was thrown from.
        if (str_ends_with($file ?? $exception->getFile(), 'Command.php')) {
            $this->error("Error: {$exception->getMessage()}");
        } else {
            $this->error(sprintf('Error: %s at ', $exception->getMessage()).sprintf('%s:%s', $file ?? $exception->getFile(), $line ?? $exception->getLine()));
        }

        return Command::FAILURE;
    }

    /**
     * Write a nicely formatted and consistent message to the console. Using InfoComment for a lack of a better term.
     */
    public function infoComment(string $info, string $comment, ?string $moreInfo = null): void
    {
        $this->line("<info>$info</info> [<comment>$comment</comment>]".($moreInfo ? " <info>$moreInfo</info>" : ''));
    }

    protected function translate(string $name, string $error): string
    {
        return __($error, [
            'attribute' => $name,
        ]);
    }

    protected static function normalizeInput(string $param)
    {
        $value = trim($param);

        return $value;
    }
}
