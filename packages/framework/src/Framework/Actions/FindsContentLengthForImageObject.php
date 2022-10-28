<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Hyde\Hyde;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use function str_starts_with;

/**
 * @see \Hyde\Framework\Testing\Feature\FindsContentLengthForImageObjectTest
 *
 * @todo Refactor output to buffer into the service container, so output can be controlled better,
 *       for example by grouping all warnings at the end of a build (with options to fail on warning, useful for CI setups).
 */
class FindsContentLengthForImageObject
{
    protected FeaturedImage $image;

    /**
     * Testing adding console debug output.
     */
    protected OutputInterface $output;

    public function __construct(FeaturedImage $image)
    {
        $this->image = $image;

        $this->output = new ConsoleOutput();
    }

    public function execute(): int
    {
        if ($this->isImageStoredRemotely()) {
            return $this->fetchRemoteImageInformation();
        }

        return $this->fetchLocalImageInformation();
    }

    protected function isImageStoredRemotely(): bool
    {
        return str_starts_with($this->image->getSource(), 'http');
    }

    protected function fetchRemoteImageInformation(): int
    {
        $this->write(PHP_EOL.'<fg=gray> ></> <fg=gray>Fetching remote image information for '.basename($this->image->getSource()).'...</>');

        $response = Http::withHeaders([
            'User-Agent' => config('hyde.http_user_agent', 'RSS Request Client'),
        ])->head($this->image->getSource());

        $headers = $response->headers();

        if (array_key_exists('Content-Length', $headers)) {
            return (int) key(array_flip($headers['Content-Length']));
        }

        $this->write(' > <comment>Warning:</comment> Could not find content length in headers for '.basename($this->image->getSource().'!')
        .PHP_EOL.'           <fg=gray> Using default content length of 0. '.'</>'
        .PHP_EOL.'           <fg=gray> Is the image path valid? '.($this->image->getSource()).'</>');

        return 0;
    }

    protected function fetchLocalImageInformation(): int
    {
        $path = Hyde::path('_media/'.$this->image->getSource());

        if (! file_exists($path)) {
            $this->write(' > <comment>Warning:</comment> Could not find image file at '.$path.'!'
            .PHP_EOL.'         <fg=gray>   Using default content length of 0. '.'</>');

            return 0;
        }

        return filesize($path);
    }

    protected function write(string $string): void
    {
        $this->output->writeln($string);
    }
}
