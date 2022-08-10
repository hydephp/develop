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
            'title' => 'My Title',
            'description' => 'My Description',
            'author' => 'My Author',
            'date' => 'My Date',
            // 'tags' => ['My Tag', 'My Other Tag'],
        ];
        $expected = <<<YAML
---
title: My Title
description: My Description
author: My Author
date: My Date
---

YAML;
        $this->assertEquals($expected, (new ConvertsArrayToFrontMatter)->execute($array));
    }

    public function test_action_returns_empty_string_if_array_is_empty()
    {
        $this->assertEquals('', (new ConvertsArrayToFrontMatter)->execute([]));
    }
}
