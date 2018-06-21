<?php

/*
 * Set specific configuration variables here
 */
return [
    // automatic loading of routes through main service provider
    'routes' => true,
    // layout where the Shop will show into, i.e. admin.layouts.master
    'layout-master' => 'shop::admin.layout',
    // URLs
    'urls' => [
        'media-manager' => '#/your-media-manager-url',
    ],
    'paths' => [
        // path to widgets, in your resources directory
        //'widgets' => 'widgets',
    ],
];
