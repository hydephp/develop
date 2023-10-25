<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Hyde\Hyde;
use OutOfBoundsException;
use Hyde\Pages\BladePage;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\RouteKey;
use Illuminate\Support\HtmlString;
use Hyde\Foundation\Facades\Routes;
use Desilva\Microserve\JsonResponse;
use Hyde\Support\Filesystem\MediaFile;
use Illuminate\Support\Facades\Process;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Framework\Actions\AnonymousViewCompiler;
use Desilva\Microserve\Request;
use Composer\InstalledVersions;
use Hyde\Framework\Actions\CreatesNewPageSourceFile;
use Hyde\Framework\Exceptions\FileConflictException;
use Hyde\Framework\Actions\CreatesNewMarkdownPostFile;
use Symfony\Component\HttpKernel\Exception\HttpException;

use function time;
use function round;
use function basename;
use function in_array;
use function json_decode;
use function json_encode;
use function array_combine;
use function escapeshellarg;
use function file_get_contents;
use function str_starts_with;
use function str_replace;
use function array_merge;
use function sprintf;
use function config;
use function app;

class DashboardController
{
    public string $title;

    protected Request $request;
    protected bool $isAsync = false;

    protected array $flashes = [];

    protected static array $tips = [
        'This dashboard won\'t be saved to your static site.',
        'Got stuck? Ask for help on [GitHub](https://github.com/hydephp/hyde)!',
        'Found a bug? Please report it on [GitHub](https://github.com/hydephp/hyde)!',
        'You can disable tips using by setting `server.dashboard_tips` to `false` in `config/hyde.php`.',
        'The dashboard update your project files. You can disable this by setting `server.dashboard_editor` to `false` in `config/hyde.php`.',
    ];

    public function __construct()
    {
        $this->title = config('hyde.name').' - Dashboard';
        $this->request = Request::capture();

        $this->loadFlashData();

        if ($this->request->method === 'POST') {
            $this->isAsync = (getallheaders()['X-RC-Handler'] ?? getallheaders()['x-rc-handler'] ?? null) === 'Async';

            if (! $this->enableEditor()) {
                $this->abort(403, 'Enable `server.editor` in `config/hyde.php` to use interactive dashboard features.');
            }

            try {
                $this->handlePostRequest();
            } catch (HttpException $exception) {
                if (! $this->isAsync) {
                    throw $exception;
                }

                $this->sendJsonErrorResponse($exception);
            }
        }
    }

    protected function handlePostRequest(): void
    {
        $actions = array_combine($actions = [
            'openInExplorer',
            'openPageInEditor',
            'openMediaFileInEditor',
            'createPage',
        ], $actions);

        $action = $this->request->data['action'] ?? $this->abort(400, 'Must provide action');
        $action = $actions[$action] ?? $this->abort(403, "Invalid action '$action'");

        if ($action === 'openInExplorer') {
            $this->openInExplorer();
        }

        if ($action === 'openPageInEditor') {
            $routeKey = $this->request->data['routeKey'] ?? $this->abort(400, 'Must provide routeKey');
            $page = Routes::getOrFail($routeKey)->getPage();
            $this->openPageInEditor($page);
        }

        if ($action === 'openMediaFileInEditor') {
            $identifier = $this->request->data['identifier'] ?? $this->abort(400, 'Must provide identifier');
            $asset = @MediaFile::all()[$identifier] ?? $this->abort(404, "Invalid media identifier '$identifier'");
            $this->openMediaFileInEditor($asset);
        }

        if ($action === 'createPage') {
            $this->createPage();
        }
    }

    public function show(): string
    {
        return AnonymousViewCompiler::handle(__DIR__.'/../../resources/dashboard.blade.php', array_merge(
            (array) $this, ['dashboard' => $this, 'request' => $this->request],
        ));
    }

    public function getVersion(): string
    {
        $version = InstalledVersions::getPrettyVersion('hyde/realtime-compiler');

        return str_starts_with($version, 'dev-') ? $version : "v$version";
    }

    public function getProjectInformation(): array
    {
        return [
            'Git Version:' => app('git.version'),
            'Hyde Version:' => self::getPackageVersion('hyde/hyde'),
            'Framework Version:' => self::getPackageVersion('hyde/framework'),
            'Project Path:' => Hyde::path(),
        ];
    }

    /** @return array<string, \Hyde\Support\Models\Route> */
    public function getPageList(): array
    {
        return Hyde::routes()->all();
    }

    /** @internal */
    public static function bytesToHuman(int $bytes, int $precision = 2): string
    {
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.['B', 'KB', 'MB', 'GB', 'TB'][$i];
    }

