<?php  /** @noinspection PhpFullyQualifiedNameUsageInspection */

declare(strict_types=1);

/**
 * @internal This file belongs to the HydePHP development repository,
 * and is not covered by the backward compatibility promise,
 * as it is not versioned through any official releases.
 *
 * When using a good IDE, such as PhpStorm, this file will be used to provide
 * rich code completion for "magic" fields, such as the global $page variables.
 */

// Global page variables

/** @var \Hyde\Pages\Concerns\HydePage $page The page being compiled/previewed */
$page = \Hyde\Support\Facades\Render::getPage();

/** @var \Hyde\Support\Models\Route $currentRoute The route for the page being compiled/previewed */
$currentRoute = \Hyde\Support\Facades\Render::getCurrentRoute();

/** @var string $currentPage The route key for the page being compiled/previewed */
$currentPage = \Hyde\Support\Facades\Render::getCurrentPage();
