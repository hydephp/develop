<?php

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\ChecksIfConfigIsUpToDate;
use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\ChecksIfConfigIsUpToDate
 */
class ChecksIfConfigIsUpToDateTest extends TestCase
{
    public function test_it_returns_true_if_config_is_up_to_date()
    {
        ChecksIfConfigIsUpToDate::$isUpToDate = null;

        $action = new ChecksIfConfigIsUpToDate();
        $this->assertTrue($action->execute());
    }

    public function test_it_returns_false_if_config_is_not_up_to_date()
    {
        ChecksIfConfigIsUpToDate::$isUpToDate = null;
        backup(Hyde::path('config/hyde.php'));

        file_put_contents(Hyde::path('config/hyde.php'), str_replace(
            '--------------------------------------------------------------------------',
            '', file_get_contents(
            Hyde::path('config/hyde.php')
        )));

        $action = new ChecksIfConfigIsUpToDate();
        $this->assertFalse($action->execute());

        restore(Hyde::path('config/hyde.php'));
    }
}
