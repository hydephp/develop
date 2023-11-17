<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Hyde\Hyde;
use Hyde\Support\Models\Route;
use Desilva\Microserve\Response;
use Hyde\Support\Models\Redirect;
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

    public function handle(): Response
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

    protected function handleRequest(): Response
    {
        $pagePath = $this->request->data['page'] ?? $this->abort(400, 'Must provide page path');
        $content = $this->request->data['markdown'] ?? $this->abort(400, 'Must provide content');

        $page = Hyde::pages()->getPage($pagePath);

        if (! $page instanceof BaseMarkdownPage) {
            $this->abort(400, 'Page is not a markdown page');
        }

        $page->markdown = new Markdown($content);
        $page->save();

        $this->writeToConsole("Updated file '$pagePath'", 'hyde@live-edit');

        if ($this->expectsJson()) {
            return new JsonResponse(200, 'OK', [
                'message' => 'Page saved successfully.',
            ]);
        }

        return $this->redirectToPage($page->getRoute());
    }

    public static function enabled(): bool
    {
        return config('hyde.server.live_edit', true);
    }

    public static function injectLiveEditScript(string $html): string
    {
        session_start();

        return str_replace('</body>', sprintf('%s</body>', Blade::render(file_get_contents(__DIR__.'/../../resources/live-edit.blade.php'), [
            'styles' => file_get_contents(__DIR__.'/../../resources/live-edit.css'),
            'scripts' => file_get_contents(__DIR__.'/../../resources/live-edit.js'),
            'csrfToken' => self::generateCSRFToken(),
        ])), $html);
    }

    protected function redirectToPage(Route $route): HtmlResponse
    {
        $redirectPage = new Redirect($this->request->path, "../$route");
        Hyde::shareViewData($redirectPage);

        return (new HtmlResponse(303, 'See Other', [
            'body' => $redirectPage->compile(),
        ]))->withHeaders([
            'Location' => $route,
        ]);
    }
}
