<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\ConvertsArrayToFrontMatter;
use Hyde\Testing\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Actions\ConvertsArrayToFrontMatter::class)]
class ConvertsArrayToFrontMatterTest extends TestCase
{
    public function testActionConvertsAnArrayToFrontMatter()
    {
        $array = [
            'key' => 'value',
            'string' => 'quoted string',
            'boolean' => true,
            'integer' => 100,
            'array' => ['key' => 'value'],
            'list' => ['foo', 'bar'],
        ];
        $expected = <<<'YAML'
---
key: value
string: 'quoted string'
boolean: true
integer: 100
array:
    key: value
list:
    - foo
    - bar
---

YAML;
        $this->assertSame(str_replace("\r", '', $expected), (new ConvertsArrayToFrontMatter)->execute($array));
    }

    public function testActionReturnsEmptyStringIfArrayIsEmpty()
    {
        $this->assertSame('', (new ConvertsArrayToFrontMatter)->execute([]));
    }
}