    public function showTips(): bool
    {
        return config('hyde.server.tips', true);
    }

    public function getTip(): HtmlString
    {
        return new HtmlString(Str::inlineMarkdown(Arr::random(static::$tips)));
    }

    public static function enabled(): bool
    {
        return config('hyde.server.dashboard', true);
    }

    // This method is called from the PageRouter and allows us to serve a dynamic welcome page
    public static function renderIndexPage(HydePage $page): string
    {
        if (config('hyde.server.save_preview')) {
            $contents = file_get_contents(StaticPageBuilder::handle($page));
        } else {
            Hyde::shareViewData($page);

            $contents = $page->compile();
        }

        // If the page is the default welcome page we inject dashboard components
        if (str_contains($contents, 'This is the default homepage')) {
            if (config('hyde.server.dashboard.welcome-banner', true)) {
                $contents = str_replace("</div>\n            <!-- End Main Hero Content -->",
                    sprintf("%s\n</div>\n<!-- End Main Hero Content -->", self::welcomeComponent()),
                    $contents);
            }

            if (config('hyde.server.dashboard.welcome-dashboard', true)) {
                $contents = str_replace('</body>', sprintf("%s\n</body>", self::welcomeFrame()), $contents);
            }

            if (config('hyde.server.dashboard.button', true)) {
                $contents = self::injectDashboardButton($contents);
            }
        }

        return $contents;
    }

    public function enableEditor(): bool
    {
        return config('hyde.server.editor', true);
    }

    public function getScripts(): string
    {
        return file_get_contents(__DIR__.'/../../resources/dashboard.js');
    }

    public function getFlash(string $key, $default = null): ?string
    {
        return $this->flashes[$key] ?? $default;
    }

    protected function flash(string $string, string $value): void
    {
        setcookie('hyde-rc-flash', json_encode([$string => $value]), time() + 180, '/') ?: $this->abort(500, 'Failed to flash session cookie');
    }

    protected function loadFlashData(): void
    {
        if ($flashData = $_COOKIE['hyde-rc-flash'] ?? null) {
            $this->flashes = json_decode($flashData, true);
            setcookie('hyde-rc-flash', ''); // Clear cookie
        }
    }

    protected function openInExplorer(): void
    {
        if ($this->enableEditor()) {
            $binary = $this->findGeneralOpenBinary();
            $path = Hyde::path();

            Process::run(sprintf('%s %s', $binary, escapeshellarg($path)))->throw();
        }
    }

    protected function openPageInEditor(HydePage $page): void
    {
        if ($this->enableEditor()) {
            $binary = $this->findGeneralOpenBinary();
            $path = Hyde::path($page->getSourcePath());

            if (! (str_ends_with($path, '.md') || str_ends_with($path, '.blade.php'))) {
                $this->abort(403, sprintf("Refusing to open unsafe file '%s'", basename($path)));
            }

            Process::run(sprintf('%s %s', $binary, escapeshellarg($path)))->throw();
        }
    }

    protected function openMediaFileInEditor(MediaFile $file): void
    {
        if ($this->enableEditor()) {
            $binary = $this->findGeneralOpenBinary();
            $path = $file->getAbsolutePath();

            if (! in_array($file->getExtension(), ['png', 'svg', 'jpg', 'jpeg', 'gif', 'ico'])) {
                $this->abort(403, sprintf("Refusing to open unsafe file '%s'", basename($path)));
            }

            Process::run(sprintf('%s %s', $binary, escapeshellarg($path)))->throw();
        }
    }

    protected function createPage(): void
    {
        if ($this->enableEditor()) {
            // Required data
            $title = $this->request->data['titleInput'] ?? $this->abort(400, 'Must provide title');
            $content = $this->request->data['contentInput'] ?? $this->abort(400, 'Must provide content');
            $pageType = $this->request->data['pageTypeSelection'] ?? $this->abort(400, 'Must provide page type');

            // Optional data
            $postDescription = $this->request->data['postDescription'] ?? null;
            $postCategory = $this->request->data['postCategory'] ?? null;
            $postAuthor = $this->request->data['postAuthor'] ?? null;
            $postDate = $this->request->data['postDate'] ?? null;

            // Match page class
            $pageClass = match ($pageType) {
                'blade-page' => BladePage::class,
                'markdown-page' => MarkdownPage::class,
                'markdown-post' => MarkdownPost::class,
                'documentation-page' => DocumentationPage::class,
                default => throw new HttpException(400, "Invalid page type '$pageType'"),
            };

            if ($pageClass === MarkdownPost::class) {
                $creator = new CreatesNewMarkdownPostFile($title, $postDescription, $postCategory, $postAuthor, $postDate, $content);
            } else {
                $creator = new CreatesNewPageSourceFile($title, $pageClass, false, $content);
            }
            try {
                $path = $creator->save();
            } catch (FileConflictException $exception) {
                $this->abort($exception->getCode(), $exception->getMessage());
            }

            $this->flash('justCreatedPage', RouteKey::fromPage($pageClass, $pageClass::pathToIdentifier($path))->get());
            $this->sendJsonResponse(201, "Created file '$path'!");
        }
    }

