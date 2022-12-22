<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use function file_get_contents;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Actions\CreatesNewPublicationPage;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

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

    public function testWithTextType()
    {
        $pubType = $this->makePublicationType([[
            'type' => 'string',
            'name' => 'title',
        ], [
            'type' => 'text',
            'name' => 'description',
        ]]);

        $fieldData = Collection::make([
            'title' => 'Hello World',
            'description' => 'This is a description
It can be multiple lines.',
        ]);

        $creator = new CreatesNewPublicationPage($pubType, $fieldData);
        $creator->create();

        $this->assertTrue(File::exists(Hyde::path('test-publication/hello-world.md')));
        $this->assertEquals('---
__createdAt: 2022-01-01 00:00:00
title: Hello World
description: |
  This is a description
  It can be multiple lines.
---

## Write something awesome.

', file_get_contents(Hyde::path('test-publication/hello-world.md')));
    }

    public function testWithArrayType()
    {
        $pubType = $this->makePublicationType([[
            'type' => 'string',
            'name' => 'title',
        ], [
            'type' => 'array',
            'name' => 'tags',
        ]]);

        $fieldData = Collection::make([
            'title' => 'Hello World',
            'tags' => ['tag1', 'tag2'],
        ]);

        $creator = new CreatesNewPublicationPage($pubType, $fieldData);
        $creator->create();

        $this->assertTrue(File::exists(Hyde::path('test-publication/hello-world.md')));
        $this->assertEquals('---
__createdAt: 2022-01-01 00:00:00
title: Hello World
tags:
  - "tag1"
  - "tag2"
---

## Write something awesome.

', file_get_contents(Hyde::path('test-publication/hello-world.md')));
    }

    public function testCreateWithoutSupplyingCanonicalField()
    {
        $pubType = $this->makePublicationType();
        $fieldData = Collection::make([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Could not find field value for 'title' which is required for as it's the type's canonical field");
        $creator = new CreatesNewPublicationPage($pubType, $fieldData);
        $creator->create();
    }

    public function testCreateWithoutSupplyingRequiredField()
    {
        $pubType = $this->makePublicationType([[
            'type' => 'string',
            'name' => 'title',
        ], [
            'type' => 'string',
            'name' => 'slug',
        ]]);

        $fieldData = Collection::make([
            'title' => 'Hello World',
        ]);

        $creator = new CreatesNewPublicationPage($pubType, $fieldData);
        $creator->create();

        // Since the inputs are collected by the command, with the shipped code this should never happen.
        // If a developer is using the action directly, it's their responsibility to ensure the data is valid.

        $this->assertTrue(File::exists(Hyde::path('test-publication/hello-world.md')));
        $this->assertEquals('---
__createdAt: 2022-01-01 00:00:00
title: Hello World
---

## Write something awesome.

', file_get_contents(Hyde::path('test-publication/hello-world.md')));
    }

    public function testItCreatesValidYaml()
    {
        $pubType = $this->makePublicationType([[
            'type' => 'string',
            'name' => 'title',
        ], [
            'type' => 'text',
            'name' => 'description',
        ], [

            'type' => 'array',
            'name' => 'tags',
        ]]);

        $fieldData = Collection::make([
            'title' => 'Hello World',
            'description' => 'This is a description.
It can be multiple lines.',
            'tags' => ['tag1', 'tag2'],
        ]);

        $creator = new CreatesNewPublicationPage($pubType, $fieldData);
        $creator->create();

        $this->assertTrue(File::exists(Hyde::path('test-publication/hello-world.md')));
        $contents = file_get_contents(Hyde::path('test-publication/hello-world.md'));
        $this->assertEquals('---
__createdAt: 2022-01-01 00:00:00
title: Hello World
description: |
  This is a description.
  It can be multiple lines.
tags:
  - "tag1"
  - "tag2"
---

## Write something awesome.

',
                            $contents
        );

        $this->assertSame([
            '__createdAt' => 1640995200,
            'title' => 'Hello World',
            'description' => 'This is a description.
It can be multiple lines.
',
            'tags' =>  [
                'tag1',
                'tag2',
            ],
        ], Yaml::parse(Str::between($contents, '---', '---')));
    }

    protected function makePublicationType(array $fields = [
        [
            'type' => 'string',
            'name' => 'title',
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
