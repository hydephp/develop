<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\Concerns\CreateAction;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\Concerns\CreateAction
 */
class CreateActionTest extends TestCase
{
    //
}

class CreateActionTestClass extends CreateAction
{
    protected string $outputPath = 'foo';

    protected function handleCreate(): void
    {
        //
    }
}
