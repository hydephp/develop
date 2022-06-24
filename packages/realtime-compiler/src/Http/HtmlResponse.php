<?php

namespace Hyde\RealtimeCompiler\Http;

class HtmlResponse extends \Desilva\Microserve\Response
{
    public function send(): void
    {
        $this->withHeaders([
            'Content-Type'   => 'text/html',
            'Content-Length' => strlen($this->responseData['body']),
        ]);

        parent::send();
    }
}
