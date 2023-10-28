<?php

declare(strict_types=1);

namespace Hyde\Console\Concerns;

use Closure;
use RuntimeException;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;

use function max;
use function sprintf;
use function array_map;
use function str_repeat;
use function array_keys;
use function func_get_args;

/**
 * @interal Adds a better choice helper for console commands.
 */
trait BetterChoiceHelper
{
    private int $retryCount = 0;

    protected function betterChoice(string $question, array $choices, int|string $default = null, ?Closure $displayDefaultUsing = null): string|array
    {
        if ($this->input->isInteractive() && $this->retryCount === 0) {
            $this->newLine();
        }

        if ($this->input->isInteractive()) {
            $defaultText = $choices[$default] ?? $default;
            if ($displayDefaultUsing !== null) {
                $defaultText = $displayDefaultUsing($defaultText);
            }
            $this->line(sprintf(' <info>%s</info> [<comment>%s</comment>]:', $question, OutputFormatter::escape($defaultText)));

            $maxWidth = max(array_map([Helper::class, 'width'], array_keys($choices)));
            foreach ($choices as $key => $value) {
                $padding = str_repeat(' ', $maxWidth - Helper::width((string) $key));
                $this->line(sprintf("  [<comment>%s$padding</comment>] %s", $key, $value));
            }

            $this->output->write(' > ');
            $answer = (new SymfonyQuestionHelper())->ask($this->input, new NullOutput, new Question($question, $default));
        } else {
            $answer = $default;
        }

        $selection = $choices[$answer] ?? null;

        if ($selection === null) {
            // High enough to not impact normal usage, but low enough to break loops
            if ($this->retryCount > 30) {
                throw new RuntimeException('Maximum retries exceeded');
            }

            $this->retryCount++;
            $this->output->error(sprintf('Invalid option "%s"', $answer));
            $this->betterChoice(...func_get_args());
        } else {
            if ($this->input->isInteractive()) {
                $this->newLine();
            }
            $this->retryCount = 0;
        }

        return $selection;
    }
}
