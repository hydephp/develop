<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing\BladeBlocks;

use InvalidArgumentException;

use function array_map;
use function count;
use function explode;
use function implode;
use function preg_match;
use function preg_replace;
use function preg_split;
use function str_replace;
use function strlen;
use function trim;

class BladeBlockExtractor
{
    /** @var array<string, BladeBlock> */
    protected array $extractedBlocks = [];

    /** @var array<int, string> */
    protected array $outputLines = [];

    /** @var array<int, string> */
    protected array $sourceLines = [];

    /** @return array{array<string, BladeBlock>, string} */
    public function handle(string $markdown): array
    {
        $this->extractedBlocks = [];
        $this->outputLines = [];
        $this->sourceLines = $this->splitIntoLines($markdown);

        $totalLines = count($this->sourceLines);

        for ($lineNumber = 0; $lineNumber < $totalLines; $lineNumber++) {
            $line = $this->sourceLines[$lineNumber];

            $fence = $this->detectOpeningFence($line);

            if ($fence === null) {
                $this->outputLines[] = $line;

                continue;
            }

            $lineNumber = $this->processFencedBlock($line, $fence, $lineNumber, $totalLines);
        }

        return [$this->extractedBlocks, implode("\n", $this->outputLines)];
    }

    protected function processFencedBlock(string $openingLine, array $fence, int $startLine, int $totalLines): int
    {
        [$body, $closingLineNumber] = $this->captureBlockBody($fence, $startLine, $totalLines);

        $wasClosed = $closingLineNumber > $startLine;
        $content = $this->dedent($body, $fence['indent']);

        if ($bladeBlock = $this->parseBladeBlock($fence['info'], $content)) {
            $this->registerExtractedBlock($bladeBlock);
        } else {
            $this->passThroughVerbatim($openingLine, $body, $wasClosed ? $this->sourceLines[$closingLineNumber] : null);
        }

        return $wasClosed ? $closingLineNumber : $totalLines;
    }

    /**
     * Walk forward from the opening fence until we find a matching closer
     * (same fence character, at least as long, and whitespace-only).
     *
     * @return array{0: array<int, string>, 1: int} The body lines and the index of the closing line (or start line if unterminated).
     */
    protected function captureBlockBody(array $fence, int $startLine, int $totalLines): array
    {
        $body = [];

        for ($cursor = $startLine + 1; $cursor < $totalLines; $cursor++) {
            if ($this->lineClosesFence($this->sourceLines[$cursor], $fence)) {
                return [$body, $cursor];
            }

            $body[] = $this->sourceLines[$cursor];
        }

        return [$body, $startLine];
    }

    protected function lineClosesFence(string $line, array $fence): bool
    {
        return preg_match($fence['closerPattern'], $line, $matches)
            && strlen($matches[1]) >= $fence['fenceLength'];
    }

    protected function registerExtractedBlock(BladeBlock $block): void
    {
        $this->extractedBlocks[$block->signature] = $block;
        $this->outputLines[] = $block->signature;
    }

    protected function passThroughVerbatim(string $openingLine, array $body, ?string $closingLine): void
    {
        $this->outputLines[] = $openingLine;

        foreach ($body as $line) {
            $this->outputLines[] = $line;
        }

        if ($closingLine !== null) {
            $this->outputLines[] = $closingLine;
        }
    }

    protected function splitIntoLines(string $markdown): array
    {
        return explode("\n", str_replace(["\r\n", "\r"], "\n", $markdown));
    }

    /**
     * @return array{
     *     indent: int,
     *     fenceChar: string,
     *     fenceLength: int,
     *     info: string,
     *     closerPattern: string
     * }|null
     */
    protected function detectOpeningFence(string $line): ?array
    {
        if (! preg_match('/^(?<indent> {0,3})(?<fence>`{3,}|~{3,})(?<info>.*)$/', $line, $matches)) {
            return null;
        }

        $fenceChar = $matches['fence'][0];

        return [
            'indent' => strlen($matches['indent']),
            'fenceChar' => $fenceChar,
            'fenceLength' => strlen($matches['fence']),
            'info' => trim($matches['info']),
            'closerPattern' => '/^ {0,3}('.$fenceChar.'{3,})[ \t]*$/',
        ];
    }

    protected function dedent(array $lines, int $indent): string
    {
        if ($indent === 0) {
            return implode("\n", $lines);
        }

        return implode("\n", array_map(
            fn (string $line): string => preg_replace('/^ {0,'.$indent.'}/', '', $line),
            $lines,
        ));
    }

    protected function parseBladeBlock(string $info, string $content): ?BladeBlock
    {
        $tokens = preg_split('/\s+/', $info);

        if (! $this->isBladeDirective($tokens)) {
            return null;
        }

        $directive = $tokens[1];

        if ($directive === 'render') {
            return new BladeRenderBlock($content);
        }

        if ($componentName = $this->extractComponentName($directive)) {
            return new BladeComponentBlock($content, $componentName);
        }

        throw new InvalidArgumentException(
            'Invalid Blade block syntax. Expected ```blade render``` or ```blade component(component-name)```.'
        );
    }

    protected function isBladeDirective(array $tokens): bool
    {
        return $tokens[0] === 'blade' && count($tokens) > 1;
    }

    protected function extractComponentName(string $directive): ?string
    {
        if (preg_match('/^component\((?<name>[^)]+)\)$/', $directive, $matches)) {
            return trim($matches['name']);
        }

        return null;
    }
}
