<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Facades\Author;
use Hyde\Pages\MarkdownPost;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;
use Hyde\Testing\UnitTestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

/**
 * @covers \Hyde\Framework\Features\Blogging\Models\PostAuthor
 */
class PostAuthorTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    protected function setUp(): void
    {
        parent::setUp();

        self::resetKernel();
    }

    public function testCanCreateAuthorModel()
    {
        $author = new PostAuthor('foo');

        $this->assertInstanceOf(PostAuthor::class, $author);
    }

    public function testCanCreateAuthorModelWithDetails()
    {
        $author = new PostAuthor('foo', 'bar', 'https://example.com');

        $this->assertSame('foo', $author->username);
        $this->assertSame('bar', $author->name);
        $this->assertSame('https://example.com', $author->website);
    }

    public function testCanCreateAuthorModelWithFullDetails()
    {
        [$username, $name, $website, $bio, $avatar, $socials] = array_values($this->exampleData());

        $author = new PostAuthor(
            username: $username,
            name: $name,
            website: $website,
            bio: $bio,
            avatar: $avatar,
            socials: $socials
        );

        $this->assertSame($username, $author->username);
        $this->assertSame($name, $author->name);
        $this->assertSame($website, $author->website);
        $this->assertSame($bio, $author->bio);
        $this->assertSame($avatar, $author->avatar);
        $this->assertSame($socials, $author->socials);
    }

    public function testCanCreateAuthorModelWithFullDetailsFromArgumentUnpacking()
    {
        $data = $this->exampleData();

        $author = new PostAuthor(...$data);

        $this->assertSame($data['username'], $author->username);
        $this->assertSame($data['name'], $author->name);
        $this->assertSame($data['website'], $author->website);
        $this->assertSame($data['bio'], $author->bio);
        $this->assertSame($data['avatar'], $author->avatar);
        $this->assertSame($data['socials'], $author->socials);
    }

    public function testCanCreateAuthorModelWithFullDetailsFromArrayUsingGetOrCreate()
    {
        $data = $this->exampleData();

        $author = PostAuthor::getOrCreate($data);

        $this->assertSame($data['username'], $author->username);
        $this->assertSame($data['name'], $author->name);
        $this->assertSame($data['website'], $author->website);
        $this->assertSame($data['bio'], $author->bio);
        $this->assertSame($data['avatar'], $author->avatar);
        $this->assertSame($data['socials'], $author->socials);
    }

    public function testCanCreateAuthorModelWithSomeDetailsFromArrayUsingGetOrCreate()
    {
        $data = $this->exampleData();

        $author = PostAuthor::getOrCreate([
            'username' => $data['username'],
            'name' => $data['name'],
            'website' => $data['website'],
        ]);

        $this->assertSame($data['username'], $author->username);
        $this->assertSame($data['name'], $author->name);
        $this->assertSame($data['website'], $author->website);
        $this->assertNull($author->bio);
        $this->assertNull($author->avatar);
        $this->assertEmpty($author->socials);
    }

    public function testCanCreateAuthorModelWithSomeDetailsFromArrayUsingGetOrCreateWithoutUsername()
    {
        $data = $this->exampleData();

        $author = PostAuthor::getOrCreate([
            'name' => $data['name'],
            'website' => $data['website'],
        ]);

        $this->assertSame($data['name'], $author->username);
        $this->assertSame($data['name'], $author->name);
        $this->assertSame($data['website'], $author->website);
    }

    public function testCanCreateAuthorModelWithSomeDetailsFromArrayUsingGetOrCreateWithoutAnyNames()
    {
        $data = $this->exampleData();

        $author = PostAuthor::getOrCreate([
            'website' => $data['website'],
        ]);

        $this->assertSame('Guest', $author->username);
        $this->assertSame('Guest', $author->name);
        $this->assertSame($data['website'], $author->website);
    }

    public function testNameIsSetToUsernameIfNoNameIsProvided()
    {
        $author = new PostAuthor('foo');

        $this->assertSame('foo', $author->name);
    }

    public function testCreateMethodCreatesNewAuthorModel()
    {
        $author = Author::create('foo');

        $this->assertInstanceOf(PostAuthor::class, $author);
    }

    public function testCreateMethodAcceptsExtraParameters()
    {
        $author = Author::create('foo', 'bar', 'https://example.com');

        $this->assertSame('foo', $author->username);
        $this->assertSame('bar', $author->name);
        $this->assertSame('https://example.com', $author->website);
    }

    public function testCreateMethodAcceptsAllParameters()
    {
        $author = Author::create(...$this->exampleData());

        $this->assertSame('mr_hyde', $author->username);
        $this->assertSame('Mr. Hyde', $author->name);
        $this->assertSame('https://HydePHP.com', $author->website);
        $this->assertSame('A mysterious figure. Is he as evil as he seems? And what did he do with Dr. Jekyll?', $author->bio);
        $this->assertSame('mr_hyde.png', $author->avatar);
        $this->assertSame(['twitter' => 'HydeFramework', 'github' => 'hydephp', 'custom' => 'https://example.com'], $author->socials);
    }

    public function testGetOrCreateMethodCreatesNewAuthorModelFromString()
    {
        $author = PostAuthor::getOrCreate('foo');
        $this->assertEquals($author, new PostAuthor('foo'));
    }

    public function testGetOrCreateMethodCreatesNewAuthorModelFromStringCanFindExistingAuthor()
    {
        Config::set('hyde.authors', [
            'foo' => Author::create('foo', 'bar'),
        ]);

        $this->assertEquals(PostAuthor::getOrCreate('foo'), Author::create('foo', 'bar'));
    }

    public function testGetOrCreateMethodCreatesNewAuthorModelFromArray()
    {
        $author = PostAuthor::getOrCreate([
            'username' => 'foo',
            'name' => 'bar',
            'website' => 'https://example.com',
        ]);

        $this->assertEquals($author, Author::create('foo', 'bar', 'https://example.com'));
    }

    public function testGetOrCreateMethodCreatesNewAuthorModelFromArrayOnlyNeedsUsername()
    {
        $this->assertEquals(PostAuthor::getOrCreate(['username' => 'foo']), Author::create('foo'));
    }

    public function testCanDefineAuthorWithNoDataInConfig()
    {
        Config::set('hyde.authors', [
            'foo' => Author::create(),
        ]);

        $authors = PostAuthor::all();

        $this->assertInstanceOf(Collection::class, $authors);
        $this->assertCount(1, $authors);
        $this->assertEquals(new PostAuthor('foo', ''), $authors->first());
    }

    public function testAllMethodReturnsEmptyCollectionIfNoAuthorsAreSetInConfig()
    {
        Config::set('hyde.authors', []);
        $authors = PostAuthor::all();

        $this->assertInstanceOf(Collection::class, $authors);
        $this->assertCount(0, $authors);
    }

    public function testAllMethodReturnsCollectionWithAllAuthorsDefinedInConfig()
    {
        Config::set('hyde.authors', [
            'foo' => Author::create('foo'),
        ]);

        $authors = PostAuthor::all();

        $this->assertInstanceOf(Collection::class, $authors);
        $this->assertCount(1, $authors);
        $this->assertEquals(new PostAuthor('foo'), $authors->first());
    }

    public function testMultipleAuthorsCanBeDefinedInConfig()
    {
        Config::set('hyde.authors', [
            'foo' => Author::create('foo'),
            'bar' => Author::create('bar'),
        ]);

        $authors = PostAuthor::all();

        $this->assertInstanceOf(Collection::class, $authors);
        $this->assertCount(2, $authors);
        $this->assertEquals(new PostAuthor('foo'), $authors->first());
        $this->assertEquals(new PostAuthor('bar'), $authors->last());
    }

    public function testGetMethodReturnsConfigDefinedAuthorByUsername()
    {
        Config::set('hyde.authors', [
            'foo' => Author::create('foo', 'bar'),
        ]);
        $author = PostAuthor::get('foo');

        $this->assertInstanceOf(PostAuthor::class, $author);
        $this->assertSame('foo', $author->username);
        $this->assertSame('bar', $author->name);
    }

    public function testGetMethodReturnsNewAuthorIfUsernameNotFoundInConfig()
    {
        Config::set('hyde.authors', []);
        $author = PostAuthor::get('foo');

        $this->assertInstanceOf(PostAuthor::class, $author);
        $this->assertSame('foo', $author->username);
    }

    public function testNameIsSetToUsernameIfNameIsNotSet()
    {
        $author = new PostAuthor('username');

        $this->assertSame('username', $author->name);
    }

    public function testToStringHelperReturnsTheName()
    {
        $author = new PostAuthor('username', 'John Doe');

        $this->assertSame('John Doe', (string) $author);
    }

    public function testToArrayMethodReturnsArrayRepresentationOfAuthor()
    {
        $author = new PostAuthor('username', 'John Doe', 'https://example.com');

        $this->assertEquals([
            'username' => 'username',
            'name' => 'John Doe',
            'website' => 'https://example.com',
        ], $author->toArray());
    }

    public function testJsonSerializeMethodReturnsArrayRepresentationOfAuthor()
    {
        $author = new PostAuthor('username', 'John Doe', 'https://example.com');

        $this->assertEquals([
            'username' => 'username',
            'name' => 'John Doe',
            'website' => 'https://example.com',
        ], $author->jsonSerialize());
    }

    public function testArraySerializeMethodReturnsArrayRepresentationOfAuthor()
    {
        $author = new PostAuthor('username', 'John Doe', 'https://example.com');

        $this->assertEquals([
            'username' => 'username',
            'name' => 'John Doe',
            'website' => 'https://example.com',
        ], $author->arraySerialize());
    }

    public function testToJsonMethodReturnsJsonRepresentationOfAuthor()
    {
        $author = new PostAuthor('username', 'John Doe', 'https://example.com');

        $this->assertSame('{"username":"username","name":"John Doe","website":"https:\/\/example.com"}', $author->toJson());
    }

    public function testCanJsonEncodeAuthor()
    {
        $author = new PostAuthor('username', 'John Doe', 'https://example.com');

        $this->assertSame('{"username":"username","name":"John Doe","website":"https:\/\/example.com"}', json_encode($author));
    }

    public function testEmptyFieldsAreRemovedFromSerializedModel()
    {
        $author = new PostAuthor('username', null, null);

        $this->assertSame('{"username":"username","name":"username"}', $author->toJson());
    }

    public function testToArrayMethodSerializesAllData()
    {
        $data = $this->exampleData();

        $author = new PostAuthor(...$data);

        $this->assertSame($data, $author->toArray());
    }

    public function testGetPostsWithNoPosts()
    {
        $author = new PostAuthor('username');

        $this->assertSame([], $author->getPosts()->all());
    }

    public function testGetPostsReturnsAllPostsByAuthor()
    {
        Hyde::pages()->addPage(new MarkdownPost('foo', ['author' => 'username']));
        Hyde::pages()->addPage(new MarkdownPost('bar', ['author' => 'username']));
        Hyde::pages()->addPage(new MarkdownPost('baz', ['author' => 'other']));
        Hyde::pages()->addPage(new MarkdownPost('qux'));

        $author = new PostAuthor('username');

        $this->assertCount(2, $author->getPosts());
        $this->assertSame('username', $author->getPosts()->first()->author->username);
        $this->assertSame('username', $author->getPosts()->last()->author->username);

        $this->assertSame('foo', $author->getPosts()->first()->identifier);
        $this->assertSame('bar', $author->getPosts()->last()->identifier);

        $this->assertEquals($author, $author->getPosts()->first()->author);
        $this->assertEquals($author, $author->getPosts()->last()->author);
    }

    /**
     * @return array{username: string, name: string, website: string, bio: string, avatar: string, socials: array{twitter: string, github: string, custom: string}}
     */
    protected function exampleData(): array
    {
        return [
            'username' => 'mr_hyde',
            'name' => 'Mr. Hyde',
            'website' => 'https://HydePHP.com',
            'bio' => 'A mysterious figure. Is he as evil as he seems? And what did he do with Dr. Jekyll?',
            'avatar' => 'mr_hyde.png',
            'socials' => ['twitter' => 'HydeFramework', 'github' => 'hydephp', 'custom' => 'https://example.com'],
        ];
    }
}
