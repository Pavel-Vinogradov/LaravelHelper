<?php

declare(strict_types=1);

return [
    'path' => base_path() . '/app/Modules',
    'base_namespace' => 'App\Modules',
    'groupWithoutPrefix' => 'Pub',
    'groupMiddleware' => [

    ],
    'modules' => [
        'Admin' => [],
        'Pub' => [],
    ],
];
