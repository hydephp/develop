<?php

namespace Hyde\RealtimeCompiler;

/**
 * Simple object oriented wrapper for sending HTTP responses.
 */
class Response
{
    /**
     * The response body to send to the client.
     * Is usually an HTML string or a media file stream.
     * @var string
     */
    private string $body;

    /**
     * The HTTP status code to send to the client.
     * @var int
     */
    private int $statusCode;

    /**
     * The HTTP headers to send to the client.
     * @var array
     */
    private array $headers;

    /**
     * Construct a new Response object instance.
     * 
     * @param string $body The response body to send to the client.
     * @param int $statusCode The HTTP status code to send to the client.
     * @param array $headers The HTTP headers to send to the client.
     */
    public function __construct(string $body, int $statusCode, array $headers)
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->headers = $headers;

        $this->handle();
    }

    /**
     * Send the response to the client.
     * @return void
     */
    public function handle(): void
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->body;
    }
}