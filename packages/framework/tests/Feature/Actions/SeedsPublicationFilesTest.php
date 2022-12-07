<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\SeedsPublicationFiles;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

use PHPUnit\Framework\ExpectationFailedException;

use function explode;
use function file_get_contents;
use function str_contains;
use function str_ends_with;
use function str_replace;

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


        $this->assertFileEqualsWithWildcards(
            '---
__createdAt: ***
title: ***
---

## Write something awesome.

', $this->getPublicationFiles()[0]);


    }

    protected function getPublicationFiles(): array
    {
        return glob(Hyde::path('test-publication/*.md'));
    }

    protected function assertFileEqualsWithWildcards(string $expected, string $filepath)
    {
        $actual = file_get_contents($filepath);

        $expectedLines     = explode("\n", str_replace("\r", '', $expected));
        $actualLines       = explode("\n", str_replace("\r", '', $actual));

        try {
            $this->assertSame(count($expectedLines), count($actualLines));

            foreach ($expectedLines as $key => $expectedLine) {
                $actualLine = $actualLines[$key];
                if (str_ends_with($expectedLine, '***')) {
                    $this->assertStringStartsWith(str_replace('***', '', $expectedLine), $actualLine);
                } else {
                    $this->assertSame($expectedLine, $actualLine);
                }
            }
        } catch (ExpectationFailedException $exception) {
            // Send a more helpful message by "borrowing" the diff from the assertEquals exception.
            $this->assertEquals($expected, $actual, 'Failed asserting that the file '.basename($filepath)." is matches the expected string pattern: \n{$exception->getMessage()}");
        }
    }
}
