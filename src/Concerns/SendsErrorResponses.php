<?php

namespace Hyde\RealtimeCompiler\Concerns;

use Desilva\Microserve\Response;

trait SendsErrorResponses
{
    protected function notFound(): Response
    {
        return new Response(404, 'Not Found');
    }
}