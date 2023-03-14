<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing;

use Hyde\Markdown\Contracts\MarkdownPreProcessorContract;
use Hyde\Markdown\Contracts\MarkdownShortcodeContract;

use function array_key_exists;
use function array_map;
use function explode;
use function implode;
use function strpos;
use function substr;

/**
 * Handle all shortcode processing for a Markdown conversion.
 *
 * The shortcode system has a few limitations, as it is meant to be simple
 * by design so that it is easy to understand how the code works, and
 * what each shortcode does. Shortcodes are expanded on a per-line basis,
 * and do not support multi-line input. Shortcodes are expected to be
 * the very first thing on a line. The signature is a static string
 * that is used to identify the shortcode. The built-in shortcodes
 * do not use regex, as that would make them harder to read.
 *
 *
 * @phpstan-consistent-constructor
 */
class ShortcodeProcessor implements MarkdownPreProcessorContract
{
    /**
     * The input Markdown document body.
     */
    protected string $input;

    /**
     * The processed Markdown document body.
     */
    protected string $output;

    /**
     * The activated shortcode instances.
     *
     * @var array<string, MarkdownShortcodeContract>
     */
    protected array $shortcodes;

    public static function preprocess(string $markdown): string
    {
        return (new static($markdown))->run();
    }

    /** @internal This class may be converted to a singleton. Thus, this constructor should not be relied upon. Use preprocess instead.  */
    public function __construct(string $input)
    {
        $this->input = $input;

        $this->discoverShortcodes();
    }

    /** @internal Use the preprocess method */
    public function run(): string
    {
        return $this->processInput()->getOutput();
    }

    /**
     * @internal As the shortcodes are currently added per-instance, this method is not useful outside of this class.
     *
     * @return array<string, MarkdownShortcodeContract>
     */
    public function getShortcodes(): array
    {
        return $this->shortcodes;
    }

    /**
     * @internal As the shortcodes are currently added per-instance, this method is not useful outside of this class.
     *
     * @param  array<MarkdownShortcodeContract>  $shortcodes
     */
    public function addShortcodesFromArray(array $shortcodes): void
    {
        foreach ($shortcodes as $shortcode) {
            $this->addShortcode($shortcode);
        }
    }

    /** @internal As the shortcodes are currently added per-instance, this method is not useful outside of this class. */
    public function addShortcode(MarkdownShortcodeContract $shortcode): void
    {
        $this->shortcodes[$shortcode::signature()] = $shortcode;
    }

    protected function discoverShortcodes(): void
    {
        $this->addShortcodesFromArray(
            ColoredBlockquotes::get()
        );
    }

    protected function getOutput(): string
    {
        return $this->output;
    }

    protected function expandShortcode(string $line): string
    {
        return array_key_exists($signature = $this->discoverSignature($line), $this->shortcodes)
            ? $this->shortcodes[$signature]::resolve($line)
            : $line;
    }

    protected function discoverSignature(string $line): string
    {
        return str_contains($line, ' ') ? substr($line, 0, strpos($line, ' ')) : $line;
    }

    protected function processInput(): static
    {
        $this->output = implode("\n", array_map(function (string $line): string {
            return $this->expandShortcode($line);
        }, explode("\n", $this->input)));

        return $this;
    }
}
