<?php

return [
    'frontend' => [
        'dimension/sitedefault/api' => [
            'target' => \Ressourcenmangel\Rsmoembed\Middleware\Api::class,
            'after' => [
                'typo3/cms-frontend/tsfe',
            ],
            'before' => [
                'typo3/cms-frontend/shortcut-and-mountpoint-redirect',
            ],
        ],
    ]
];
