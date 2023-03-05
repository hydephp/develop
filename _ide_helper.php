<?php  /** @noinspection PhpFullyQualifiedNameUsageInspection */

declare(strict_types=1);

/** @var \Hyde\Pages\Concerns\HydePage $page; */
$page = \Hyde\Support\Facades\Render::getPage();
/** @var \Hyde\Support\Models\Route $currentRoute; */
$currentRoute = \Hyde\Support\Facades\Render::getCurrentRoute();
/** @var string $currentPage; */
$currentPage = \Hyde\Support\Facades\Render::getCurrentPage();
