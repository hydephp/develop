<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Composer\InstalledVersions;
use Hyde\Framework\Actions\AnonymousViewCompiler;
use Hyde\Hyde;
use Hyde\RealtimeCompiler\Concerns\InteractsWithLaravel;

use function app;
use function array_merge;
use function str_starts_with;

class DashboardController
{
    use InteractsWithLaravel;

    public string $title;

    public function __construct()
    {
        $this->bootApplication();

        $this->title = config('site.name') . ' - Dashboard';
    }

    public function show(): string
    {
        return (new AnonymousViewCompiler(__DIR__.'/../../resources/dashboard.blade.php', array_merge(
            (array) $this,
            ['dashboard' => $this],
        )))->__invoke();
    }

    public function isEnhanced(): bool
    {
        return DashboardApiController::enabled();
    }

    public function getVersion(): string
    {
        $version = InstalledVersions::getPrettyVersion('hyde/realtime-compiler');
        return str_starts_with($version, 'dev-') ? $version : "v$version";
    }

    public function getVersions(): array
    {
        return [
            'Git Version:' => app('git.version'),
            'Hyde Version:' => InstalledVersions::getPrettyVersion('hyde/hyde') ?: 'unreleased',
            'Framework Version:' => InstalledVersions::getPrettyVersion('hyde/framework') ?: 'unreleased',
        ];
    }

    /** @return array<string, \Hyde\Support\Models\Route> */
    public function getPageList(): array
    {
        return Hyde::routes()->all();
    }

    public function getEditLink(string $projectFilePath): string
    {
        return "/dashboard-api?action=openFileInEditor&path=$projectFilePath";
    }
}
