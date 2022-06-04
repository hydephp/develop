<?php

namespace Hyde\RealtimeCompiler\Routing;

use Desilva\Microserve\Request;
use Hyde\RealtimeCompiler\Concerns\SendsErrorResponses;

class PageRouter
{
    use SendsErrorResponses;

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function handle(Request $request): static
    {
        return new static($request);
    }
}