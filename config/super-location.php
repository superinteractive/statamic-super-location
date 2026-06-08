<?php

declare(strict_types=1);

return [
    'tiles' => [
        'url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        'attribution' => '&copy; <a target="_blank" rel="noopener" href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        'max_zoom' => 19,
    ],

    'search' => [
        'endpoint' => 'https://nominatim.openstreetmap.org/search',
        'default_parameters' => [
            'format' => 'jsonv2',
            'addressdetails' => 1,
            'limit' => 5,
        ],
        'language' => 'en',
    ],

    'defaults' => [
        'latitude' => 0,
        'longitude' => 0,
        'zoom' => 2,
    ],
];
