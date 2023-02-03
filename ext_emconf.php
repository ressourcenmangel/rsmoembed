<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Ressourcenmangel Oembed',
    'description' => 'A simple content element to embed elements like facebook, youtube, soundcloud, ...',
    'category' => 'plugin',
    'author' => 'Matthias Kappenberg',
    'author_email' => 'matthias.kappenberg@ressourcenmangel.de',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '1.0.2',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
