<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Desilva\Microserve\Response;

/**
 * @internal This class is not intended to be edited outside the Hyde Realtime Compiler.
 */
class LiveEditController extends BaseController
{
    protected bool $withConsoleOutput = true;
    protected bool $withSession = true;

    public function handle(): Response
    {
        // TODO: Implement handle() method.
    }

    public static function enabled(): bool
    {
        return config('hyde.server.live_edit', true);
    }
}
