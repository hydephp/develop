<?php

namespace Hyde\RealtimeCompiler\Tests\Integration;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class TestResponse
{
    protected ResponseInterface $response;
    protected IntegrationTestCase $test;

    protected string $html;
    protected string $text;

    public static function get(IntegrationTestCase $test, string $uri): static
    {
        $guzzle = new Client();

        $response = $guzzle->get('http://localhost:8080'.$uri, ['http_errors' => false]);

        return new static($test, $response);
    }

    public function __construct(IntegrationTestCase $test, ResponseInterface $response)
    {
        $this->test = $test;
        $this->response = $response;

        $this->parsePageData();
    }

    protected function parsePageData(): void
    {
        $this->html = $this->response->getBody()->getContents();
        $this->text = $this->extractText($this->html);
    }

    protected function extractText(string $html): string
    {
        // Strip script and style tags
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);

        $html = strip_tags($html);

        $html = implode("\n", array_map('trim', explode("\n", $html)));

        // Remove double spaces and double newlines
        $html = preg_replace('/\n{2,}/', "\n", $html);
        $html = preg_replace('/\s{2,}/', ' ', $html);

        return trim($html);
    }

    public function dd(): void
    {
        dd($this->html);
    }

    public function ddText(): void
    {
        dd($this->text);
    }

    public function ddHeaders(): void
    {
        dd($this->response->getHeaders());
    }

    public function assertStatus(int $code): static
    {
        $this->test->assertSame($code, $this->response->getStatusCode());

        return $this;
    }

    public function assertSeeText(string $text): static
    {
        $this->test->assertStringContainsString($text, $this->text);

        return $this;
    }

    public function assertHeader(string $header, string $value): static
    {
        $this->test->assertArrayHasKey($header, $this->response->getHeaders());
        $this->test->assertContains($value, $this->response->getHeader($header));

        return $this;
    }

    public function assertJson(array $data): static
    {
        $this->test->assertJson($this->html);
        $this->test->assertSame($data, json_decode($this->html, true));

        return $this;
    }
}
