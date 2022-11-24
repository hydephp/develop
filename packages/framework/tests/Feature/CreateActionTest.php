<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\Concerns\CreateAction;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\Concerns\CreateAction
 */
class CreateActionTest extends TestCase
{
    public function testCreate()
    {
        $action = new CreateActionTestClass;
        $action->create();

        $this->assertTrue(file_exists(Hyde::path('foo')));
        $this->assertSame('bar', file_get_contents(Hyde::path('foo')));

        unlink(Hyde::path('foo'));
    }
}

class CreateActionTestClass extends CreateAction
{
    protected string $outputPath = 'foo';

    protected function handleCreate(): void
    {
        $this->filePutContents('bar');
    }
}
