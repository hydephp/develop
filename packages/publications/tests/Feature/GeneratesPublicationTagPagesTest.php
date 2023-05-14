<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\Pages\PublicationPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Publications\Actions\GeneratesPublicationTagPages
 */
class GeneratesPublicationTagPagesTest extends TestCase
{
    public function test_tags_index_page_is_generated_when_tags_are_used()
    {
        $this->directory('test-publication');

        $type = new PublicationType('test-publication', fields: [[
            'name' => 'tag',
            'type' => 'tag',
        ]]);
        $type->save();

        $page = new PublicationPage('foo', [
            'tag' => 'bar',
        ], type: $type);
        $page->save();

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertInstanceOf(
            InMemoryPage::class,
            $booted->getPage('tags/index')
        );

        $this->assertSame([
            '_pages/404.blade.php',
            '_pages/index.blade.php',
            'test-publication/foo.md',
            'test-publication/index',
            'tags/index',
            'tags/bar',
        ], $booted->getPages()->keys()->toArray());
    }

    public function test_tags_index_page_is_not_generated_when_tags_are_not_used()
    {
        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertSame([
            '_pages/404.blade.php',
            '_pages/index.blade.php',
        ], $booted->getPages()->keys()->toArray());
    }

    public function test_tags_pages_for_publications_are_generated_for_used_tag()
    {
        $this->directory('publication');
        (new PublicationType('publication', fields: [
            ['name' => 'general', 'type' => 'tag'],
        ]))->save();
        $this->file('publication/foo.md', "---\ngeneral: foo\n---\n");

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertSame([
            '_pages/404.blade.php',
            '_pages/index.blade.php',
            'publication/foo.md',
            'publication/index',
            'tags/index',
            'tags/foo',
        ], $booted->getPages()->keys()->toArray());
    }

    public function test_tags_pages_for_publications_are_generated_for_used_tags_with_publication_tags_array()
    {
        $this->directory('publication');
        (new PublicationType('publication', fields: [
            ['name' => 'general', 'type' => 'tag'],
        ]))->save();
        $this->markdown('publication/foo.md', matter: ['general' => ['foo', 'bar']]);

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertSame([
            '_pages/404.blade.php',
            '_pages/index.blade.php',
            'publication/foo.md',
            'publication/index',
            'tags/index',
            'tags/foo',
            'tags/bar',
        ], $booted->getPages()->keys()->toArray());
    }

    public function test_tags_pages_for_publications_are_not_generated_when_no_tags_are_used()
    {
        $this->createPublication();
        (new PublicationType('publication', fields: [
            ['name' => 'general', 'type' => 'tag'],
        ]))->save();

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertSame([
            '_pages/404.blade.php',
            '_pages/index.blade.php',
            'publication/foo.md',
            'publication/index',
        ], $booted->getPages()->keys()->toArray());
    }

    public function test_generated_index_page()
    {
        $this->createPublication();
        (new PublicationType('publication', fields: [
            ['name' => 'general', 'type' => 'tag'],
        ]))->save();
        $this->file('publication/foo.md', "---\ngeneral: 'foo'\n---\n");
        $this->file('publication/bar.md', "---\ngeneral: 'bar'\n---\n");
        $this->file('publication/baz.md', "---\ngeneral: 'bar'\n---\n");

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $page = $booted->getPage('tags/index');

        $this->assertInstanceOf(InMemoryPage::class, $page);

        $this->assertSame('tags/index', $page->identifier);
        $this->assertSame('tags/index', $page->getRouteKey());
        $this->assertSame('tags/index.html', $page->getOutputPath());
        $this->assertSame('Tags', $page->title);

        $this->assertSame(['tags' => ['bar' => 2, 'foo' => 1]], $page->matter->toArray());
    }

    public function test_generated_detail_page()
    {
        $this->createPublication();
        (new PublicationType('publication', fields: [
            ['name' => 'general', 'type' => 'tag'],
        ]))->save();
        $this->file('publication/foo.md', "---\ngeneral: 'foo'\n---\n");
        $this->file('publication/bar.md', "---\ngeneral: 'bar'\n---\n");
        $this->file('publication/baz.md', "---\ngeneral: 'bar'\n---\n");

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $page = $booted->getPage('tags/foo');

        $this->assertInstanceOf(InMemoryPage::class, $page);

        $this->assertSame('tags/foo', $page->identifier);
        $this->assertSame('tags/foo', $page->getRouteKey());
        $this->assertSame('tags/foo.html', $page->getOutputPath());
        $this->assertSame('Foo', $page->title);

        $this->assertEquals(['tag' => 'foo', 'publications' => [PublicationPage::get('publication/foo')]], $page->matter->toArray());
    }

    protected function createPublication(): void
    {
        $this->directory('publication');

        (new PublicationType('publication'))->save();
        (new PublicationPage('foo', [], '', PublicationType::get('publication')))->save();
    }
}
