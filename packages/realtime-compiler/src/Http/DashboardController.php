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
use Desilva\Microserve\Response;
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

/**
 * @internal This class is not intended to be edited outside the Hyde Realtime Compiler.
 */
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
        'You can disable tips using by setting `server.dashboard.tips` to `false` in `config/hyde.php`.',
        'The dashboard update your project files. You can disable this by setting `server.dashboard.interactive` to `false` in `config/hyde.php`.',
    ];

    protected JsonResponse $response;

    public function __construct()
    {
        $this->title = config('hyde.name').' - Dashboard';
        $this->request = Request::capture();

        $this->loadFlashData();

        if ($this->request->method === 'POST') {
            $this->isAsync = (getallheaders()['X-RC-Handler'] ?? getallheaders()['x-rc-handler'] ?? null) === 'Async';
        }
    }

    public function handle(): Response
    {
        if ($this->request->method === 'POST') {
            if (! $this->isInteractive()) {
                return $this->sendJsonErrorResponse(403, 'Enable `server.editor` in `config/hyde.php` to use interactive dashboard features.');
            }

            if ($this->shouldUnsafeRequestBeBlocked()) {
                return $this->sendJsonErrorResponse(403, "Refusing to serve request from address {$_SERVER['REMOTE_ADDR']} (must be on localhost)");
            }

            try {
                return $this->handlePostRequest();
            } catch (HttpException $exception) {
                if (! $this->isAsync) {
                    throw $exception;
                }

                return $this->sendJsonErrorResponse($exception->getStatusCode(), $exception->getMessage());
            }
        }

        return new HtmlResponse(200, 'OK', [
            'body' => $this->show(),
        ]);
    }

    protected function show(): string
    {
        return AnonymousViewCompiler::handle(__DIR__.'/../../resources/dashboard.blade.php', array_merge(
            (array) $this, ['dashboard' => $this, 'request' => $this->request],
        ));
    }

    protected function handlePostRequest(): JsonResponse
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

        return $this->response ?? new JsonResponse(200, 'OK', [
            'message' => 'Action completed successfully',
        ]);
    }

    public function getVersion(): string
    {
        $version = InstalledVersions::getPrettyVersion('hyde/realtime-compiler');

        return str_starts_with($version, 'dev-') ? $version : "v$version";
    }

    public function getProjectInformation(): array
    {
        return [
            'Git Version' => app('git.version'),
            'Hyde Version' => self::getPackageVersion('hyde/hyde'),
            'Framework Version' => self::getPackageVersion('hyde/framework'),
            'Project Path' => Hyde::path(),
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

    /** @internal */
    public static function isMediaFileProbablyMinified(string $contents): bool
    {
        return substr_count(trim($contents), "\n") < 3 && strlen($contents) > 200;
    }

    /** @internal */
    public static function highlightMediaLibraryCode(string $contents): HtmlString
    {
        $contents = e($contents);
        $contents = str_replace(['&#039;', '&quot;'], ['%SQT%', '%DQT%'], $contents); // Temporarily replace escaped quotes

        if (static::isMediaFileProbablyMinified($contents)) {
            return new HtmlString(substr($contents, 0, count(MediaFile::files()) === 1 ? 2000 : 800));
        }

        $highlighted = str($contents)->explode("\n")->slice(0, 25)->map(function (string $line): string {
            $line = rtrim($line);

            if (str_starts_with($line, '//')) {
                return "<span style='font-size: 80%; color: gray'>$line</span>";
            }

            if (str_starts_with($line, '/*') && str_ends_with($line, '*/')) {
                // Commented code should not be additionally formatted, though we always want to comment multiline blocks
                $quickReturn = true;
            }

            $line = str_replace('/*', "<span style='font-size: 80%; color: gray'>/*", $line);
            $line = str_replace('*/', '*/</span>', $line);

            if ($quickReturn ?? false) {
                return rtrim($line);
            }

            $line = strtr($line, [
                '{' => "<span style='color: #0f6674'>{</span>",
                '}' => "<span style='color: #0f6674'>}</span>",
                '(' => "<span style='color: #0f6674'>(</span><span style=\"color: #f77243;\">",
                ')' => "</span><span style='color: #0f6674'>)</span>",
                ':' => "<span style='color: #0f6674'>:</span>",
                ';' => "<span style='color: #0f6674'>;</span>",
                '+' => "<span style='color: #0f6674'>+</span>",
                'return' => "<span style='color: #8e44ad'>return</span>",
                'function' => "<span style='color: #8e44ad'>function</span>",
            ]);

            return rtrim($line);
        })->implode("\n");

        $highlighted = str_replace(['%SQT%', '%DQT%'], ['&#039;', '&quot;'], $highlighted);

        return new HtmlString($highlighted);
    }

    public function showTips(): bool
    {
        return config('hyde.server.dashboard.tips', true);
    }

    public function getTip(): HtmlString
    {
        return new HtmlString(Str::inlineMarkdown(Arr::random(static::$tips)));
    }

    public static function enabled(): bool
    {
        // Previously, the setting was hyde.server.dashboard, so for backwards compatability we need this
        if (is_bool($oldConfig = config('hyde.server.dashboard'))) {
            trigger_error('Using `hyde.server.dashboard` as boolean is deprecated. Please use `hyde.server.dashboard.enabled` instead.', E_USER_DEPRECATED);

            return $oldConfig;
        }

        return config('hyde.server.dashboard.enabled', true);
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

    public function isInteractive(): bool
    {
        return config('hyde.server.dashboard.interactive', true);
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
        if ($this->isInteractive()) {
            $binary = $this->findGeneralOpenBinary();
            $path = Hyde::path();

            Process::run(sprintf('%s %s', $binary, escapeshellarg($path)))->throw();
        }
    }

    protected function openPageInEditor(HydePage $page): void
    {
        if ($this->isInteractive()) {
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
        if ($this->isInteractive()) {
            $binary = $this->findGeneralOpenBinary();
            $path = $file->getAbsolutePath();

            if (! in_array($file->getExtension(), ['png', 'svg', 'jpg', 'jpeg', 'gif', 'ico', 'css', 'js'])) {
                $this->abort(403, sprintf("Refusing to open unsafe file '%s'", basename($path)));
            }

            Process::run(sprintf('%s %s', $binary, escapeshellarg($path)))->throw();
        }
    }

    protected function createPage(): void
    {
        if ($this->isInteractive()) {
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
                default => $this->abort(400, "Unsupported page type '$pageType'"),
            };

            $creator = $pageClass === MarkdownPost::class
                ? new CreatesNewMarkdownPostFile($title, $postDescription, $postCategory, $postAuthor, $postDate, $content)
                : new CreatesNewPageSourceFile($title, $pageClass, false, $content);

            try {
                $path = $creator->save();
            } catch (FileConflictException $exception) {
                $this->abort($exception->getCode(), $exception->getMessage());
            }

            $this->flash('justCreatedPage', RouteKey::fromPage($pageClass, $pageClass::pathToIdentifier($path))->get());
            $this->setJsonResponse(201, "Created file '$path'!");
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

    protected function shouldUnsafeRequestBeBlocked(): bool
    {
        // As the dashboard is not password-protected, and it can make changes to the file system,
        // we block any requests that are not coming from the host machine. While we are clear
        // in the documentation that the realtime compiler should only be used for local
        // development, we still want to be extra careful in case someone forgets.

        $requestIp = $_SERVER['REMOTE_ADDR'];
        $allowedIps = ['::1', '127.0.0.1', 'localhost'];

        return ! in_array($requestIp, $allowedIps, true);
    }

    protected function setJsonResponse(int $statusCode, string $body): void
    {
        $this->response = new JsonResponse($statusCode, $this->matchStatusCode($statusCode), [
            'body' => $body,
        ]);
    }

    protected function sendJsonErrorResponse(int $statusCode, string $message): JsonResponse
    {
        return new JsonResponse($statusCode, $this->matchStatusCode($statusCode), [
            'error' => $message,
        ]);
    }

    protected function abort(int $code, string $message): never
    {
        throw new HttpException($code, $message);
    }

    protected function findGeneralOpenBinary(): string
    {
        return match (PHP_OS_FAMILY) {
            // Using PowerShell allows us to open the file in the background
            'Windows' => 'powershell Start-Process',
            'Darwin' => 'open',
            'Linux' => 'xdg-open',
            default => $this->abort(500,
                sprintf("Unable to find a matching 'open' binary for OS family '%s'", PHP_OS_FAMILY)
            )
        };
    }

    protected function matchStatusCode(int $statusCode): string
    {
        return match ($statusCode) {
            200 => 'OK',
            201 => 'Created',
            400 => 'Bad Request',
            403 => 'Forbidden',
            404 => 'Not Found',
            409 => 'Conflict',
            default => 'Internal Server Error',
        };
    }
}
