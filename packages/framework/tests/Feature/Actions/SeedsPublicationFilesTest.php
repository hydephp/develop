<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use function explode;
use function file_get_contents;
use Hyde\Framework\Actions\SeedsPublicationFiles;
use Hyde\Framework\Features\Publications\Models\PublicationFieldType;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use PHPUnit\Framework\ExpectationFailedException;
use function str_ends_with;
use function str_replace;

/**
 * @covers \Hyde\Framework\Actions\SeedsPublicationFiles
 */
class SeedsPublicationFilesTest extends TestCase
{
    protected PublicationType $pubType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory('test-publication');
        $this->setupTestPublication();
        $this->pubType = PublicationType::get('test-publication');
    }

    public function testCreate()
    {
        $action = new SeedsPublicationFiles($this->pubType);
        $action->create();

        $this->assertFileExists($this->firstPublicationFilePath());
    }

    public function testWithStringType()
    {
        $this->updateSchema('string', 'title');
        (new SeedsPublicationFiles($this->pubType))->create();

        $this->assertFileMatchesString(
            '---
__createdAt: ***
title: ***
---

## Write something awesome.

', $this->firstPublicationFilePath()
        );
    }

    public function testWithArrayType()
    {
        $this->updateSchema('array', 'tags');
        (new SeedsPublicationFiles($this->pubType))->create();

        $this->assertFileMatchesString(
            '---
__createdAt: ***
tags:
  - ***
  - ***
  - ***
', $this->firstPublicationFilePath(), 5);
    }

    public function testWithBooleanType()
    {
        $this->updateSchema('boolean', 'published');
        (new SeedsPublicationFiles($this->pubType))->create();

        $this->assertFileMatchesString(
            '---
__createdAt: ***
published: ***
---

## Write something awesome.

', $this->firstPublicationFilePath());
    }

    protected function getPublicationFiles(): array
    {
        $files = glob(Hyde::path('test-publication/*.md'));
        $this->assertNotEmpty($files, 'No publication files found.');

        return $files;
    }

    protected function firstPublicationFilePath(): string
    {
        return $this->getPublicationFiles()[0];
    }

    protected function assertFileMatchesString(string $expected, string $filepath, ?int $limit = null)
    {
        $actual = file_get_contents($filepath);

        $expectedLines = explode("\n", str_replace("\r", '', $expected));
        $actualLines = explode("\n", str_replace("\r", '', $actual));

        try {
            foreach ($expectedLines as $key => $expectedLine) {
                $actualLine = $actualLines[$key];
                if (str_ends_with($expectedLine, '***')) {
                    $this->assertStringStartsWith(str_replace('***', '', $expectedLine), $actualLine);
                } else {
                    $this->assertSame($expectedLine, $actualLine);
                }

                if ($limit && $key >= $limit) {
                    break;
                }
            }
        } catch (ExpectationFailedException $exception) {
            // Send a more helpful message by "borrowing" the diff from the assertEquals exception.
            $this->assertEquals($expected, $actual, 'Failed asserting that the file '.basename($filepath)." is matches the expected string pattern: \n{$exception->getMessage()}");
        }

        if (! $limit) {
            $this->assertSame(count($expectedLines), count($actualLines));
        }
    }

    protected function updateSchema(string $type, string $name, int|string|null $min = 0, int|string|null $max = 0): void
    {
        $this->pubType->fields = [
            (new PublicationFieldType($type, $name, $min, $max))->toArray(),
        ];
        $this->pubType->save();
    }
}
