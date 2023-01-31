<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\ConvertsMarkdownToPlainText;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\ConvertsMarkdownToPlainText
 */
class ConvertsMarkdownToPlainTextTest extends TestCase
{
    protected function convert(string $markdown): string
    {
        return (new ConvertsMarkdownToPlainText($markdown))->execute();
    }
}
