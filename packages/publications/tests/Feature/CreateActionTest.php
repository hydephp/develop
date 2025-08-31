<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Exceptions\FileConflictException;
use Hyde\Hyde;
use Hyde\Publications\Actions\CreateAction;
use Hyde\Testing\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Publications\Actions\CreateAction::class)]
class CreateActionTest extends TestCase
{
    public function testCreate()
    {
        $action = new CreateActionTestClass;
        $action->create();

        $this->assertFileExists(Hyde::path('foo'));
        $this->assertSame('bar', file_get_contents(Hyde::path('foo')));

        Filesystem::unlink('foo');
    }

    public function testWithConflict()
    {
        file_put_contents(Hyde::path('foo'), 'keep');
        $this->expectException(FileConflictException::class);

        $action = new CreateActionTestClass;
        $action->create();

        $this->assertSame('keep', file_get_contents(Hyde::path('foo')));

        Filesystem::unlink('foo');
    }

    public function testWithConflictForce()
    {
        file_put_contents(Hyde::path('foo'), 'keep');
        $action = new CreateActionTestClass;
        $action->force()->create();

        $this->assertSame('bar', file_get_contents(Hyde::path('foo')));

        Filesystem::unlink('foo');
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
        $this->assertFalse($action->hasFileConflict());

        file_put_contents(Hyde::path('foo'), 'keep');
        $this->assertTrue($action->fileExists());
        $this->assertTrue($action->hasFileConflict());

        $action->force();
        $this->assertFalse($action->hasFileConflict());

        $action->force(false);
        $this->assertTrue($action->hasFileConflict());

        Filesystem::unlink('foo');
    }

    public function testCanSaveToSubdirectory()
    {
        $action = new CreateActionTestClass;
        $action->setOutputPath('foo/bar');
        $action->create();

        $this->assertFileExists(Hyde::path('foo/bar'));
        $this->assertSame('bar', file_get_contents(Hyde::path('foo/bar')));
        unlink(Hyde::path('foo/bar'));
        rmdir(Hyde::path('foo'));
    }

    public function testFormatStringForStorage()
    {
        $action = new CreateActionTestClass;
        $this->assertSame('hello-world', $action->getFormattedNameForStorage());
    }
}

class CreateActionTestClass extends CreateAction
{
    protected string $outputPath = 'foo';

    protected function handleCreate(): void
    {
        $this->save('bar');
    }

    public function getFormattedNameForStorage(): string
    {
        return $this->formatStringForStorage('Hello World!');
    }
}
