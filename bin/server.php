<?php

define('BASE_PATH', realpath(getcwd()));
define('HYDE_START', microtime(true));

require_once sprintf("%s/vendor/autoload.php", BASE_PATH);

$app = \Desilva\Microserve\Microserve::boot(Hyde\RealtimeCompiler\Http\HttpKernel::class);
$app->handle();