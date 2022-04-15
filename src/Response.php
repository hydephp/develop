<?php

namespace Hyde\RealtimeCompiler;

class Response
{
    /**
     * @var string
     */
    private string $body;

    /**
     * @var int
     */
    private int $statusCode;

    /**
     * @var array
     */
    private array $headers;

    /**
     * @param string $body
     * @param int $statusCode
     * @param array $headers
     */
    public function __construct(string $body, int $statusCode, array $headers)
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->headers = $headers;

        $this->handle();
    }

// --Commented out by Inspection START (2022-04-15 14:05):
//    /**
//     * @return string
//     */
//    public function getBody(): string
//    {
//        return $this->body;
//    }
// --Commented out by Inspection STOP (2022-04-15 14:05)


// --Commented out by Inspection START (2022-04-15 14:07):
//    /**
//     * @return int
//     */
//    public function getStatusCode(): int
//    {
//        return $this->statusCode;
//    }
// --Commented out by Inspection STOP (2022-04-15 14:07)


// --Commented out by Inspection START (2022-04-15 14:07):
//    /**
//     * @return array
//     */
//    public function getHeaders(): array
//    {
//        return $this->headers;
//    }
// --Commented out by Inspection STOP (2022-04-15 14:07)


    public function handle()
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->body;
    }
}