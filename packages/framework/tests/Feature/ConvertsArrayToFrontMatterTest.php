<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\ConvertsArrayToFrontMatter;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\ConvertsArrayToFrontMatter
 */
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
        $this->assertEquals(str_replace("\r", '', $expected), (new ConvertsArrayToFrontMatter)->execute($array));
    }

    public function testActionReturnsEmptyStringIfArrayIsEmpty()
    {
        $this->assertEquals('', (new ConvertsArrayToFrontMatter)->execute([]));
    }
}
