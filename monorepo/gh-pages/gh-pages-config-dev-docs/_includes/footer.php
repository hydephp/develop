<?php

return 'HydePHP Monorepo '.Hyde\Framework\Hyde::version().
            (file_exists(Hyde\Framework\Hyde::path('origin-ref'))
            ? '-'.'<a href="https://github.com/caendesilva/hyde-monorepo/commit/'.file_get_contents(Hyde\Framework\Hyde::path('origin-ref')).'">'.substr(file_get_contents(Hyde\Framework\Hyde::path('origin-ref')), 0, 7).'</a>'
            : '') . ' (compiled '.date('Y-m-d H:i:s').' '. now()->format('e'). ' - <a href="/build-information.html">Build Information</a>)';