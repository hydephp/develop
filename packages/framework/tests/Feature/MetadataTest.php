<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Meta;
use Hyde\Framework\Features\Metadata\Elements\LinkElement;
use Hyde\Framework\Features\Metadata\Elements\MetadataElement;
use Hyde\Framework\Features\Metadata\Elements\OpenGraphElement;
use Hyde\Framework\Features\Metadata\MetadataBag;
use Hyde\Hyde;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Metadata\MetadataBag
 * @covers \Hyde\Framework\Features\Metadata\PageMetadataBag
 * @covers \Hyde\Framework\Features\Metadata\GlobalMetadataBag
 * @covers \Hyde\Framework\Features\Metadata\Elements\LinkElement
 * @covers \Hyde\Framework\Features\Metadata\Elements\MetadataElement
 * @covers \Hyde\Framework\Features\Metadata\Elements\OpenGraphElement
 */
class MetadataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['hyde.url' => null]);
        config(['hyde.meta' => []]);
        config(['hyde.rss.enabled' => false]);
        config(['hyde.generate_sitemap' => false]);
    }

    protected function assertPageHasMetadata(HydePage $page, string $metadata)
    {
        $this->assertStringContainsString(
            $metadata,
            $page->metadata->render()
        );
    }

    protected function assertPageDoesNotHaveMetadata(HydePage $page, string $metadata)
    {
        $this->assertStringNotContainsString(
            $metadata,
            $page->metadata->render()
        );
    }

    public function testMetadataObjectIsGeneratedAutomatically()
    {
        $page = new MarkdownPage();

        $this->assertNotNull($page->metadata);
        $this->assertInstanceOf(MetadataBag::class, $page->metadata);
        $this->assertEquals([], $page->metadata->get());
    }

    public function testLinkItemModel()
    {
        $item = new LinkElement('rel', 'href');
        $this->assertEquals('rel', $item->uniqueKey());
        $this->assertEquals('<link rel="rel" href="href">', (string) $item);

        $item = new LinkElement('rel', 'href', ['attr' => 'value']);
        $this->assertEquals('<link rel="rel" href="href" attr="value">', (string) $item);
    }

    public function testMetadataItemModel()
    {
        $item = new MetadataElement('name', 'content');
        $this->assertEquals('name', $item->uniqueKey());
        $this->assertEquals('<meta name="name" content="content">', (string) $item);
    }

    public function testOpenGraphItemModel()
    {
        $item = new OpenGraphElement('property', 'content');
        $this->assertEquals('property', $item->uniqueKey());
        $this->assertEquals('<meta property="og:property" content="content">', (string) $item);

        $item = new OpenGraphElement('og:property', 'content');
        $this->assertEquals('<meta property="og:property" content="content">', (string) $item);
    }

    public function testLinkItemCanBeAdded()
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::link('foo', 'bar'));

        $this->assertEquals([
            'links:foo' => Meta::link('foo', 'bar'),
        ], $page->metadata->get());
    }

    public function testMetadataItemCanBeAdded()
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::name('foo', 'bar'));

        $this->assertEquals([
            'metadata:foo' => Meta::name('foo', 'bar'),
        ], $page->metadata->get());
    }

    public function testOpenGraphItemCanBeAdded()
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::property('foo', 'bar'));

        $this->assertEquals([
            'properties:foo' => Meta::property('foo', 'bar'),
        ], $page->metadata->get());
    }

    public function testGenericItemCanBeAdded()
    {
        $page = new MarkdownPage();
        $page->metadata->add('foo');

        $this->assertEquals([
            'generics:0' => 'foo',
        ], $page->metadata->get());
    }

    public function testMultipleItemsCanBeAccessedWithGetMethod()
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::link('foo', 'bar'));
        $page->metadata->add(Meta::name('foo', 'bar'));
        $page->metadata->add(Meta::property('foo', 'bar'));
        $page->metadata->add('foo');

        $this->assertEquals([
            'links:foo' => Meta::link('foo', 'bar'),
            'metadata:foo' => Meta::name('foo', 'bar'),
            'properties:foo' => Meta::property('foo', 'bar'),
            'generics:0' => 'foo',
        ], $page->metadata->get());
    }

    public function testMultipleItemsOfSameKeyAndTypeOnlyKeepsLatest()
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::link('foo', 'bar'));
        $page->metadata->add(Meta::link('foo', 'baz'));

        $this->assertEquals([
            'links:foo' => Meta::link('foo', 'baz'),
        ], $page->metadata->get());
    }

    public function testRenderReturnsHtmlStringOfImplodedMetadataArrays()
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::link('foo', 'bar'));
        $page->metadata->add(Meta::name('foo', 'bar'));
        $page->metadata->add(Meta::property('foo', 'bar'));
        $page->metadata->add('foo');

        $this->assertEquals(implode("\n", [
            '<link rel="foo" href="bar">',
            '<meta name="foo" content="bar">',
            '<meta property="og:foo" content="bar">',
            'foo',
        ]),
            $page->metadata->render());
    }

    public function testCustomMetadataOverridesConfigDefinedMetadata()
    {
        config(['hyde.meta' => [
            Meta::name('foo', 'bar'),
        ]]);
        $page = new MarkdownPage();
        $page->metadata->add(Meta::name('foo', 'baz'));
        $this->assertEquals([
            'metadata:foo' => Meta::name('foo', 'baz'),
        ], $page->metadata->get());
    }

    public function testDynamicMetadataOverridesConfigDefinedMetadata()
    {
        config(['hyde.meta' => [
            Meta::name('twitter:title', 'bar'),
        ]]);
        $page = MarkdownPage::make(matter: ['title' => 'baz']);

        $this->assertEquals([
            'metadata:twitter:title' => Meta::name('twitter:title', 'HydePHP - baz'),
            'properties:title' => Meta::property('title', 'HydePHP - baz'),
        ], $page->metadata->get());
    }

    public function testDoesNotAddCanonicalLinkWhenBaseUrlIsNotSet()
    {
        config(['hyde.url' => null]);
        $page = MarkdownPage::make('bar');

        $this->assertStringNotContainsString('<link rel="canonical"', $page->metadata->render());
    }

    public function testDoesNotAddCanonicalLinkWhenIdentifierIsNotSet()
    {
        config(['hyde.url' => 'foo']);
        $page = MarkdownPage::make();

        $this->assertStringNotContainsString('<link rel="canonical"', $page->metadata->render());
    }

    public function testAddsCanonicalLinkWhenBaseUrlAndIdentifierIsSet()
    {
        config(['hyde.url' => 'foo']);
        $page = MarkdownPage::make('bar');

        $this->assertStringContainsString('<link rel="canonical" href="foo/bar.html">', $page->metadata->render());
    }

    public function testCanonicalLinkUsesCleanUrlSetting()
    {
        config(['hyde.url' => 'foo']);
        config(['hyde.pretty_urls' => true]);
        $page = MarkdownPage::make('bar');

        $this->assertStringContainsString('<link rel="canonical" href="foo/bar">', $page->metadata->render());
    }

    public function testCanOverrideCanonicalLinkWithFrontMatter()
    {
        config(['hyde.url' => 'foo']);
        $page = MarkdownPage::make('bar', [
            'canonicalUrl' => 'canonical',
        ]);
        $this->assertStringContainsString('<link rel="canonical" href="canonical">', $page->metadata->render());
    }

    public function testAddsTwitterAndOpenGraphTitleWhenTitleIsSet()
    {
        $page = MarkdownPage::make(matter: ['title' => 'Foo Bar']);

        $this->assertEquals(
            '<meta name="twitter:title" content="HydePHP - Foo Bar">'."\n".
            '<meta property="og:title" content="HydePHP - Foo Bar">',
            $page->metadata->render()
        );
    }

    public function testDoesNotAddTwitterAndOpenGraphTitleWhenNoTitleIsSet()
    {
        $page = MarkdownPage::make(matter: ['title' => null]);

        $this->assertEquals('',
            $page->metadata->render()
        );
    }

    public function testAddsDescriptionWhenDescriptionIsSetInPost()
    {
        $page = MarkdownPost::make(matter: ['description' => 'My Description']);
        $this->assertPageHasMetadata($page, '<meta name="description" content="My Description">');
    }

    public function testDoesNotAddDescriptionWhenDescriptionIsNotSetInPost()
    {
        $page = new MarkdownPost();
        $this->assertPageDoesNotHaveMetadata($page, '<meta name="description" content="My Description">');
    }

    public function testAddsAuthorWhenAuthorIsSetInPost()
    {
        $page = MarkdownPost::make(matter: ['author' => 'My Author']);
        $this->assertPageHasMetadata($page, '<meta name="author" content="My Author">');
    }

    public function testDoesNotAddAuthorWhenAuthorIsNotSetInPost()
    {
        $page = new MarkdownPost();
        $this->assertPageDoesNotHaveMetadata($page, '<meta name="author" content="My Author">');
    }

    public function testAddsKeywordsWhenCategoryIsSetInPost()
    {
        $page = MarkdownPost::make(matter: ['category' => 'My Category']);
        $this->assertPageHasMetadata($page, '<meta name="keywords" content="My Category">');
    }

    public function testDoesNotAddKeywordsWhenCategoryIsNotSetInPost()
    {
        $page = new MarkdownPost();
        $this->assertPageDoesNotHaveMetadata($page, '<meta name="keywords" content="My Category">');
    }

    public function testAddsUrlPropertyWhenCanonicalUrlIsSetInPost()
    {
        $page = MarkdownPost::make(matter: ['canonicalUrl' => 'example.html']);
        $this->assertPageHasMetadata($page, '<meta property="og:url" content="example.html">');
    }

    public function testDoesNotAddUrlPropertyWhenCanonicalUrlIsNotSetInPost()
    {
        $page = new MarkdownPost();
        $this->assertPageDoesNotHaveMetadata($page, '<meta property="og:url" content="example.html">');
    }

    public function testDoesNotAddUrlPropertyWhenCanonicalUrlIsNull()
    {
        $page = MarkdownPost::make(matter: ['canonicalUrl' => null]);
        $this->assertPageDoesNotHaveMetadata($page, '<meta property="og:url" content="example.html">');
    }

    public function testAddsTitlePropertyWhenTitleIsSetInPost()
    {
        $page = MarkdownPost::make(matter: ['title' => 'My Title']);
        $this->assertPageHasMetadata($page, '<meta property="og:title" content="HydePHP - My Title">');
    }

    public function testDoesNotAddTitlePropertyWhenTitleIsNotSetInPost()
    {
        $page = new MarkdownPost();
        $this->assertPageDoesNotHaveMetadata($page, '<meta property="og:title"');
    }

    public function testAddsPublishedTimePropertyWhenDateIsSetInPost()
    {
        $page = MarkdownPost::make(matter: ['date' => '2022-01-01']);
        $this->assertPageHasMetadata($page, '<meta property="og:article:published_time" content="2022-01-01T00:00:00+00:00">');
    }

    public function testDoesNotAddPublishedTimePropertyWhenDateIsNotSetInPost()
    {
        $page = new MarkdownPost();
        $this->assertPageDoesNotHaveMetadata($page, '<meta property="og:article:published_time" content="2022-01-01T00:00:00+00:00">');
    }

    public function testAddsImagePropertyWhenImageIsSetInPost()
    {
        $page = MarkdownPost::make(matter: ['image' => 'image.jpg']);
        $this->assertPageHasMetadata($page, '<meta property="og:image" content="../media/image.jpg">');
    }

    public function testDoesNotAddImagePropertyWhenImageIsNotSetInPost()
    {
        $page = new MarkdownPost();
        $this->assertPageDoesNotHaveMetadata($page, '<meta property="og:image" content="media/image.jpg">');
    }

    public function testAddsTypePropertyAutomatically()
    {
        $page = MarkdownPost::make();
        $this->assertPageHasMetadata($page, '<meta property="og:type" content="article">');
    }

    public function testDynamicPostMetaPropertiesReturnsBaseArrayWhenInitializedWithEmptyFrontMatter()
    {
        $page = MarkdownPost::make();
        $this->assertEquals('<meta property="og:type" content="article">', $page->metadata->render());
    }

    public function testDynamicPostMetaPropertiesContainsImageMetadataWhenFeaturedImageSetToString()
    {
        $page = MarkdownPost::make(matter: [
            'image' => 'foo.jpg',
        ]);

        $this->assertPageHasMetadata($page, '<meta property="og:image" content="../media/foo.jpg">');
    }

    public function testDynamicPostMetaPropertiesContainsImageLinkThatIsAlwaysRelative()
    {
        $page = MarkdownPost::make(matter: [
            'image' => 'foo.jpg',
        ]);

        $this->assertPageHasMetadata($page, '<meta property="og:image" content="../media/foo.jpg">');
    }

    public function testDynamicPostMetaPropertiesContainsImageLinkThatIsAlwaysRelativeForNestedPosts()
    {
        $page = MarkdownPost::make('foo/bar', matter: [
            'image' => 'foo.jpg',
        ]);

        $this->assertPageHasMetadata($page, '<meta property="og:image" content="../../media/foo.jpg">');
    }

    public function testDynamicPostMetaPropertiesContainsImageLinkThatIsAlwaysRelativeForNestedOutputDirectories()
    {
        MarkdownPost::setOutputDirectory('_posts/foo');
        $page = MarkdownPost::make(matter: [
            'image' => 'foo.jpg',
        ]);

        $this->assertPageHasMetadata($page, '<meta property="og:image" content="../../media/foo.jpg">');
    }

    public function testDynamicPostMetaPropertiesContainsImageLinkThatIsAlwaysRelativeForNestedPostsAndNestedOutputDirectories()
    {
        MarkdownPost::setOutputDirectory('_posts/foo');
        $page = MarkdownPost::make('bar/baz', matter: [
            'image' => 'foo.jpg',
        ]);

        $this->assertPageHasMetadata($page, '<meta property="og:image" content="../../../media/foo.jpg">');
    }

    public function testDynamicPostMetaPropertiesContainsImageLinkThatUsesTheConfiguredMediaDirectory()
    {
        Hyde::setMediaDirectory('assets');
        $page = MarkdownPost::make(matter: [
            'image' => 'foo.jpg',
        ]);

        $this->assertPageHasMetadata($page, '<meta property="og:image" content="../assets/foo.jpg">');
    }

    public function testDynamicPostMetaPropertiesContainsImageMetadataWhenFeaturedImageSetToArrayWithPath()
    {
        $page = MarkdownPost::make(matter: [
            'image' => [
                'source' => 'foo.jpg',
            ],
        ]);

        $this->assertPageHasMetadata($page, '<meta property="og:image" content="../media/foo.jpg">');
    }

    public function testDynamicPostMetaPropertiesContainsImageMetadataWhenFeaturedImageSetToArrayWithUrl()
    {
        $page = MarkdownPost::make(matter: [
            'image' => [
                'source' => 'https://example.com/foo.jpg',
            ],
        ]);

        $this->assertPageHasMetadata($page, '<meta property="og:image" content="https://example.com/foo.jpg">');
    }

    public function testDynamicPostAuthorReturnsAuthorNameWhenAuthorSetToArrayUsingUsername()
    {
        $page = MarkdownPost::make(matter: [
            'author' => [
                'username' => 'username',
            ],
        ]);
        $this->assertPageHasMetadata($page, '<meta name="author" content="username">');
    }

    public function testDynamicPostAuthorReturnsAuthorNameWhenAuthorSetToArrayUsingName()
    {
        $page = MarkdownPost::make(matter: [
            'author' => [
                'name' => 'Name',
            ],
        ]);

        $this->assertPageHasMetadata($page, '<meta name="author" content="Name">');
    }

    public function testNoAuthorIsSetWhenAuthorSetToArrayWithoutNameOrUsername()
    {
        $page = MarkdownPost::make(matter: [
            'author' => [],
        ]);

        $this->assertPageDoesNotHaveMetadata($page, '<meta name="author"');
    }
}
