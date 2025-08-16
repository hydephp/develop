<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;

/**
 * High level test for the Yaml configuration feature.
 *
 * @see \Hyde\Framework\Testing\Feature\YamlConfigurationFeatureTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\Internal\LoadYamlConfiguration::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\Internal\LoadYamlEnvironmentVariables::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\Internal\YamlConfigurationRepository::class)]
class HighLevelYamlConfigurationFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        $this->setUpConfigurationBeforeApplicationBoots();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        unlink('hyde.yml');
        unlink('config/custom.php');

        $this->clearEnvVars();

        parent::tearDown();
    }

    protected function setUpConfigurationBeforeApplicationBoots(): void
    {
        file_put_contents('hyde.yml', <<<'YAML'
        hyde:
            name: Yaml Site Name
        docs:
            sidebar_order:
                # Reversed compared to the default order
                - getting-started
                - installation
                - readme
        custom:
            setting_one: Override
            setting_two: Added
        YAML);

        file_put_contents('config/custom.php', <<<'PHP'
        <?php
        return [
            'setting_one' => 'Default',
            'setting_three' => 'Inherited'
        ];
        PHP);
    }

    public function testTestTheYamlConfigurationFeature()
    {
        $config = config()->all();

        $this->assertSame('Yaml Site Name', env('SITE_NAME'));
        $this->assertSame('Yaml Site Name', $_ENV['SITE_NAME']);
        $this->assertSame('Yaml Site Name', $_SERVER['SITE_NAME']);

        $this->assertSame('Yaml Site Name', $config['hyde']['name']);
        $this->assertSame('Yaml Site Name Docs', $config['docs']['sidebar']['header']);
        $this->assertSame(['getting-started', 'installation', 'readme'], $config['docs']['sidebar_order']);

        $this->assertSame('Override', $config['custom']['setting_one']);
        $this->assertSame('Added', $config['custom']['setting_two']);
        $this->assertSame('Inherited', $config['custom']['setting_three']);
    }

    protected function clearEnvVars(): void
    {
        // Todo: Can we access loader? https://github.com/vlucas/phpdotenv/pull/107/files
        putenv('SITE_NAME');
        unset($_ENV['SITE_NAME'], $_SERVER['SITE_NAME']);
    }
}
