<?php
/**
 * @deprecated There should be virtually no reason for this file to edited from within a HydePHP application.
 *             We should instead attempt to inline the changes here, but provide the ability to load from
 *             a custom file if one is present.
 */

return [
    'default' => 'file',

    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],
    ],
];
