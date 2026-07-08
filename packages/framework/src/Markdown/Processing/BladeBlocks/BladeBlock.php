<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing\BladeBlocks;

use function hash;
use function implode;
use function sprintf;

abstract class BladeBlock
{
    protected string $content;

    public readonly string $signature;

    abstract protected function render(): string;

    private static int $sequence = 1;

    public function __construct(string $content)
    {
        $this->content = $content;

        $this->signature = sprintf('<!-- HYDE[BladeBlock]%s -->',
            hash('sha256', implode("\0", $this->getHashableContent())),
        );
    }

    public function compile(): string
    {
        return sprintf(
            '<div class="blade-block not-prose">%s</div>',
            $this->render(),
        );
    }

    /** @return array<string> */
    protected function getHashableContent(): array
    {
        return [static::class, self::$sequence++, $this->content];
    }
}
