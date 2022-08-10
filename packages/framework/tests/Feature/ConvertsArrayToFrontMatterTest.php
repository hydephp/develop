<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\ConvertsArrayToFrontMatter;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\ConvertsArrayToFrontMatter
 */
class ConvertsArrayToFrontMatterTest extends TestCase
{
    public function test_action_converts_an_array_to_front_matter()
    {
        $array = [
            'key' => 'value',
            'string' => 'quoted string',
            'boolean' => true,
            'integer' => 100,
            // 'array' => ['key' => 'value'],
            // 'list' => ['foo', 'bar'],
        ];
        $expected = <<<'YAML'
---
key: value
string: quoted string
boolean: 1
integer: 100
---

YAML;
        $this->assertEquals($expected, (new ConvertsArrayToFrontMatter)->execute($array));
    }

    public function test_action_returns_empty_string_if_array_is_empty()
    {
        $this->assertEquals('', (new ConvertsArrayToFrontMatter)->execute([]));
    }
}
