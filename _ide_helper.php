<?php  /** @noinspection PhpFullyQualifiedNameUsageInspection */

declare(strict_types=1);

/** @var \Hyde\Pages\Concerns\HydePage $page The page being compiled/previewed */
$page = \Hyde\Support\Facades\Render::getPage();

/** @var \Hyde\Support\Models\Route $currentRoute The route for the page being compiled/previewed */
$currentRoute = \Hyde\Support\Facades\Render::getCurrentRoute();

/** @var string $currentPage The route key for the page being compiled/previewed */
$currentPage = \Hyde\Support\Facades\Render::getCurrentPage();
