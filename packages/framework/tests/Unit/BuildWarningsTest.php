<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Closure;
use Hyde\Framework\Exceptions\BuildWarning;
use Hyde\Support\BuildWarnings;
use Hyde\Testing\UnitTestCase;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Mockery;
use Symfony\Component\Console\Style\OutputStyle;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Support\BuildWarnings::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Exceptions\BuildWarning::class)]
class BuildWarningsTest extends UnitTestCase
{
    protected function tearDown(): void
    {
        app()->forgetInstance(BuildWarnings::class);
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

    public function testHasWarningWithWarnings()
    {
        BuildWarnings::report('This is a warning');

        $this->assertTrue(BuildWarnings::hasWarnings());
    }

    public function testGetWarnings()
    {
        $this->assertSame([], BuildWarnings::getWarnings());
    }

    public function testGetWarningsWithWarnings()
    {
        BuildWarnings::report('This is a warning');

        $this->assertEquals([new BuildWarning('This is a warning')], BuildWarnings::getWarnings());
    }

    public function testReport()
    {
        BuildWarnings::report('This is a warning');

        $this->assertTrue(BuildWarnings::hasWarnings());
        $this->assertEquals([new BuildWarning('This is a warning')], BuildWarnings::getWarnings());
    }

    public function testReportWithBuildWarningObject()
    {
        BuildWarnings::report($warning = new BuildWarning('This is a warning'));

        $this->assertTrue(BuildWarnings::hasWarnings());
        $this->assertSame([$warning], BuildWarnings::getWarnings());
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

    public function testReportsWarningsAsExceptionsDefaultsToFalse()
    {
        self::mockConfig();
        $this->assertFalse(BuildWarnings::reportsWarningsAsExceptions());
    }

    public function testReportsWarningsAsExceptionsReturnsTrueWhenTrue()
    {
        self::mockConfig(['hyde.convert_build_warnings_to_exceptions' => true]);
        $this->assertTrue(BuildWarnings::reportsWarningsAsExceptions());
    }

    public function testReportsWarningsAsExceptionsReturnsFalseWhenFalse()
    {
        self::mockConfig(['hyde.convert_build_warnings_to_exceptions' => false]);
        $this->assertFalse(BuildWarnings::reportsWarningsAsExceptions());
    }

    public function testWriteWarningsToOutput()
    {
        BuildWarnings::report('This is a warning');

        $output = Mockery::mock(OutputStyle::class);
        $output->shouldReceive('writeln')->once()->withArgs(
            $this->assertArgumentIs(' 1. <comment>This is a warning</comment>')
        );

        BuildWarnings::writeWarningsToOutput($output);
    }

    public function testWriteWarningsToOutputWithVerboseOutput()
    {
        BuildWarnings::report('This is a warning');

        $output = Mockery::mock(OutputStyle::class);
        $output->shouldReceive('writeln')->once()->withArgs(
            $this->assertArgumentIs(' 1. <comment>This is a warning</comment>')
        );
        $output->shouldReceive('writeln')->once()->withArgs(function (string $string) {
            $this->assertStringContainsString('BuildWarnings.php', $string);

            return true;
        });

        BuildWarnings::writeWarningsToOutput($output, true);
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

    public function testCanConstructBuildWarning()
    {
        $this->assertInstanceOf(BuildWarning::class, new BuildWarning('This is a warning'));
    }

    protected function assertArgumentIs(string $expected): Closure
    {
        return function (string $string) use ($expected) {
            $this->assertSame($expected, $string);

            return true;
        };
    }
}
