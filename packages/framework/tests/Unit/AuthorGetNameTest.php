<?php

namespace Hyde\Testing\Framework\Unit;

use Hyde\Framework\Models\Author;
use Hyde\Testing\TestCase;

/**
 * Class HasAuthorTest.
 *
 * @covers \Hyde\Framework\Models\Author::getName
 */
class AuthorGetNameTest extends TestCase
{
    // Test get name helper returns name if set
    public function test_get_name_helper_returns_name_if_set()
    {
        $author = new Author('username');
        $author->name = 'John Doe';

        $this->assertEquals('John Doe', $author->getName());
    }

    // Test get name helper returns username if name is not set
    public function test_get_name_helper_returns_username_if_name_is_not_set()
    {
        $author = new Author('username');

        $this->assertEquals('username', $author->getName());
    }
}
