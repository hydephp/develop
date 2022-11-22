<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\CreatesNewPublicationFile;
use Hyde\Framework\Features\Publications\Models\PublicationField;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
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

        $creator = new CreatesNewPublicationFile($pubType, $fieldData);
        $creator->create();

        $this->assertTrue(File::exists(Hyde::path('test-publication/hello-world.md')));
        $this->assertEqualsIgnoringLineEndingType('---
__createdAt: 2022-11-22 11:19:09
title: Hello World
---
Raw MD text ...
', file_get_contents(Hyde::path('test-publication/hello-world.md')));

        unlink(Hyde::path('test-publication/hello-world.md'));
    }
}
