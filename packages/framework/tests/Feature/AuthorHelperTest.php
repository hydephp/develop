<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Helpers\Author as AuthorHelper;
use Hyde\Framework\Models\Author as AuthorModel;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

/**
 * Class AuthorHelperTest.
 *
 * @covers \Hyde\Framework\Helpers\Author
 */
class AuthorHelperTest extends TestCase
{
    public function test_create_method_creates_new_author_model()
    {
        $author = AuthorHelper::create('foo');

        $this->assertInstanceOf(AuthorModel::class, $author);
    }

    public function test_create_method_accepts_all_parameters()
    {
        $author = AuthorHelper::create('foo', 'bar', 'https://example.com');

        $this->assertEquals('foo', $author->username);
        $this->assertEquals('bar', $author->name);
        $this->assertEquals('https://example.com', $author->website);
    }

    public function test_all_method_returns_empty_collection_if_no_authors_are_set_in_config()
    {
        Config::set('authors', []);
        $authors = AuthorHelper::all();

        $this->assertInstanceOf(Collection::class, $authors);
        $this->assertCount(0, $authors);
    }

    public function test_all_method_returns_collection_with_all_authors_defined_in_config()
    {
        Config::set('authors', [
            AuthorHelper::create('foo'),
        ]);
        $authors = AuthorHelper::all();

        $this->assertInstanceOf(Collection::class, $authors);
        $this->assertCount(1, $authors);
        $this->assertEquals(AuthorHelper::create('foo'), $authors->first());
    }

    public function test_multiple_authors_can_be_defined_in_config()
    {
        Config::set('authors', [
            AuthorHelper::create('foo'),
            AuthorHelper::create('bar'),
        ]);
        $authors = AuthorHelper::all();

        $this->assertInstanceOf(Collection::class, $authors);
        $this->assertCount(2, $authors);
        $this->assertEquals(AuthorHelper::create('foo'), $authors->first());
        $this->assertEquals(AuthorHelper::create('bar'), $authors->last());
    }

    public function test_get_method_returns_config_defined_author_by_username()
    {
        Config::set('authors', [
            AuthorHelper::create('foo', 'bar'),
        ]);
        $author = AuthorHelper::get('foo');

        $this->assertInstanceOf(AuthorModel::class, $author);
        $this->assertEquals('foo', $author->username);
        $this->assertEquals('bar', $author->name);
    }

    public function test_get_method_returns_new_author_if_username_not_found_in_config()
    {
        Config::set('authors', []);
        $author = AuthorHelper::get('foo');

        $this->assertInstanceOf(AuthorModel::class, $author);
        $this->assertEquals('foo', $author->username);
    }
}
