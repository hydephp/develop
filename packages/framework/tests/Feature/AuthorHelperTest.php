<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Models\Author;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

/**
 * Class AuthorHelperTest.
 *
 * @covers \Hyde\Framework\Models\Author
 */
class AuthorHelperTest extends TestCase
{
    public function test_create_method_creates_new_author_model()
    {
        $author = Author::create('foo');

        $this->assertInstanceOf(Author::class, $author);
    }

    public function test_create_method_accepts_all_parameters()
    {
        $author = Author::create('foo', 'bar', 'https://example.com');

        $this->assertEquals('foo', $author->username);
        $this->assertEquals('bar', $author->name);
        $this->assertEquals('https://example.com', $author->website);
    }

    public function test_all_method_returns_empty_collection_if_no_authors_are_set_in_config()
    {
        Config::set('authors', []);
        $authors = Author::all();

        $this->assertInstanceOf(Collection::class, $authors);
        $this->assertCount(0, $authors);
    }

    public function test_all_method_returns_collection_with_all_authors_defined_in_config()
    {
        Config::set('authors', [
            Author::create('foo'),
        ]);
        $authors = Author::all();

        $this->assertInstanceOf(Collection::class, $authors);
        $this->assertCount(1, $authors);
        $this->assertEquals(Author::create('foo'), $authors->first());
    }

    public function test_multiple_authors_can_be_defined_in_config()
    {
        Config::set('authors', [
            Author::create('foo'),
            Author::create('bar'),
        ]);
        $authors = Author::all();

        $this->assertInstanceOf(Collection::class, $authors);
        $this->assertCount(2, $authors);
        $this->assertEquals(Author::create('foo'), $authors->first());
        $this->assertEquals(Author::create('bar'), $authors->last());
    }

    public function test_get_method_returns_config_defined_author_by_username()
    {
        Config::set('authors', [
            Author::create('foo', 'bar'),
        ]);
        $author = Author::get('foo');

        $this->assertInstanceOf(Author::class, $author);
        $this->assertEquals('foo', $author->username);
        $this->assertEquals('bar', $author->name);
    }

    public function test_get_method_returns_new_author_if_username_not_found_in_config()
    {
        Config::set('authors', []);
        $author = Author::get('foo');

        $this->assertInstanceOf(Author::class, $author);
        $this->assertEquals('foo', $author->username);
    }

    public function test_get_name_helper_returns_name_if_set()
    {
        $author = new Author('username');
        $author->name = 'John Doe';

        $this->assertEquals('John Doe', $author->getName());
    }

    public function test_get_name_helper_returns_username_if_name_is_not_set()
    {
        $author = new Author('username');

        $this->assertEquals('username', $author->getName());
    }
}
