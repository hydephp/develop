<?php

namespace Hyde\RealtimeCompiler;

class Response
{
    /**
     * @var string
     */
    private $body;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var array
     */
    private $headers;

    /**
     * @param string $body
     * @param int $statusCode
     * @param array $headers
     */
    public function __construct($body, $statusCode, array $headers)
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->headers = $headers;

        $this->handle();
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    public function handle()
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->body;
    }
}