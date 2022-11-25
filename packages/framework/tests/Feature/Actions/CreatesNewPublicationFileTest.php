<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\CreatesNewPublicationFile;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Rgasch\Collection\Collection;

/**
 * @covers \Hyde\Framework\Actions\CreatesNewPublicationFile
 */
class CreatesNewPublicationFileTest extends TestCase
{
    public function testCreate()
    {
        $pubType = new PublicationType(
                  'test',
                  'title',
                  'sort',
                  'asc',
                  10,
                  true,
                  'detail',
                  'list',
                  [
                      [
                          'type' => 'string',
                          'name' => 'title',
                          'min'  => 0,
                          'max'  => 128,
                      ],
                  ],
                  'test-publication',
              );
        $fieldData = Collection::make([
            'title' => 'Hello World',
        ]);

        $this->freezeTime();

        $creator = new CreatesNewPublicationFile($pubType, $fieldData);
        $creator->create();

        $this->assertTrue(File::exists(Hyde::path('test-publication/hello-world.md')));
        $this->assertEqualsIgnoringLineEndingType('---
__createdAt: '.Carbon::now()->format('Y-m-d H:i:s').'
title: Hello World
---

## Write something awesome.

', file_get_contents(Hyde::path('test-publication/hello-world.md')));

        unlink(Hyde::path('test-publication/hello-world.md'));
        rmdir(Hyde::path('test-publication'));
    }
}
