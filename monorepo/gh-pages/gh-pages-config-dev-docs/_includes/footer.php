<?php

return 'HydePHP Monorepo '.Hyde\Hyde::version().
            (file_exists(getcwd() . ('/origin-ref'))
            ? '-'.'<a href="https://github.com/caendesilva/hyde-monorepo/commit/'.file_get_contents(getcwd() . ('/origin-ref')).'">'.substr(file_get_contents(getcwd() . ('/origin-ref')), 0, 7).'</a>'
            : '') . ' (compiled '.date('Y-m-d H:i:s').' '. now()->format('e'). ' - <a href="build-information.html">Build Information</a>)';
