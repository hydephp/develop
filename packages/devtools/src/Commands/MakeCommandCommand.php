<?php

namespace Hyde\DevTools\Commands;

use Hyde\Framework\Hyde;
use Illuminate\Foundation\Console\ConsoleMakeCommand;

class MakeCommandCommand extends ConsoleMakeCommand
{
    protected $description = 'Create a new Hyde/Framework command';

    protected function getStub()
    {
        return __DIR__.'/stubs/console.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Commands';
    }

    protected function getPath($name)
    {
        return Hyde::vendorPath('src/Commands/Hyde'.ucfirst(basename($name)).'Command.php');
    }
}
