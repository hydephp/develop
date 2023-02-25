<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Exceptions\BuildWarning;
use Hyde\Support\BuildWarnings;
use Hyde\Testing\UnitTestCase;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Config;
use Mockery;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @covers \Hyde\Support\BuildWarnings
 * @covers \Hyde\Framework\Exceptions\BuildWarning
 */
class BuildWarningsTest extends UnitTestCase
{
    protected function tearDown(): void
    {
        BuildWarnings::getInstance()->clear();

        parent::tearDown();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf(BuildWarnings::class, BuildWarnings::getInstance());
    }

    public function testGetInstanceReturnsSingleton()
    {
        $this->assertSame(BuildWarnings::getInstance(), BuildWarnings::getInstance());
    }

    public function testHasWarnings()
    {
        $this->assertFalse(BuildWarnings::hasWarnings());
    }

    public function testGetWarnings()
    {
        $this->assertSame([], BuildWarnings::getWarnings());
    }

    public function testReport()
    {
        BuildWarnings::report('This is a warning');

        $this->assertTrue(BuildWarnings::hasWarnings());
        $this->assertEquals([new BuildWarning('This is a warning')], BuildWarnings::getWarnings());
    }

    public function testReportWithLocation()
    {
        BuildWarnings::report('This is a warning', 'path/to/file.md');

        $this->assertTrue(BuildWarnings::hasWarnings());
        $this->assertEquals([new BuildWarning('This is a warning', 'path/to/file.md')], BuildWarnings::getWarnings());
    }

    public function testReportsWarningsDefaultsToTrue()
    {
        self::mockConfig();
        $this->assertTrue(BuildWarnings::reportsWarnings());
    }

    public function testReportsWarningsReturnsTrueWhenTrue()
    {
        self::mockConfig(['hyde.log_warnings' => true]);
        $this->assertTrue(BuildWarnings::reportsWarnings());
    }

    public function testReportsWarningsReturnsFalseWhenFalse()
    {
        self::mockConfig(['hyde.log_warnings' => false]);
        $this->assertFalse(BuildWarnings::reportsWarnings());
    }

    public function testWriteWarningsToOutput()
    {
        BuildWarnings::report('This is a warning');

        $output = Mockery::mock(OutputStyle::class);
        $output->shouldReceive('writeln')->with(' 1. <comment>This is a warning</comment>');

        BuildWarnings::writeWarningsToOutput($output);

        $this->assertTrue(true);
    }

    public function testWriteWarningsToOutputWithLocation()
    {
        BuildWarnings::report('This is a warning', 'path/to/file.md');

        $output = Mockery::mock(OutputStyle::class);
        $output->shouldReceive('writeln')->with(' 1. <comment>This is a warning</comment>');
        $output->shouldReceive('writeln')->once()->withArgs(function (string $string) {
            $this->assertStringContainsString('BuildWarnings.php', $string);
            return true;
        });

        BuildWarnings::writeWarningsToOutput($output, true);

        $this->assertTrue(true);
    }

    public function testWriteWarningsToOutputWithConvertingBuildWarningsToExceptions()
    {
        self::mockConfig(['hyde.convert_build_warnings_to_exceptions' => true]);

        BuildWarnings::report('This is a warning');

        $output = Mockery::mock(OutputStyle::class);

        app()->bind(ExceptionHandler::class, function () use ($output) {
            $handler = Mockery::mock(ExceptionHandler::class);
            $handler->shouldReceive('renderForConsole')->once()->withArgs(function ($output, $warning) {
                $this->assertEquals(new BuildWarning('This is a warning'), $warning);

                return $output instanceof OutputStyle && $warning instanceof BuildWarning;
            });

            return $handler;
        });

        BuildWarnings::writeWarningsToOutput($output);
    }

    public function testAdd()
    {
        $instance = BuildWarnings::getInstance();

        $instance->add(new BuildWarning('This is a warning'));
        $this->assertTrue($instance->hasWarnings());
    }

    public function testGet()
    {
        $instance = BuildWarnings::getInstance();

        $instance->add($warning = new BuildWarning('This is a warning'));
        $this->assertSame([$warning], $instance->get());
    }

    public function testClear()
    {
        $instance = BuildWarnings::getInstance();

        $instance->add(new BuildWarning('This is a warning'));
        $instance->clear();
        $this->assertFalse($instance->hasWarnings());
    }

    protected static function mockConfig(array $items = []): void
    {
        app()->bind('config', fn () => new Repository($items));

        Config::swap(app('config'));
    }
}
