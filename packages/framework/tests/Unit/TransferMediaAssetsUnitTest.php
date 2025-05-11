<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Facades\Config;
use Hyde\Facades\Filesystem;
use Hyde\Foundation\HydeKernel;
use Hyde\Framework\Actions\PreBuildTasks\TransferMediaAssets;
use Hyde\Framework\Features\BuildTasks\BuildTaskSkippedException;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Testing\UnitTestCase;
use Illuminate\Console\OutputStyle;
use Mockery;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @covers \Hyde\Framework\Actions\PreBuildTasks\TransferMediaAssets
 */
class TransferMediaAssetsUnitTest extends UnitTestCase
{
    protected TransferMediaAssets $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->task = new TransferMediaAssets();
    }

    public function testItCanBeInstantiated()
    {
        $this->assertInstanceOf(TransferMediaAssets::class, $this->task);
    }

    public function testGetMessage()
    {
        $this->assertSame('Transferring Media Assets', $this->task->getMessage());
    }

    public function testSkipsWhenNoMediaFiles()
    {
        // Mock MediaFile::all to return empty collection
        HydeKernel::setInstance(Mockery::mock(new HydeKernel(), function ($mock) {
            $mock->shouldReceive('assets')->once()->andReturn(collect());
        }));

        $this->expectException(BuildTaskSkippedException::class);
        $this->expectExceptionMessage("No media files to transfer.\n");

        $this->task->handle();
    }

    public function testSkipsWhenOnlyAppCssAndUsingCdn()
    {
        // Mock Config::getBool to return true for hyde.load_app_styles_from_cdn
        self::mockConfig(['hyde.load_app_styles_from_cdn' => true]);

        // Mock MediaFile::files to return only app.css
        HydeKernel::setInstance(Mockery::mock(new HydeKernel(), function ($mock) {
            $mock->shouldReceive('assets')->once()->andReturn(collect(['app.css' => new MediaFile('app.css')]));
        }));

        $this->expectException(BuildTaskSkippedException::class);
        $this->expectExceptionMessage("No media files to transfer.\n");

        $this->task->handle();
    }

    public function testExcludesAppCssWhenUsingCdn()
    {
        // Create a collection with multiple files including app.css
        $mediaFiles = collect([
            'app.css' => new MediaFile('app.css'),
            'style.css' => new MediaFile('style.css'),
            'image.jpg' => new MediaFile('image.jpg')
        ]);

        // The collection after forgetting app.css
        $expectedFiles = collect([
            'style.css' => new MediaFile('style.css'),
            'image.jpg' => new MediaFile('image.jpg')
        ]);

        // Mock Config::getBool to return true for hyde.load_app_styles_from_cdn
        self::mockConfig(['hyde.load_app_styles_from_cdn' => true]);

        // Mock MediaFile::all to return our collection
        HydeKernel::setInstance(Mockery::mock(new HydeKernel(), function ($mock) use ($mediaFiles) {
            $mock->shouldReceive('assets')->once()->andReturn($mediaFiles);
        }));

        // Mock methods on the task to prevent actual execution but verify correct flow
        $taskMock = Mockery::mock(TransferMediaAssets::class)->makePartial();
        $taskMock->shouldReceive('withProgressBar')
            ->once()
            ->with(Mockery::on(function ($arg) use ($expectedFiles) {
                // Verify app.css is not in the collection
                return !$arg->has('app.css') && $arg->count() === 2;
            }), Mockery::type('Closure'))
            ->andReturn(null);

        $taskMock->shouldReceive('newLine')->twice();

        $taskMock->handle();
    }

    public function testTransfersMediaFiles()
    {
        // Create a collection with media files
        $file1 = new MediaFile('style.css');
        $file2 = new MediaFile('image.jpg');

        $mediaFiles = collect([
            'style.css' => $file1,
            'image.jpg' => $file2
        ]);

        // Mock Config::getBool to return false for hyde.load_app_styles_from_cdn
        self::mockConfig(['hyde.load_app_styles_from_cdn' => false]);

        // Mock MediaFile::all to return our collection
        $this->mock(MediaFile::class, function ($mock) use ($mediaFiles) {
            $mock->shouldReceive('all')->once()->andReturn($mediaFiles);
            $mock->shouldReceive('files')->never();
        });

        // Create a mock for the TransferMediaAssets class
        $taskMock = Mockery::mock(TransferMediaAssets::class)->makePartial();

        // Mock withProgressBar to execute the callback for each file
        $taskMock->shouldReceive('withProgressBar')
            ->once()
            ->with(Mockery::type('Illuminate\Support\Collection'), Mockery::type('Closure'))
            ->andReturnUsing(function ($files, $callback) {
                foreach ($files as $file) {
                    $callback($file);
                }
            });

        // Mock needsParentDirectory to prevent actual directory creation
        $taskMock->shouldReceive('needsParentDirectory')->twice();

        // Mock Filesystem::putContents to verify it's called with the right parameters
        $this->mock(Filesystem::class, function ($mock) use ($file1, $file2) {
            $mock->shouldReceive('putContents')
                ->once()
                ->with($file1->getOutputPath(), $file1->getContents());

            $mock->shouldReceive('putContents')
                ->once()
                ->with($file2->getOutputPath(), $file2->getContents());
        });

        $taskMock->shouldReceive('newLine')->twice();

        $taskMock->handle();
    }

    public function testPrintFinishMessageDoesNothing()
    {
        ob_start();
        $this->task->printFinishMessage();
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }

    public function testNeedsParentDirectoryCreatesDirectory()
    {
        // Mock Filesystem::makeDirectory to verify it's called
        $this->mock(Filesystem::class, function ($mock) {
            $mock->shouldReceive('makeDirectory')
                ->once()
                ->with('path/to', true);
        });

        // Use reflection to call protected method
        $method = new \ReflectionMethod(TransferMediaAssets::class, 'needsParentDirectory');
        $method->setAccessible(true);
        $method->invoke($this->task, 'path/to/file.txt');
    }
}