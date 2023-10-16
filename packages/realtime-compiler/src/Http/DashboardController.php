<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Hyde\Hyde;
use OutOfBoundsException;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Framework\Actions\AnonymousViewCompiler;
use Desilva\Microserve\Request;
use Composer\InstalledVersions;

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

    public function __construct()
    {
        $this->title = config('hyde.name').' - Dashboard';
    }

    public function show(): string
    {
        return AnonymousViewCompiler::handle(__DIR__.'/../../resources/dashboard.blade.php', array_merge(
            (array) $this, ['dashboard' => $this, 'request' => Request::capture()],
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

            if (config('hyde.server.dashboard.button', false)) {
                $contents = self::injectDashboardButton($contents);
            }
        }

        return $contents;
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
}
