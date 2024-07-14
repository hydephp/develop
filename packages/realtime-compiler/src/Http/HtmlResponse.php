<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Desilva\Microserve\Response;

class HtmlResponse extends Response
{
    public function send(): static
    {
        $this->withHeaders([
            'Content-Type' => 'text/html',
            'Content-Length' => strlen($this->responseData['body']),
        ]);

        return parent::send();
    }
}
