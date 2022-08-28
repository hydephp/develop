<?php

namespace Hyde\Framework\Modules\Markdown;

use Hyde\Framework\Contracts\MarkdownProcessorContract;
use Hyde\Framework\Contracts\MarkdownShortcodeContract;
use Hyde\Framework\Modules\Markdown\Shortcodes\AbstractColoredBlockquote;

/**
 * Handle shortcode processing for Markdown conversions.
 *
 * The shortcode system has a few limitations, as it is meant to be simple
 * by design so that it is easy to understand how the code works, and
 * what each shortcode does. Shortcodes are expanded on a per-line basis,
 * and do not support multi-line input. Shortcodes are expected to be
 * the very first thing on a line. The signature is a static string
 * that is used to identify the shortcode. The built-in shortcodes
 * do not use regex, as that would make them harder to read.
 *
 * @todo #438 Refactor or cache discovered shortcodes array to singleton as it does not need to be re-instantiated
 *      for each Markdown conversion.
 *
 * @see \Hyde\Framework\Testing\Feature\Services\Markdown\ShortcodeProcessorTest
 * @phpstan-consistent-constructor
 */
class ShortcodeProcessor implements MarkdownProcessorContract
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
     */
    public array $shortcodes;

    public function __construct(string $input)
    {
        $this->input = $input;

        $this->discoverShortcodes();
    }

    public function processInput(): static
    {
        $this->output = implode("\n", array_map(function ($line) {
            return $this->expandShortcode($line);
        }, explode("\n", $this->input)));

        return $this;
    }

    public function getOutput(): string
    {
        return $this->output;
    }

    public function run(): string
    {
        return $this->processInput()->getOutput();
    }

    public static function process(string $input): string
    {
        return (new static($input))->run();
    }

    protected function discoverShortcodes(): void
    {
        $this->addShortcodesFromArray(array_merge(
            AbstractColoredBlockquote::get(),
        ));
    }

    public function addShortcodesFromArray(array $shortcodes): static
    {
        foreach ($shortcodes as $shortcode) {
            $this->addShortcode($shortcode);
        }

        return $this;
    }

    public function addShortcode(MarkdownShortcodeContract $shortcode): static
    {
        $this->shortcodes[$shortcode::signature()] = $shortcode;

        return $this;
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
}
