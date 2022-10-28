<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Blogging\Models\PostAuthor;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

/**
 * @covers \Hyde\Framework\Features\Blogging\Models\PostAuthor
 */
class PostAuthorTest extends TestCase
{
    public function test_create_method_creates_new_author_model()
    {
        $author = PostAuthor::create('foo');

        $this->assertInstanceOf(PostAuthor::class, $author);
    }

    public function test_create_method_accepts_all_parameters()
    {
        $author = PostAuthor::create('foo', 'bar', 'https://example.com');

        $this->assertEquals('foo', $author->username);
        $this->assertEquals('bar', $author->name);
        $this->assertEquals('https://example.com', $author->website);
    }

    public function test_make_method_creates_new_author_model_from_string()
    {
        $author = PostAuthor::make('foo');
        $this->assertEquals($author, new PostAuthor('foo'));
    }

    public function test_make_method_creates_new_author_model_from_string_can_find_existing_author()
    {
        Config::set('hyde.authors', [
            PostAuthor::create('foo', 'bar'),
        ]);

        $this->assertEquals(PostAuthor::make('foo'), PostAuthor::create('foo', 'bar'));
    }

    public function test_make_method_creates_new_author_model_from_array()
    {
        $author = PostAuthor::make([
            'username' => 'foo',
            'name' => 'bar',
            'website' => 'https://example.com',
        ]);
        $this->assertEquals($author, PostAuthor::create('foo', 'bar', 'https://example.com'));
    }

    public function test_make_method_creates_new_author_model_from_array_only_needs_username()
    {
        $this->assertEquals(PostAuthor::make(['username' => 'foo']), PostAuthor::create('foo'));
    }

    public function test_all_method_returns_empty_collection_if_no_authors_are_set_in_config()
    {
        Config::set('hyde.authors', []);
        $authors = PostAuthor::all();

        $this->assertInstanceOf(Collection::class, $authors);
        $this->assertCount(0, $authors);
    }

    public function test_all_method_returns_collection_with_all_authors_defined_in_config()
    {
        Config::set('hyde.authors', [
            PostAuthor::create('foo'),
        ]);
        $authors = PostAuthor::all();

        $this->assertInstanceOf(Collection::class, $authors);
        $this->assertCount(1, $authors);
        $this->assertEquals(PostAuthor::create('foo'), $authors->first());
    }

    public function test_multiple_authors_can_be_defined_in_config()
    {
        Config::set('hyde.authors', [
            PostAuthor::create('foo'),
            PostAuthor::create('bar'),
        ]);
        $authors = PostAuthor::all();

        $this->assertInstanceOf(Collection::class, $authors);
        $this->assertCount(2, $authors);
        $this->assertEquals(PostAuthor::create('foo'), $authors->first());
        $this->assertEquals(PostAuthor::create('bar'), $authors->last());
    }

    public function test_get_method_returns_config_defined_author_by_username()
    {
        Config::set('hyde.authors', [
            PostAuthor::create('foo', 'bar'),
        ]);
        $author = PostAuthor::get('foo');

        $this->assertInstanceOf(PostAuthor::class, $author);
        $this->assertEquals('foo', $author->username);
        $this->assertEquals('bar', $author->name);
    }

    public function test_get_method_returns_new_author_if_username_not_found_in_config()
    {
        Config::set('hyde.authors', []);
        $author = PostAuthor::get('foo');

        $this->assertInstanceOf(PostAuthor::class, $author);
        $this->assertEquals('foo', $author->username);
    }

    public function test_get_name_helper_returns_name_if_set()
    {
        $author = new PostAuthor('username');
        $author->name = 'John Doe';

        $this->assertEquals('John Doe', $author->getName());
    }

    public function test_get_name_helper_returns_username_if_name_is_not_set()
    {
        $author = new PostAuthor('username');

        $this->assertEquals('username', $author->getName());
    }

    public function test_to_string_helper_returns_the_name()
    {
        $author = new PostAuthor('username');
        $author->name = 'John Doe';

        $this->assertEquals('John Doe', (string) $author);
    }
}
