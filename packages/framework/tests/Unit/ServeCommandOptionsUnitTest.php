<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Mockery;
use Hyde\Testing\UnitTestCase;
use Illuminate\Console\OutputStyle;
use Hyde\Console\Commands\ServeCommand;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;

/**
 * @covers \Hyde\Console\Commands\ServeCommand
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\ServeCommandTest
 */
class ServeCommandOptionsUnitTest extends UnitTestCase
{
    protected function setUp(): void
    {
        self::mockConfig([
            'hyde.server.host' => 'localhost',
            'hyde.server.port' => 8080,
        ]);
    }

    public function test_getHostSelection()
    {
        $this->assertSame('localhost', $this->getMock()->getHostSelection());
    }

    public function test_getHostSelection_withHostOption()
    {
        $this->assertSame('foo', $this->getMock(['host' => 'foo'])->getHostSelection());
    }

    public function test_getHostSelection_withConfigOption()
    {
        self::mockConfig(['hyde.server.host' => 'foo']);
        $this->assertSame('foo', $this->getMock()->getHostSelection());
    }

    public function test_getHostSelection_withHostOptionAndConfigOption()
    {
        self::mockConfig(['hyde.server.host' => 'foo']);
        $this->assertSame('bar', $this->getMock(['host' => 'bar'])->getHostSelection());
    }

    public function test_getPortSelection()
    {
        $this->assertSame(8080, $this->getMock()->getPortSelection());
    }

    public function test_getPortSelection_withPortOption()
    {
        $this->assertSame(8081, $this->getMock(['port' => 8081])->getPortSelection());
    }

    public function test_getPortSelection_withConfigOption()
    {
        self::mockConfig(['hyde.server.port' => 8082]);
        $this->assertSame(8082, $this->getMock()->getPortSelection());
    }

    public function test_getPortSelection_withPortOptionAndConfigOption()
    {
        self::mockConfig(['hyde.server.port' => 8082]);
        $this->assertSame(8081, $this->getMock(['port' => 8081])->getPortSelection());
    }

    public function test_getEnvironmentVariables()
    {
        $this->assertSame([
            'HYDE_SERVER_REQUEST_OUTPUT' => true,
        ], $this->getMock()->getEnvironmentVariables());
    }

    public function test_getEnvironmentVariables_withNoAnsiOption()
    {
        $this->assertSame([
            'HYDE_SERVER_REQUEST_OUTPUT' => false,
        ], $this->getMock(['no-ansi' => true])->getEnvironmentVariables());
    }

    public function testSavePreviewOptionPropagatesToEnvironmentVariables()
    {
        $command = $this->getMock(['save-preview' => 'false']);
        $this->assertSame('disabled', $command->getEnvironmentVariables()['HYDE_SERVER_SAVE_PREVIEW']);

        $command = $this->getMock(['save-preview' => 'true']);
        $this->assertSame('enabled', $command->getEnvironmentVariables()['HYDE_SERVER_SAVE_PREVIEW']);

        $command = $this->getMock(['save-preview' => '']);
        $this->assertSame('enabled', $command->getEnvironmentVariables()['HYDE_SERVER_SAVE_PREVIEW']);

        $command = $this->getMock(['save-preview' => null]);
        $this->assertFalse(isset($command->getEnvironmentVariables()['HYDE_SERVER_SAVE_PREVIEW']));

        $command = $this->getMock();
        $this->assertFalse(isset($command->getEnvironmentVariables()['HYDE_SERVER_SAVE_PREVIEW']));
    }

    public function testDashboardOptionPropagatesToEnvironmentVariables()
    {
        $command = $this->getMock(['dashboard' => 'false']);
        $this->assertSame('disabled', $command->getEnvironmentVariables()['HYDE_SERVER_DASHBOARD']);

        $command = $this->getMock(['dashboard' => 'true']);
        $this->assertSame('enabled', $command->getEnvironmentVariables()['HYDE_SERVER_DASHBOARD']);

        $command = $this->getMock(['dashboard' => '']);
        $this->assertSame('enabled', $command->getEnvironmentVariables()['HYDE_SERVER_DASHBOARD']);

        $command = $this->getMock(['dashboard' => null]);
        $this->assertFalse(isset($command->getEnvironmentVariables()['HYDE_SERVER_DASHBOARD']));

        $command = $this->getMock();
        $this->assertFalse(isset($command->getEnvironmentVariables()['HYDE_SERVER_DASHBOARD']));
    }

    public function testPrettyUrlsOptionPropagatesToEnvironmentVariables()
    {
        $command = $this->getMock(['pretty-urls' => 'false']);
        $this->assertSame('disabled', $command->getEnvironmentVariables()['HYDE_PRETTY_URLS']);

        $command = $this->getMock(['pretty-urls' => 'true']);
        $this->assertSame('enabled', $command->getEnvironmentVariables()['HYDE_PRETTY_URLS']);

        $command = $this->getMock(['pretty-urls' => '']);
        $this->assertSame('enabled', $command->getEnvironmentVariables()['HYDE_PRETTY_URLS']);

        $command = $this->getMock(['pretty-urls' => null]);
        $this->assertFalse(isset($command->getEnvironmentVariables()['HYDE_PRETTY_URLS']));

        $command = $this->getMock();
        $this->assertFalse(isset($command->getEnvironmentVariables()['HYDE_PRETTY_URLS']));
    }

