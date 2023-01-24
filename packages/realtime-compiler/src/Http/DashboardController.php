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
        // We still compile the welcome page for two reasons: one, so we can check if it's the default homepage (as we don't want to override it)
        // and two, because we still want to save the normal welcome page without any of the dynamic dashboard content.
        $contents = file_get_contents((new StaticPageBuilder($page))->__invoke());

        // If the page is the default welcome page we render the dynamic welcome page, otherwise we just return the contents
        return str_contains($contents, 'This is the default homepage') ? self::renderDynamicWelcomePage() : $contents;
    }

    protected static function renderDynamicWelcomePage(): string
    {
        return (new VirtualPage('index', [
            'title' => 'Welcome to HydePHP!',
            'dashboard' => new self(),
        ], view: __DIR__.'/../../resources/dynamic-welcome.blade.php'))->compile();
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
                New! When using the Realtime Compiler, you now have a content dashboard!
                Scroll down to see it, or visit <a href="/dashboard">/dashboard</a> at any time!
            </section>
            <!-- End Dashboard Component -->
        HTML;
    }

    protected static function welcomeFrame(): string
    {
        return <<<'HTML'
            <aside>
                <iframe src="/dashboard" frameborder="0" style="
                width: 100vw;
                height: 100vh;
                position: absolute;
                "></iframe>
            </aside>
        HTML;
    }
}
