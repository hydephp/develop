<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Concerns\InvokableAction;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Concerns\InvokableAction
 */
class InvokableActionTest extends TestCase
{
    public function testInvokableAction()
    {
        $action = new InvokableActionTestClass();

        $this->assertEquals('Hello World!', $action());
        $this->assertEquals('Hello World!', $action->__invoke());
        $this->assertEquals('Hello World!', $action->call());
    }

    public function testInvokableActionWithArgs()
    {
        $action = new InvokableActionTestClass('Test');

        $this->assertEquals('Hello Test!', $action());
        $this->assertEquals('Hello Test!', $action->__invoke());
        $this->assertEquals('Hello Static!', $action->call('Static'));
    }

    public function testInvokableActionStaticCall()
    {
        $this->assertEquals('Hello World!', InvokableActionTestClass::call());
    }

    public function testInvokableActionStaticCallWithArgs()
    {
        $this->assertEquals('Hello Test!', InvokableActionTestClass::call('Test'));
    }
}

class InvokableActionTestClass extends InvokableAction
{
    protected string $name;

    public function __construct(string $name = 'World')
    {
        $this->name = $name;
    }

    public function __invoke(): string
    {
        return "Hello $this->name!";
    }
}
