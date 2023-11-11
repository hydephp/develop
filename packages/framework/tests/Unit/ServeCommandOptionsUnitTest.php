<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Hyde\Console\Commands\ServeCommand;

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
            'HYDE_RC_REQUEST_OUTPUT' => true,
        ], $this->getMock()->getEnvironmentVariables());
    }

    public function test_getEnvironmentVariables_withNoAnsiOption()
    {
        $this->assertSame([
            'HYDE_RC_REQUEST_OUTPUT' => false,
        ], $this->getMock(['no-ansi' => true])->getEnvironmentVariables());
    }

    public function testDashboardOptionPropagatesToEnvironmentVariables()
    {
        $command = $this->getMock(['dashboard' => 'false']);
        $this->assertSame('disabled', $command->getEnvironmentVariables()['HYDE_RC_SERVER_DASHBOARD']);

        $command = $this->getMock(['dashboard' => 'true']);
        $this->assertSame('enabled', $command->getEnvironmentVariables()['HYDE_RC_SERVER_DASHBOARD']);

        $command = $this->getMock(['dashboard' => '']);
        $this->assertSame('enabled', $command->getEnvironmentVariables()['HYDE_RC_SERVER_DASHBOARD']);

        $command = $this->getMock(['dashboard' => null]);
        $this->assertFalse(isset($command->getEnvironmentVariables()['HYDE_RC_SERVER_DASHBOARD']));

        $command = $this->getMock();
        $this->assertFalse(isset($command->getEnvironmentVariables()['HYDE_RC_SERVER_DASHBOARD']));
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

    protected function getMock(array $options = []): ServeCommandMock
    {
        return new ServeCommandMock($options);
    }
}

/**
 * @method getHostSelection
 * @method getPortSelection
 * @method getEnvironmentVariables
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
