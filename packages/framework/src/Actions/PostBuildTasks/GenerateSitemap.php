<?php

namespace Hyde\Framework\Actions\PostBuildTasks;

use Hyde\Framework\Contracts\AbstractBuildTask;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Hyde;
use Hyde\Framework\Services\SitemapService;

class GenerateSitemap extends AbstractBuildTask
{
    public static string $description = 'Generating sitemap';

    public function run(): void
    {
        $this->runPreflightCheck();

        file_put_contents(
            Hyde::getSiteOutputPath('sitemap.xml'),
            SitemapService::generateSitemap()
        );
    }

    public function then(): void
    {
        $this->writeln("\n".' > Created <info>sitemap.xml</info> in '.$this->getExecutionTime());
    }

    /** @deprecated to reduce complexity, or throw exceptions that can be caught with messages */
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
