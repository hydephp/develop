<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Hyde;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\Pages\PublicationPage;
use Hyde\Publications\Publications;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Publications\Publications::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Publications\PublicationsExtension::class)]
class PublicationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->directory('test-publication');
    }

    public function testGetPublicationTypes()
    {
        $this->assertEquals(new Collection(), Publications::getPublicationTypes());
    }

    public function testGetPublicationTypesWithTypes()
    {
        $this->createPublicationType();
        Hyde::boot();

        $this->assertEquals(new Collection([
            'test-publication' => PublicationType::get('test-publication'),
        ]), Publications::getPublicationTypes());
    }

    public function testGetPublicationTypesMethodReturnsTheSameInstances()
    {
        $this->createPublicationType();

        $this->assertSame(Publications::getPublicationTypes(), Publications::getPublicationTypes());
    }

    public function testGetPublicationsForPubType()
    {
        $this->createPublicationType();

        $this->assertEquals(
            new Collection(),
            Publications::getPublicationsForType(PublicationType::get('test-publication'))
        );
    }

    public function testGetPublicationsForPubTypeWithPublications()
    {
        $this->createPublicationType();
        $this->createPublication();

        $this->assertEquals(
            new Collection([
                PublicationPage::parse('test-publication/foo'),
            ]),
            Publications::getPublicationsForType(PublicationType::get('test-publication'))
        );
    }

    public function testGetPublicationsForPubTypeOnlyContainsInstancesOfPublicationPage()
    {
        $this->createPublicationType();
        $this->createPublication();

        $this->assertContainsOnlyInstancesOf(
            PublicationPage::class,
            Publications::getPublicationsForType(PublicationType::get('test-publication'))
        );
    }

    public function testGetPublicationsForPubTypeSortsPublicationsBySortField()
    {
        (new PublicationType('test-publication', sortField: 'order'))->save();

        $this->markdown('test-publication/one.md', matter: ['order' => 1]);
        $this->markdown('test-publication/two.md', matter: ['order' => 2]);
        $this->markdown('test-publication/three.md', matter: ['order' => 3]);

        $this->assertEquals(
            new Collection([
                PublicationPage::parse('test-publication/one'),
                PublicationPage::parse('test-publication/two'),
                PublicationPage::parse('test-publication/three'),
            ]),
            Publications::getPublicationsForType(PublicationType::get('test-publication'))
        );
    }

    public function testGetPublicationsForPubTypeSortsPublicationsWithSpecifiedDirection()
    {
        (new PublicationType('test-publication', sortField: 'order', sortAscending: false))->save();

        $this->markdown('test-publication/one.md', matter: ['order' => 1]);
        $this->markdown('test-publication/two.md', matter: ['order' => 2]);
        $this->markdown('test-publication/three.md', matter: ['order' => 3]);

        $this->assertEquals(
            new Collection([
                PublicationPage::parse('test-publication/three'),
                PublicationPage::parse('test-publication/two'),
                PublicationPage::parse('test-publication/one'),
            ]),
            Publications::getPublicationsForType(PublicationType::get('test-publication'))
        );
    }

    public function testGetPublicationsForPubTypeSortsPublicationsByNewSortField()
    {
        (new PublicationType('test-publication'))->save();

        $this->markdown('test-publication/one.md', matter: ['readCount' => 1]);
        $this->markdown('test-publication/two.md', matter: ['readCount' => 2]);
        $this->markdown('test-publication/three.md', matter: ['readCount' => 3]);

        $this->assertEquals(
            new Collection([
                PublicationPage::parse('test-publication/one'),
                PublicationPage::parse('test-publication/two'),
                PublicationPage::parse('test-publication/three'),
            ]),
            Publications::getPublicationsForType(PublicationType::get('test-publication'), 'readCount')
        );
    }

    public function testGetPublicationsForPubTypeSortsPublicationsByNewSortFieldDescending()
    {
        (new PublicationType('test-publication', sortField: 'order'))->save();

        $this->markdown('test-publication/one.md', matter: ['readCount' => 1]);
        $this->markdown('test-publication/two.md', matter: ['readCount' => 2]);
        $this->markdown('test-publication/three.md', matter: ['readCount' => 3]);

        $this->assertEquals(
            new Collection([
                PublicationPage::parse('test-publication/three'),
                PublicationPage::parse('test-publication/two'),
                PublicationPage::parse('test-publication/one'),
            ]),
            Publications::getPublicationsForType(PublicationType::get('test-publication'), 'readCount', false)
        );
    }

    public function testGetPublicationsForPubTypeWithInvalidSortField()
    {
        (new PublicationType('test-publication', sortField: 'order'))->save();

        $this->markdown('test-publication/one.md', matter: ['readCount' => 1]);
        $this->markdown('test-publication/two.md', matter: ['readCount' => 2]);
        $this->markdown('test-publication/three.md', matter: ['readCount' => 3]);

        $this->assertEquals(
            new Collection([
                PublicationPage::parse('test-publication/one'),
                PublicationPage::parse('test-publication/three'),
                PublicationPage::parse('test-publication/two'),
            ]),
            Publications::getPublicationsForType(PublicationType::get('test-publication'), 'invalid')
        );
    }

    public function testGetMediaForPubType()
    {
        $this->createPublicationType();

        $this->assertEquals(
            new Collection(),
            Publications::getMediaForType(PublicationType::get('test-publication'))
        );
    }

    public function testGetMediaForPubTypeWithMedia()
    {
        $this->createPublicationType();
        $this->directory('_media/test-publication');
        file_put_contents(Hyde::path('_media/test-publication/image.png'), '');

        $this->assertEquals(
            new Collection([
                'test-publication/image.png',
            ]),
            Publications::getMediaForType(PublicationType::get('test-publication'))
        );
    }

    public function testGetMediaForPubTypeWithCustomMediaDirectory()
    {
        Hyde::setMediaDirectory('_assets');
        $this->createPublicationType();
        $this->directory('_assets/test-publication');
        file_put_contents(Hyde::path('_assets/test-publication/image.png'), '');

        $this->assertEquals(
            new Collection([
                'test-publication/image.png',
            ]),
            Publications::getMediaForType(PublicationType::get('test-publication'))
        );
    }

    public function testParsePublicationFile()
    {
        $this->createPublicationType();
        $this->createPublication();

        $file = PublicationPage::parse('test-publication/foo');
        $this->assertInstanceOf(PublicationPage::class, $file);
        $this->assertSame('test-publication/foo', $file->getIdentifier());
    }

    public function testParsePublicationFileWithNonExistentFile()
    {
        $this->createPublicationType();

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File [test-publication/foo.md] not found.');

        PublicationPage::parse('test-publication/foo');
    }

    public function testPublicationTypeExists()
    {
        $this->createPublicationType();

        $this->assertTrue(Publications::publicationTypeExists('test-publication'));
        $this->assertFalse(Publications::publicationTypeExists('foo'));
    }

    public function testCanGetTagsUsedInPublications()
    {
        $type = new PublicationType('test-publication', fields: [[
            'name' => 'tag',
            'type' => 'tag',
        ]]);

        $page = new PublicationPage(matter: [
            'tag' => ['foo', 'bar'],
        ], type: $type);

        Hyde::kernel()->pages()->addPage($page);

        $this->assertSame(['foo', 'bar'], Publications::getPublicationTags());
    }

    public function testMultipleOccurringTagsAreAggregatedUniquely()
    {
        $type = new PublicationType('test-publication', fields: [[
            'name' => 'tag',
            'type' => 'tag',
        ]]);

        Hyde::kernel()->pages()->addPage(new PublicationPage('1', [
            'tag' => ['foo', 'bar'],
        ], type: $type));

        Hyde::kernel()->pages()->addPage(new PublicationPage('2', [
            'tag' => ['foo', 'baz'],
        ], type: $type));

        $this->assertSame(['foo', 'bar', 'baz'], Publications::getPublicationTags());
    }

    public function testAllTagsMethodFindsBothArrayAndSingleTagValues()
    {
        $type = new PublicationType('test-publication', fields: [[
            'name' => 'tag',
            'type' => 'tag',
        ]]);

        Hyde::kernel()->pages()->addPage(new PublicationPage('1', [
            'tag' => 'foo',
        ], type: $type));

        Hyde::kernel()->pages()->addPage(new PublicationPage('2', [
            'tag' => ['bar', 'baz'],
        ], type: $type));

        $this->assertSame(['foo', 'bar', 'baz'], Publications::getPublicationTags());
    }

    public function testAllTagsMethodReturnsEmptyArrayWhenThereAreNoTagsUsed()
    {
        $this->assertSame([], Publications::getPublicationTags());
    }

    protected function createPublicationType(): void
    {
        (new PublicationType('test-publication'))->save();
    }

    protected function createPublication(): void
    {
        file_put_contents(
            Hyde::path('test-publication/foo.md'),
            "---\n__canonical: canonical\n__createdAt: 2022-11-16 11:32:52\nfoo: bar\n---\n\nHello World!\n"
        );
    }
}
