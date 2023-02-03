<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Illuminate\Foundation\Console\VendorPublishCommand as BaseCommand;

/**
 * Publish any publishable assets from vendor packages.
 *
 * @property \Illuminate\Console\View\Components\Factory $illuminateComponents
 * @see \Hyde\Framework\Testing\Feature\Commands\VendorPublishCommandTest
 */
class VendorPublishCommand extends BaseCommand
{
    public function handle()
    {
        $this->illuminateComponents = $this->components;
        $this->components = $this->voidComponents();

        parent::handle();
    }

    /**
     * Dynamically revert to the same output style as our other commands.
     */
    protected function voidComponents(): object
    {
        return new class($this) {
            protected VendorPublishCommand $command;

            public function __construct(VendorPublishCommand $command)
            {
                $this->command = $command;
            }

            public function __call($method, $parameters)
            {
                if (method_exists($this->command, $method)) {
                    return $this->command->$method(...$parameters);
                }

                return $this->command->illuminateComponents->$method(...$parameters);
            }
        };
    }
}

