<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Publications\Validation\BooleanRule;
use Hyde\Testing\TestCase;
use Illuminate\Contracts\Validation\Factory;

/**
 * @covers \Hyde\Publications\Validation\BooleanRule
 */
class BooleanRuleTest extends TestCase
{
    public function testValidatePasses()
    {
        $validator = $this->app[Factory::class];

        $this->assertTrue($validator->make(['foo' => 'true'], ['foo' => new BooleanRule])->passes());
        $this->assertTrue($validator->make(['foo' => 'false'], ['foo' => new BooleanRule])->passes());
        $this->assertTrue($validator->make(['foo' => true], ['foo' => new BooleanRule])->passes());
        $this->assertTrue($validator->make(['foo' => false], ['foo' => new BooleanRule])->passes());
        $this->assertTrue($validator->make(['foo' => '0'], ['foo' => new BooleanRule])->passes());
        $this->assertTrue($validator->make(['foo' => '1'], ['foo' => new BooleanRule])->passes());
        $this->assertTrue($validator->make(['foo' => 0], ['foo' => new BooleanRule])->passes());
        $this->assertTrue($validator->make(['foo' => 1], ['foo' => new BooleanRule])->passes());
    }

    public function testValidateFails()
    {
        $validator = $this->app[Factory::class];

        $this->assertTrue($validator->make(['foo' => 'foo'], ['foo' => new BooleanRule])->fails());
        $this->assertTrue($validator->make(['foo' => 'bar'], ['foo' => new BooleanRule])->fails());
        $this->assertTrue($validator->make(['foo' => null], ['foo' => new BooleanRule])->fails());
        $this->assertTrue($validator->make(['foo' => 2], ['foo' => new BooleanRule])->fails());
    }
}
