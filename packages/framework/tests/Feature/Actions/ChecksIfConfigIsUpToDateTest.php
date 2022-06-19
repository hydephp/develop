<?php

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\ChecksIfConfigIsUpToDate;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\ChecksIfConfigIsUpToDate
 */
class ChecksIfConfigIsUpToDateTest extends TestCase
{
    public function test_it_returns_true_if_config_is_up_to_date()
    {
        $action = new ChecksIfConfigIsUpToDate();

        $action->hydeConfig = $this->makeConfig();
        $action->frameworkConfig = $this->makeConfig();

        $this->assertTrue($action->execute());
    }

    public function test_it_returns_false_if_config_is_not_up_to_date()
    {
        $action = new ChecksIfConfigIsUpToDate();

        $action->hydeConfig = $this->makeConfig();
        $action->frameworkConfig = 'foo';

        $this->assertFalse($action->execute());
    }

    protected function makeConfig(): string
    {
        return <<<'EOF'
<?php return [
    /*
    |--------------------------------------------------------------------------
    | Foo Bar
    |--------------------------------------------------------------------------
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Second Option
    |--------------------------------------------------------------------------
    |
    */
];
EOF;
    }
}