    protected static function injectDashboardButton(string $contents): string
    {
        return str_replace('</body>', sprintf('%s</body>', self::button()), $contents);
    }

    protected static function button(): string
    {
        return <<<'HTML'
            <style>
                 .dashboard-btn {
                    background-image: linear-gradient(to right, #1FA2FF 0%, #12D8FA  51%, #1FA2FF  100%);
                    margin: 10px;
                    padding: .5rem 1rem;
                    text-align: center;
                    transition: 0.5s;
                    background-size: 200% auto;
                    background-position: right center;
                    color: white;
                    box-shadow: 0 0 20px #162134;
                    border-radius: 10px;
                    display: block;
                    position: absolute;
                    right: 1rem;
                    top: 1rem
                 }

                 .dashboard-btn:hover {
                    background-position: left center;
                    color: #fff;
                    text-decoration: none;
                }
            </style>
            <a href="/dashboard" class="dashboard-btn">Dashboard</a>
        HTML;
    }

    protected static function welcomeComponent(): string
    {
        $dashboardMessage = config('hyde.server.dashboard.welcome-dashboard', true)
            ? '<br>Scroll down to see it, or visit <a href="/dashboard" style="color: #1FA2FF;">/dashboard</a> at any time!' : '';

        return <<<HTML
            <!-- Dashboard Component -->
            <section class="text-white">
                <hr style="border-width: 1px; max-width: 240px; opacity: .75; margin-top: 30px; margin-bottom: 24px">
                <p style="margin-bottom: 8px;">
                    <span style="
                        background: #1FA2FF;
                        background: -webkit-linear-gradient(to right, #1FA2FF, #12D8FA, #1FA2FF);
                        background: linear-gradient(to right, #1FA2FF, #12D8FA, #1FA2FF);
                        padding: 3px 8px;
                        border-radius: 25px;
                        font-size: 12px;
                        text-transform: uppercase;
                        font-weight: 600;
                    ">New</span> When using the Realtime Compiler, you now have a content dashboard!
                    $dashboardMessage
                </p>

                <a href="#dashboard" onclick="document.getElementById('dashboard').scrollIntoView({behavior: 'smooth'}); return false;">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#ffffff"><path d="M0 0h24v24H0z" fill="none"/><path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z"/></svg>
                </a>
            </section>
            <!-- End Dashboard Component -->
        HTML;
    }

    protected static function welcomeFrame(): string
    {
        return <<<'HTML'
            <aside>
                <iframe id="dashboard" src="/dashboard?embedded=true" frameborder="0" style="width: 100vw; height: 100vh; position: absolute;"></iframe>
            </aside>
        HTML;
    }

    protected static function getPackageVersion(string $packageName): string
    {
        try {
            $prettyVersion = InstalledVersions::getPrettyVersion($packageName);
        } catch (OutOfBoundsException) {
            //
        }

        return $prettyVersion ?? 'unreleased';
    }

    protected function sendJsonResponse(int $statusCode, string $body): never
    {
        $statusMessage = match ($statusCode) {
            200 => 'OK',
            201 => 'Created',
            default => 'Internal Server Error',
        };

        (new JsonResponse($statusCode, $statusMessage, [
            'body' => $body,
        ]))->send();

        exit;
    }

    protected function sendJsonErrorResponse(HttpException $exception): never
    {
        $statusMessage = match ($exception->getStatusCode()) {
            400 => 'Bad Request',
            403 => 'Forbidden',
            404 => 'Not Found',
            409 => 'Conflict',
            default => 'Internal Server Error',
        };

        (new JsonResponse($exception->getStatusCode(), $statusMessage, [
            'error' => $exception->getMessage(),
        ]))->send();

        exit;
    }

    protected function abort(int $code, string $message): never
    {
        throw new HttpException($code, $message);
    }

    protected function findGeneralOpenBinary(): string
    {
        return match (PHP_OS_FAMILY) {
            'Windows' => 'powershell Start-Process', // Using PowerShell allows us to open the file in the background
            'Darwin' => 'open',
            'Linux' => 'xdg-open',
            default => throw new HttpException(500, sprintf("Unable to find a matching binary for OS family '%s'", PHP_OS_FAMILY))
        };
    }
}
