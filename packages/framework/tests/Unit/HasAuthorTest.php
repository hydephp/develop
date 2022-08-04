<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Concerns\HasAuthor;
use Hyde\Framework\Models\Author;
use Hyde\Framework\Models\FrontMatter;
use Hyde\Testing\TestCase;

/**
 * Class HasAuthorTest.
 *
 * @covers \Hyde\Framework\Concerns\HasAuthor
 */
class HasAuthorTest extends TestCase
{
    use HasAuthor;

    protected FrontMatter $matter;

    protected function matter(...$args)
    {
        return $this->matter->get(...$args);
    }

    public function test_it_can_create_a_new_author_instance_from_username_string()
    {
        $this->matter = FrontMatter::fromArray([
            'author' => 'John Doe',
        ]);

        $this->constructAuthor();
        $this->assertInstanceOf(Author::class, $this->author);
        $this->assertEquals('John Doe', $this->author->username);
        $this->assertNull($this->author->name);
        $this->assertNull($this->author->website);
    }

    public function test_it_can_create_a_new_author_instance_from_user_array()
    {
        $this->matter = FrontMatter::fromArray(['author' => [
            'username' => 'john_doe',
            'name' => 'John Doe',
            'website' => 'https://example.com',
        ]]);
        $this->constructAuthor();
        $this->assertInstanceOf(Author::class, $this->author);
        $this->assertEquals('john_doe', $this->author->username);
        $this->assertEquals('John Doe', $this->author->name);
        $this->assertEquals('https://example.com', $this->author->website);
    }
}
