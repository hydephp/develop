<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Hyde;
use Hyde\Publications\Actions\PublicationPageCompiler;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\Pages\PublicationPage;
use Hyde\Support\Facades\Render;
use Hyde\Testing\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Publications\Actions\PublicationPageCompiler::class)]
class PublicationPageCompilerTest extends TestCase
{
    public function testCanCompilePublicationPages()
    {
        $this->setupPublicationType();

        file_put_contents(Hyde::path('test-publication/detail.blade.php'), 'Detail: {{ $publication->title }}');

        $string = PublicationPageCompiler::call(new PublicationPage('my-publication', type: PublicationType::get('test-publication')));

        $this->assertSame('Detail: My Publication', $string);
    }

    public function testCanCompileListPages()
    {
        $this->setupPublicationType();

        file_put_contents(Hyde::path('test-publication/my-publication.md'), 'Foo');
        file_put_contents(Hyde::path('test-publication/list.blade.php'), 'List: {{ $publicationType->getPublications()->first()->title }}');

        $string = PublicationPageCompiler::call(PublicationType::get('test-publication')->getListPage());

        $this->assertSame('List: My Publication', $string);
    }

    public function testCanCompilePublicationPagesWithRegisteredView()
    {
        $this->setupPublicationType();

        $schema = json_decode(file_get_contents(Hyde::path('test-publication/schema.json')));
        $schema->detailTemplate = 'foo';
        file_put_contents(Hyde::path('test-publication/schema.json'), json_encode($schema));
        $this->file('resources/views/foo.blade.php', 'Registered detail view');

        $publicationPage = new PublicationPage('my-publication', type: PublicationType::get('test-publication'));
        Render::setPage($publicationPage);
        $this->assertSame('Registered detail view', PublicationPageCompiler::call($publicationPage));
    }

    public function testCanCompileListPagesWithRegisteredView()
    {
        $this->setupPublicationType();

        $schema = json_decode(file_get_contents(Hyde::path('test-publication/schema.json')));
        $schema->listTemplate = 'foo';
        file_put_contents(Hyde::path('test-publication/schema.json'), json_encode($schema));
        $this->file('resources/views/foo.blade.php', 'Registered list view');

        $publicationType = PublicationType::get('test-publication');
        $publicationPage = $publicationType->getListPage();
        $this->assertSame('Registered list view', PublicationPageCompiler::call($publicationPage));
    }

    public function testCanCompilePublicationPagesWithRegisteredNamespacedView()
    {
        $this->setupPublicationType();

        $schema = json_decode(file_get_contents(Hyde::path('test-publication/schema.json')));
        $schema->detailTemplate = 'hyde-publications::detail';
        file_put_contents(Hyde::path('test-publication/schema.json'), json_encode($schema));

        $publicationPage = new PublicationPage('my-publication', type: PublicationType::get('test-publication'));
        Render::setPage($publicationPage);
        $this->assertStringContainsString('My Publication', PublicationPageCompiler::call($publicationPage));
    }

    public function testCanCompileListPagesWithRegisteredNamespacedView()
    {
        $this->setupPublicationType();
        $this->file('vendor/hyde/framework/resources/views/layouts/test.blade.php', 'Registered list view');

        $schema = json_decode(file_get_contents(Hyde::path('test-publication/schema.json')));
        $schema->listTemplate = 'hyde::layouts.test';
        file_put_contents(Hyde::path('test-publication/schema.json'), json_encode($schema));
        file_put_contents(Hyde::path('test-publication/my-publication.md'), 'Foo');

        $publicationType = PublicationType::get('test-publication');
        $publicationPage = $publicationType->getListPage();
        $this->assertSame('Registered list view', PublicationPageCompiler::call($publicationPage));
    }

    public function testWithMissingDetailBladeView()
    {
        $this->setupPublicationType();

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File [test-publication/detail.blade.php] not found.');

        PublicationPageCompiler::call(new PublicationPage('my-publication', type: PublicationType::get('test-publication')));
    }

    public function testWithMissingListBladeView()
    {
        $this->setupPublicationType();

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File [test-publication/list.blade.php] not found.');

        PublicationPageCompiler::call(PublicationType::get('test-publication')->getListPage());
    }

    protected function setupPublicationType()
    {
        $this->directory('test-publication');
        (new PublicationType('Test Publication'))->save();
    }
}
