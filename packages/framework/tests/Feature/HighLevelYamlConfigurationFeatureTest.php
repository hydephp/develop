<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;

/**
 * High level test for the Yaml configuration feature.
 *
 * @see \Hyde\Framework\Testing\Feature\YamlConfigurationFeatureTest
 *
 * @covers \Hyde\Foundation\Internal\LoadYamlConfiguration
 * @covers \Hyde\Foundation\Internal\LoadYamlEnvironmentVariables
 * @covers \Hyde\Foundation\Internal\YamlConfigurationRepository
 */
class HighLevelYamlConfigurationFeatureTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->clearEnvVars();

        parent::tearDown();
    }

    protected function clearEnvVars(): void
    {
        // Todo: Can we access loader? https://github.com/vlucas/phpdotenv/pull/107/files
        putenv('SITE_NAME');
        unset($_ENV['SITE_NAME'], $_SERVER['SITE_NAME']);
    }
}
