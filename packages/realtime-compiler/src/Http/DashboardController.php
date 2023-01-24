<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Hyde\Pages\VirtualPage;
use function app;
use function array_merge;
use Composer\InstalledVersions;
use function config;
use function file_get_contents;
use Hyde\Framework\Actions\AnonymousViewCompiler;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Hyde;
use Hyde\Pages\Concerns\HydePage;
use function sprintf;
use function str_replace;
use function str_starts_with;

class DashboardController
{
    public string $title;

    public function __construct()
    {
        $this->title = config('site.name').' - Dashboard';
    }

    public function show(): string
    {
        return AnonymousViewCompiler::call(__DIR__.'/../../resources/dashboard.blade.php', array_merge(
            (array) $this, ['dashboard' => $this],
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
            'Hyde Version:' => InstalledVersions::getPrettyVersion('hyde/hyde') ?: 'unreleased',
            'Framework Version:' => InstalledVersions::getPrettyVersion('hyde/framework') ?: 'unreleased',
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
        return true;
    }

    // This method is called from the PageRouter and allows us to serve a dynamic welcome page
    public static function renderIndexPage(HydePage $page): string
    {
        $contents = file_get_contents((new StaticPageBuilder($page))->__invoke());

        // If the page is the default welcome page we inject dashboard components
        if (str_contains($contents, 'This is the default homepage')) {
            $contents = str_replace("</div>\n            <!-- End Main Hero Content -->", sprintf("%s\n            </div>\n            <!-- End Main Hero Content -->", self::welcomeComponent()), $contents);
            $contents = str_replace('</body>', sprintf("%s\n</body>", self::welcomeFrame()), $contents);
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
        return <<<'HTML'
            <!-- Dashboard Component -->
                        <section class="prose">
                            <hr class="text-white">
                            <p>
                                New! When using the Realtime Compiler, you now have a content dashboard!
                                Scroll down to see it, or visit <a href="/dashboard">/dashboard</a> at any time!
                            </p>
                            
                            <a href="#dashboard" onclick="document.getElementById('dashboard').scrollIntoView({behavior: 'smooth'}); return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0z" fill="none"/><path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z"/></svg>
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
}
