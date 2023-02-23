<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Publications\Models\PublicationListPage;
use Hyde\Publications\Models\PublicationPage;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\PublicationsExtension;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Publications\PublicationsExtension
 */
class PublicationsExtensionTest extends TestCase
{
    public function test_get_page_classes_method()
    {
        $this->assertSame([
            PublicationPage::class,
            PublicationListPage::class,
        ], PublicationsExtension::getPageClasses());
    }

    public function test_publication_pages_are_discovered()
    {
        $this->createPublication();

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertCount(4, $booted->getPages()); // Default pages + publication index + publication page
        $this->assertInstanceOf(PublicationPage::class, $booted->getPages()->get('publication/foo.md'));
    }

    public function test_listing_pages_for_publications_are_discovered()
    {
        $this->createPublication();

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertInstanceOf(
            PublicationListPage::class,
            $booted->getPage('publication/index')
        );
    }

    public function test_tags_index_page_is_generated_when_tags_file_exists()
    {
        $this->file('tags.yml', "general:\n    - foo\n    - bar\n    - baz\n");

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertInstanceOf(
            InMemoryPage::class,
            $booted->getPage('tags/index')
        );

        $this->assertSame([
            '_pages/404.blade.php',
            '_pages/index.blade.php',
            'tags/index',
        ], $booted->getPages()->keys()->toArray());
    }

    public function test_tags_index_page_is_not_generated_when_tags_file_does_not_exist()
    {
        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertSame([
            '_pages/404.blade.php',
            '_pages/index.blade.php',
        ], $booted->getPages()->keys()->toArray());
    }

    public function test_no_tags_pages_for_publications_are_generated_when_no_publication_types_have_tag_fields()
    {
        $this->directory('publication');
        (new PublicationType('publication'))->save();
        $this->file('tags.yml', "general:\n    - foo\n    - bar\n    - baz\n");

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertSame([
            '_pages/404.blade.php',
            '_pages/index.blade.php',
            'publication/index',
            'tags/index',
        ], $booted->getPages()->keys()->toArray());
    }

    public function test_tags_pages_for_publications_are_generated_for_used_tag()
    {
        $this->directory('publication');
        (new PublicationType('publication', fields: [
            ['name' => 'general', 'type' => 'tag'],
        ]))->save();
        $this->file('publication/foo.md', "---\ngeneral: foo\n---\n");
        $this->file('tags.yml', "general:\n    - foo\n    - bar\n    - baz\n");

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
        $this->file('tags.yml', "general:\n    - foo\n    - bar\n    - baz\n");

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
        $this->file('tags.yml', "general:\n    - foo\n    - bar\n    - baz\n");

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertSame([
            '_pages/404.blade.php',
            '_pages/index.blade.php',
            'publication/foo.md',
            'publication/index',
            'tags/index',
        ], $booted->getPages()->keys()->toArray());
    }

    // TODO test contents of generated pages

    protected function createPublication(): void
    {
        $this->directory('publication');

        (new PublicationType('publication'))->save();
        (new PublicationPage('foo', [], '', PublicationType::get('publication')))->save();
    }
}
