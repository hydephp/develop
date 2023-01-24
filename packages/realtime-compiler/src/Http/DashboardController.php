<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use function app;
use function array_merge;
use Composer\InstalledVersions;
use Hyde\Framework\Actions\AnonymousViewCompiler;
use Hyde\Hyde;
use Hyde\RealtimeCompiler\Concerns\InteractsWithLaravel;
use function str_starts_with;

class DashboardController
{
    use InteractsWithLaravel;

    public string $title;

    public function __construct()
    {
        $this->bootApplication();

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

    public static function button(): string
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
}
