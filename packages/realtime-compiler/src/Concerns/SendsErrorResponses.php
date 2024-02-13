<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Concerns;

use Desilva\Microserve\Response;

/**
 * Provides shorthands to send error responses,
 * reducing boilerplate and repeated code.
 */
trait SendsErrorResponses
{
    protected function notFound(): Response
    {
        return new Response(404, 'Not Found');
    }
}
