<?php

namespace Hyde\RealtimeCompiler\Routing;

use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\RealtimeCompiler\Concerns\SendsErrorResponses;

class PageRouter
{
    use SendsErrorResponses;

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function handlePageRequest(): Response
    {
        return new Response(200, 'OK', [
            'body' => 'Hello World!',
        ]);
    }

    public static function handle(Request $request): Response
    {
        return (new static($request))->handlePageRequest();
    }
}