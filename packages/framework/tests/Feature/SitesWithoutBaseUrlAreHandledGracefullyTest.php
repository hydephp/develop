<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;

/**
 * High level test to ensure that sites without a base URL are handled gracefully.
 *
 * For example: In case a user forgot to set a base URL, we don't want their site
 * to have localhost links in the compiled HTML output since that would break
 * things when deployed to production. So we fall back to relative links.
 *
 * Some things like sitemaps and RSS feeds cannot be generated without a base URL,
 * as their schemas generally do not allow relative URLs. In those cases, we
 * don't generate files at all, and we don't add any links to them either.
 *
 * @coversNothing This test is not testing a specific class, but a general feature of the framework.
 */
class SitesWithoutBaseUrlAreHandledGracefullyTest extends TestCase
{
    //
}
