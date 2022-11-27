<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Pages\PublicationPage;
use Hyde\Testing\TestCase;

require_once __DIR__.'/BaseHydePageUnitTest.php';

/**
 * @covers \Hyde\Pages\PublicationPage
 */
class PublicationPageUnitTest extends BaseHydePageUnitTest
{
    public function testSourceDirectory()
    {
        $this->assertSame(
            '',
            PublicationPage::sourceDirectory()
        );
    }

    public function testOutputDirectory()
    {
        $this->assertSame(
            '',
            PublicationPage::outputDirectory()
        );
    }

    public function testFileExtension()
    {
        $this->assertSame(
            '.md',
            PublicationPage::fileExtension()
        );
    }

    public function testSourcePath()
    {
        $this->assertSame(
            'hello-world.md',
            PublicationPage::sourcePath('hello-world')
        );
    }

    public function testOutputPath()
    {
        $this->assertSame(
            'hello-world.html',
            PublicationPage::outputPath('hello-world')
        );
    }

    public function testPath()
    {
        $this->assertSame(
            Hyde::path('hello-world.md'),
            PublicationPage::path('hello-world.md')
        );
    }

    public function testGetSourcePath()
    {
        $this->assertSame(
            'directory/hello-world.md',
            (new PublicationPage($this->pubType(), 'hello-world'))->getSourcePath()
        );
    }

    public function testGetOutputPath()
    {
        $this->assertSame(
            'directory/hello-world.html',
            (new PublicationPage($this->pubType(), 'hello-world'))->getOutputPath()
        );
    }

    public function testGetLink()
    {
        $this->assertSame(
            'directory/hello-world.html',
            (new PublicationPage($this->pubType(), 'hello-world'))->getLink()
        );
    }

    public function testMake()
    {
        $this->assertEquals(PublicationPage::make(type: $this->pubType()), new PublicationPage($this->pubType()));
    }

    public function testMakeWithData()
    {
        $this->assertEquals(
            PublicationPage::make('foo', ['foo' => 'bar'], type: $this->pubType()),
            new PublicationPage($this->pubType(), 'foo', ['foo' => 'bar'])
        );
    }

    public function testShowInNavigation()
    {
        $this->assertTrue((new PublicationPage($this->pubType()))->showInNavigation());
    }

    public function testNavigationMenuPriority()
    {
        $this->assertSame(999, (new PublicationPage($this->pubType()))->navigationMenuPriority());
    }

    public function testNavigationMenuLabel()
    {
        $this->assertSame('Foo', (new PublicationPage($this->pubType(), 'foo'))->navigationMenuLabel());
    }

    public function testNavigationMenuGroup()
    {
        $this->assertNull((new PublicationPage($this->pubType(), 'foo'))->navigationMenuGroup());
    }

    protected function pubType(): PublicationType
    {
        return new PublicationType(
            'name',
            'canonicalField',
            'sortField',
            'sortDirection',
            1,
            true,
            'detailTemplate',
            'listTemplate',
            [],
            'directory'
        );
    }
}
