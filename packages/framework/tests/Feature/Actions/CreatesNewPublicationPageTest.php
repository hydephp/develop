<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Actions\CreatesNewPublicationPage;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Rgasch\Collection\Collection;
use RuntimeException;

/**
 * @covers \Hyde\Framework\Actions\CreatesNewPublicationPage
 */
class CreatesNewPublicationPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2022));
    }

    protected function tearDown(): void
    {
        Filesystem::deleteDirectory(('test-publication'));

        parent::tearDown();
    }

    public function testCreate()
    {
        $pubType = $this->makePublicationType();
        $fieldData = Collection::make([
            'title' => 'Hello World',
        ]);

        $creator = new CreatesNewPublicationPage($pubType, $fieldData);
        $creator->create();

        $this->assertTrue(File::exists(Hyde::path('test-publication/hello-world.md')));
        $this->assertEquals('---
__createdAt: 2022-01-01 00:00:00
title: Hello World
---

## Write something awesome.

', file_get_contents(Hyde::path('test-publication/hello-world.md')));
    }

    public function testCreateWithoutSupplyingCanonicalField()
    {
        $pubType = $this->makePublicationType();
        $fieldData = Collection::make([
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Could not find field value for 'title' which is required for as it's the type's canonical field");
        $creator = new CreatesNewPublicationPage($pubType, $fieldData);
        $creator->create();
    }

    protected function makePublicationType(array $fields = [
        [
            'type' => 'string',
            'name' => 'title',
            'min'  => 0,
            'max'  => 128,
        ],
    ]): PublicationType
    {
        return new PublicationType(
            'test',
            'title',
            fields: $fields,
            directory: 'test-publication',
        );
    }
}
