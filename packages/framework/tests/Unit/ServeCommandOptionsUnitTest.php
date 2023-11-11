<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\TestCase;
use Hyde\Console\Commands\ServeCommand;

/**
 * @covers \Hyde\Console\Commands\ServeCommand
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\ServeCommandTest
 */
class ServeCommandOptionsUnitTest extends TestCase
{
    public function test_getHostSelection()
    {
        $command = new ServeCommandMock();
        $this->assertSame('localhost', $command->getHostSelection());
    }

    public function test_getHostSelection_withHostOption()
    {
        $command = new ServeCommandMock(['host' => 'foo']);
        $this->assertSame('foo', $command->getHostSelection());
    }

    public function test_getHostSelection_withConfigOption()
    {
        $this->app['config']->set('hyde.server.host', 'foo');
        $command = new ServeCommandMock();
        $this->assertSame('foo', $command->getHostSelection());
    }

    public function test_getHostSelection_withHostOptionAndConfigOption()
    {
        $this->app['config']->set('hyde.server.host', 'foo');
        $command = new ServeCommandMock(['host' => 'bar']);
        $this->assertSame('bar', $command->getHostSelection());
    }

    public function test_getPortSelection()
    {
        $command = new ServeCommandMock();
        $this->assertSame(8080, $command->getPortSelection());
    }

    public function test_getPortSelection_withPortOption()
    {
        $command = new ServeCommandMock(['port' => 8081]);
        $this->assertSame(8081, $command->getPortSelection());
    }

    public function test_getPortSelection_withConfigOption()
    {
        $this->app['config']->set('hyde.server.port', 8082);
        $command = new ServeCommandMock();
        $this->assertSame(8082, $command->getPortSelection());
    }

    public function test_getPortSelection_withPortOptionAndConfigOption()
    {
        $this->app['config']->set('hyde.server.port', 8082);
        $command = new ServeCommandMock(['port' => 8081]);
        $this->assertSame(8081, $command->getPortSelection());
    }
}

/**
 * @method getHostSelection
 * @method getPortSelection
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
