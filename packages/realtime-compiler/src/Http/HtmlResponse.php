<?php

namespace Hyde\RealtimeCompiler\Http;

use Desilva\Microserve\Response;

class HtmlResponse extends Response
{
    public function send(): void
    {
        $this->withHeaders([
            'Content-Type' => 'text/html',
            'Content-Length' => strlen($this->responseData['body']),
        ]);

        parent::send();
    }
}
