<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\SeedsPublicationFiles;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

use function explode;
use function file_get_contents;

/**
 * @covers \Hyde\Framework\Actions\SeedsPublicationFiles
 */
class SeedsPublicationFilesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->directory('test-publication');
        $this->setupTestPublication();
    }

    public function testCreate()
    {
        $action = new SeedsPublicationFiles(PublicationType::get('test-publication'));
        $action->create();

        $this->assertFileExists($this->getPublicationFiles()[0]);
    }

    public function testCreateWithStringType()
    {
        $action = new SeedsPublicationFiles(PublicationType::get('test-publication'));
        $action->create();

        $lines = explode("\n", file_get_contents($this->getPublicationFiles()[0]));

        $this->assertSame('---', $lines[0]);
        $this->assertStringStartsWith('__createdAt: ', $lines[1]);
        $this->assertStringStartsWith('title: ', $lines[2]);
        $this->assertSame('---', $lines[3]);
        $this->assertSame('', $lines[4]);
        $this->assertSame('## Write something awesome.', $lines[5]);
        $this->assertSame('', $lines[6]);
        $this->assertSame('', $lines[7]);
    }
    protected function getPublicationFiles(): array
    {
        return glob(Hyde::path('test-publication/*.md'));
    }
}
