<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\Concerns\CreateAction;
use Hyde\Framework\Exceptions\FileConflictException;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use function unlinkIfExists;

/**
 * @covers \Hyde\Framework\Actions\Concerns\CreateAction
 */
class CreateActionTest extends TestCase
{
    protected function tearDown(): void
    {
        unlinkIfExists(Hyde::path('foo'));

        parent::tearDown();
    }

    public function testCreate()
    {
        $action = new CreateActionTestClass;
        $action->create();

        $this->assertTrue(file_exists(Hyde::path('foo')));
        $this->assertSame('bar', file_get_contents(Hyde::path('foo')));
    }

    public function testWithConflict()
    {
        file_put_contents(Hyde::path('foo'), 'keep');
        $this->expectException(FileConflictException::class);

        $action = new CreateActionTestClass;
        $action->create();

        $this->assertSame('keep', file_get_contents(Hyde::path('foo')));
    }

    public function testWithConflictForce()
    {
        file_put_contents(Hyde::path('foo'), 'keep');
        $action = new CreateActionTestClass;
        $action->force()->create();

        $this->assertSame('bar', file_get_contents(Hyde::path('foo')));
    }

    public function testOutputPathHelpers()
    {
        $action = new CreateActionTestClass;
        $action->setOutputPath('bar');

        $this->assertSame('bar', $action->getOutputPath());
        $this->assertSame(Hyde::path('bar'), $action->getAbsoluteOutputPath());
    }

    public function testConflictPredictionHelpers()
    {
        $action = new CreateActionTestClass;

        $this->assertFalse($action->fileExists());
        $this->assertFalse($action->fileConflicts());

        file_put_contents(Hyde::path('foo'), 'keep');
        $this->assertTrue($action->fileExists());
        $this->assertTrue($action->fileConflicts());

        $action->force();
        $this->assertFalse($action->fileConflicts());

        $action->force(false);
        $this->assertTrue($action->fileConflicts());
    }

    public function testCanSaveToSubdirectory()
    {
        $action = new CreateActionTestClass;
        $action->setOutputPath('foo/bar');
        $action->create();

        $this->assertTrue(file_exists(Hyde::path('foo/bar')));
        $this->assertSame('bar', file_get_contents(Hyde::path('foo/bar')));
        unlink(Hyde::path('foo/bar'));
        rmdir(Hyde::path('foo'));
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
