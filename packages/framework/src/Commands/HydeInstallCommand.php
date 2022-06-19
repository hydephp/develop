<?php

namespace Hyde\Framework\Commands;

use Hyde\Framework\Concerns\Commands\AsksToRebuildSite;
use Hyde\Framework\Hyde;
use Illuminate\Support\Facades\Cache;
use LaravelZero\Framework\Commands\Command;

/**
 * Initialize a new Hyde project.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\HydeInstallCommandTest
 */
class HydeInstallCommand extends Command
{
    use AsksToRebuildSite;

    protected $signature = 'install {--mark-installed : Hide the command without running the installer}';
    protected $description = 'Initialize a new Hyde project.';

    public ?string $siteName = null;
    public ?string $siteUrl = null;

    public function __construct()
    {
        parent::__construct();

        if ($this->isInstalled()) {
            $this->setHidden();
        }
    }

    public function handle(): int
    {
        if ($this->option('mark-installed')) {
            $this->info('Marking Hyde as installed and hiding the command!');
            $this->markInstalled();

            return 0;
        }

        $this->title('Welcome to HydePHP!');

        $this->info('This guided installer is optional, but can help you to get set up quickly.');

        $this->warn('Please note that this installer should not be run in existing projects.');

        if (! $this->confirm('Do you want to continue?', true)) {
            $this->comment('Aborting installation.');

            return 130;
        }

        $this->info('Installing HydePHP...');
        $this->newLine();

        $this->call('update:configs');

        $this->promptForSiteName();

        $this->promptForSiteUrl();

        $this->promptForHomepage();

        $this->askToRebuildSite();

        $this->markInstalled();

        $this->newLine();

        $this->line('<bg=blue;fg=white>                                                         </>');
        $this->line('<bg=blue;fg=white>         HydePHP has been installed successfully!        </>');
        $this->line('<bg=blue;fg=white>                Go build something great!                </>');
        $this->line('<bg=blue;fg=white>                                                         </>');

        $this->newLine();

        $this->info('What\'s next?');
        $this->newLine();
        $this->line('<fg=gray> > Run `hyde build` to build your site.</>');
        $this->line('<fg=gray> > Run `hyde serve` to start a development server that rebuilds your site on the fly.</>');
        $this->line('<fg=gray> > Run `hyde help` to get help for one of the commands.</>');
        $this->line('<fg=gray> > You can run `npm install` and `npm run dev` to compile any TailwindCSS assets.</>');
        $this->newLine();
        $this->line('<fg=gray> > You may also want to check out the <href=https://hydephp.com/docs>HydePHP Docs</>');
        $this->newLine();

        return 0;
    }

    protected function promptForSiteName()
    {
        if ($this->siteName = $this->ask('What is the name of your site? <fg=gray>(leave blank to skip)</>')) {
            $this->updateSiteName();
            $this->info('Site name set to: <comment>'.$this->siteName.'</>');

            return;
        }

        $this->line('Skipping site name.');
    }

    protected function promptForSiteUrl()
    {
        if ($this->siteUrl = $this->ask('What is the URL of your site? <fg=gray>(leave blank to skip)</>')) {
            $this->updateSiteUrl();
            $this->info('Site URL set to: <comment>'.$this->siteUrl.'</>');

            return;
        }

        $this->line('Skipping site URL.');
    }

    protected function promptForHomepage()
    {
        $this->newLine();

        $this->info('Hyde has a few different homepage options.');
        if ($this->confirm('Would you like to select an index.blade.php file?')) {
            $this->call('publish:homepage');
        } else {
            $this->line('Okay, leaving the default homepage.');
        }
    }

    protected function updateSiteName(): void
    {
        $config = file_get_contents(Hyde::path('config/hyde.php'));
        $config = str_replace(
            "'name' => ".'$siteName'." = env('SITE_NAME', 'HydePHP'),",
            "'name' => ".'$siteName'." = env('SITE_NAME', '".$this->siteName."'),",
            $config
        );
        file_put_contents(Hyde::path('config/hyde.php'), $config);
    }

    protected function updateSiteUrl(): void
    {
        $config = file_get_contents(Hyde::path('config/hyde.php'));
        $config = str_replace(
            "'site_url' => env('SITE_URL', null),",
            "'site_url' => env('SITE_URL', '".$this->siteUrl."'),",
            $config
        );
        file_put_contents(Hyde::path('config/hyde.php'), $config);
    }

    protected function markInstalled(): void
    {
        Cache::forever('hyde.installed', true);
    }

    protected function isInstalled(): bool
    {
        return Cache::get('hyde.installed', false) === true;
    }
}