    public function testPlayCdnOptionPropagatesToEnvironmentVariables()
    {
        $command = $this->getMock(['play-cdn' => 'false']);
        $this->assertSame('disabled', $command->getEnvironmentVariables()['HYDE_PLAY_CDN']);

        $command = $this->getMock(['play-cdn' => 'true']);
        $this->assertSame('enabled', $command->getEnvironmentVariables()['HYDE_PLAY_CDN']);

        $command = $this->getMock(['play-cdn' => '']);
        $this->assertSame('enabled', $command->getEnvironmentVariables()['HYDE_PLAY_CDN']);

        $command = $this->getMock(['play-cdn' => null]);
        $this->assertFalse(isset($command->getEnvironmentVariables()['HYDE_PLAY_CDN']));

        $command = $this->getMock();
        $this->assertFalse(isset($command->getEnvironmentVariables()['HYDE_PLAY_CDN']));
    }

    public function test_parseEnvironmentOption()
    {
        $command = $this->getMock(['foo' => 'true']);
        $this->assertSame('enabled', $command->parseEnvironmentOption('foo'));

        $command = $this->getMock(['foo' => 'false']);
        $this->assertSame('disabled', $command->parseEnvironmentOption('foo'));
    }

    public function test_parseEnvironmentOption_withEmptyString()
    {
        $command = $this->getMock(['foo' => '']);
        $this->assertSame('enabled', $command->parseEnvironmentOption('foo'));
    }

    public function test_parseEnvironmentOption_withNull()
    {
        $command = $this->getMock(['foo' => null]);
        $this->assertNull($command->parseEnvironmentOption('foo'));
    }

    public function test_parseEnvironmentOption_withInvalidValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid boolean value for --foo option.');

        $command = $this->getMock(['foo' => 'bar']);
        $command->parseEnvironmentOption('foo');
    }

    public function test_checkArgvForOption()
    {
        $serverBackup = $_SERVER;

        $_SERVER['argv'] = ['--pretty-urls'];

        $command = $this->getMock();

        $this->assertSame('true', $command->checkArgvForOption('pretty-urls'));
        $this->assertSame(null, $command->checkArgvForOption('dashboard'));

        $_SERVER = $serverBackup;
    }

    public function testWithOpenArgument()
    {
        $output = $this->createMock(OutputStyle::class);
        $output->expects($this->never())->method('writeln');

        $command = $this->getMock(['--open' => true]);
        $command->setOutput($output);

        $binary = match (PHP_OS_FAMILY) {
            'Darwin' => 'open',
            'Windows' => 'start',
            default => 'xdg-open',
        };

        Process::shouldReceive('command')->once()->with("$binary http://localhost:8080")->andReturnSelf();
        Process::shouldReceive('run')->once()->andReturnSelf();
        Process::shouldReceive('failed')->once()->andReturn(false);

        $command->openInBrowser();
    }

    public function testWithOpenArgumentThatFails()
    {
        $output = Mockery::mock(OutputStyle::class);
        $output->shouldReceive('getFormatter')->andReturn($this->createMock(OutputFormatterInterface::class));

        $warning = '<warning>Unable to open the site preview in the browser on your system:</warning>';
        $context = '  Missing suitable \'open\' binary.';

        $output->shouldReceive('writeln')->once()->with($warning, OutputInterface::VERBOSITY_NORMAL);
        $output->shouldReceive('writeln')->once()->with($context, OutputInterface::VERBOSITY_NORMAL);
        $output->shouldReceive('newLine')->once();

        $command = $this->getMock(['--open' => true]);
        $command->setOutput($output);

        $binary = match (PHP_OS_FAMILY) {
            'Darwin' => 'open',
            'Windows' => 'start',
            default => 'xdg-open',
        };

        Process::shouldReceive('command')->once()->with("$binary http://localhost:8080")->andReturnSelf();
        Process::shouldReceive('run')->once()->andReturnSelf();
        Process::shouldReceive('failed')->once()->andReturn(true);
        Process::shouldReceive('errorOutput')->once()->andReturn("Missing suitable 'open' binary.");

        $command->openInBrowser();

        Mockery::close();

        $this->assertTrue(true);
    }

    protected function getMock(array $options = []): ServeCommandMock
    {
        return new ServeCommandMock($options);
    }
}

/**
 * @method getHostSelection
 * @method getPortSelection
 * @method getEnvironmentVariables
 * @method parseEnvironmentOption(string $name)
 * @method checkArgvForOption(string $name)
 * @method openInBrowser()
 */
class ServeCommandMock extends ServeCommand
{
    public function __construct(array $options = [])
    {
        parent::__construct();

        $this->input = new InputMock($options);
    }

    public function __call($method, $parameters)
    {
        return call_user_func_array([$this, $method], $parameters);
    }

    public function option($key = null)
    {
        return $this->input->getOption($key);
    }
}

class InputMock
{
    protected array $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function getOption(string $key)
    {
        return $this->options[$key] ?? null;
    }
}
