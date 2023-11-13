<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Hyde\Hyde;
use Hyde\Markdown\Models\Markdown;
use Desilva\Microserve\JsonResponse;
use Illuminate\Support\Facades\Blade;
use Hyde\Pages\Concerns\BaseMarkdownPage;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @internal This class is not intended to be edited outside the Hyde Realtime Compiler.
 */
class LiveEditController extends BaseController
{
    protected bool $withConsoleOutput = true;
    protected bool $withSession = true;

    public function handle(): JsonResponse
    {
        try {
            $this->authorizePostRequest();

            return $this->handleRequest();
        } catch (HttpException $exception) {
            if ($this->expectsJson()) {
                return $this->sendJsonErrorResponse($exception->getStatusCode(), $exception->getMessage());
            }

            throw $exception;
        }
    }

    protected function handleRequest(): JsonResponse
    {
        $pagePath = $this->request->data['pagePath'] ?? $this->abort(400, 'Must provide page path');
        $content = $this->request->data['contentInput'] ?? $this->abort(400, 'Must provide content');

        $page = Hyde::pages()->getPage($pagePath);

        if (! $page instanceof BaseMarkdownPage) {
            $this->abort(400, 'Page is not a markdown page');
        }

        $page->markdown = new Markdown($content);
        $page->save();

        return new JsonResponse(200, 'OK', [
            'message' => 'Page saved successfully.',
        ]);
    }

    public static function enabled(): bool
    {
        return config('hyde.server.live_edit', true);
    }

    public static function injectLiveEditScript(string $html): string
    {
        return str_replace('</body>', sprintf('%s</body>', Blade::render(file_get_contents(__DIR__.'/../../resources/live-edit.blade.php'), [
            'styles' => file_get_contents(__DIR__.'/../../resources/live-edit.css'),
            'scripts' => file_get_contents(__DIR__.'/../../resources/live-edit.js'),
            'csrfToken' => self::generateCSRFToken(),
        ])), $html);
    }
}
