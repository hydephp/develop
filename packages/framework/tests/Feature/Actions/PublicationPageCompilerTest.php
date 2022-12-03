<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\PublicationPageCompiler;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Pages\PublicationPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\PublicationPageCompiler
 */
class PublicationPageCompilerTest extends TestCase
{
    public function testCanCompilePublicationPages()
    {
        $this->directory('test-publication');
        $this->setupTestPublication();

        file_put_contents(Hyde::path('test-publication/test-publication_detail.blade.php'), 'Detail: {{ $publication->title }}');

        $string = PublicationPageCompiler::call(new PublicationPage('my-publication', type: PublicationType::get('test-publication')));

        $this->assertEquals('Detail: My Publication', $string);
    }

    public function testCanCompilePublicationListPages()
    {
        $this->directory('test-publication');
        $this->setupTestPublication();

        file_put_contents(Hyde::path('test-publication/my-publication.md'), 'Foo');
        file_put_contents(Hyde::path('test-publication/test-publication_list.blade.php'), 'List: {{ $publications->first()->title }}');

        $string = PublicationPageCompiler::call(PublicationType::get('test-publication')->getListPage());

        $this->assertEquals('List: My Publication', $string);
    }
}
