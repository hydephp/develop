<?php

namespace Hyde\Framework\Commands;

use Hyde\Framework\Concerns\ActionCommand;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Hyde;
use Hyde\Framework\Services\SitemapService;

/**
 * Hyde Command to run the Build Process for the Sitemap.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\HydeBuildSitemapCommandTest
 */
class HydeBuildSitemapCommand extends ActionCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'build:sitemap';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap.xml';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (! $this->runPreflightCheck()) {
            return 1;
        }

        $this->action('Generating sitemap', function () {
            file_put_contents(
                Hyde::getSiteOutputPath('sitemap.xml'),
                SitemapService::generateSitemap()
            );
        }, 'Created <info>sitemap.xml</info>');

        return 0;
    }

    protected function runPreflightCheck(): bool
    {
        if (! Features::sitemap()) {
            $this->error('Cannot generate sitemap.xml, please check your configuration.');

            if (! Hyde::hasSiteUrl()) {
                $this->warn('Hint: You don\'t have a site URL configured. Check config/hyde.php');
            }
            if (config('site.generate_sitemap', true) !== true) {
                $this->warn('Hint: You have disabled sitemap generation in config/hyde.php');
                $this->line(' > You can enable sitemap generation by setting <info>`site.generate_sitemap`</> to <info>`true`</>');
            }
            if (! extension_loaded('simplexml') || config('testing.mock_disabled_extensions', false) === true) {
                $this->warn('Hint: You don\'t have the <info>`simplexml`</> extension installed. Check your PHP installation.');
            }

            return false;
        }

        return true;
    }
}
