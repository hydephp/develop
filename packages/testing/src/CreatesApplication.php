<?php

namespace Hyde\Testing;

use Hyde\Foundation\HydeKernel;
use Hyde\Hyde;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication(): Application
    {
        $app = require file_exists(__DIR__.'/../../../app/bootstrap.php') ? __DIR__.'/../../../app/bootstrap.php' : getcwd().'/app/bootstrap.php';

        $app->make(Kernel::class)->bootstrap();

        HydeKernel::setInstance(new HydeKernel(Hyde::path()));

        return $app;
    }
}
