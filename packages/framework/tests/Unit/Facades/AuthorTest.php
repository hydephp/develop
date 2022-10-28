<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Facades\Author;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Facades\Author
 */
class AuthorTest extends TestCase
{
    public function testCreate()
    {
        $author = Author::create('john_doe', 'John Doe', 'https://johndoe.com');

        $this->assertSame('john_doe', $author->username);
        $this->assertSame('John Doe', $author->name);
        $this->assertSame('https://johndoe.com', $author->website);
        
        $this->assertEquals(PostAuthor::create('foo'), Author::create('foo'));
    }
}
