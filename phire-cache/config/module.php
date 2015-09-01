<?php
/**
 * Module Name: phire-cache
 * Author: Nick Sagona
 * Description: This is the cache module for Phire CMS 2
 * Version: 1.0
 */
return [
    'phire-cache' => [
        'prefix'     => 'Phire\Cache\\',
        'src'        => __DIR__ . '/../src',
        'routes'     => include 'routes.php',
        'resources'  => include 'resources.php',
        'nav.module' => [
            'name' => 'Cache Config',
            'href' => '/cache',
            'acl' => [
                'resource'   => 'cache',
                'permission' => 'index'
            ]
        ],
        'events' => [
            [
                'name'     => 'app.route.pre',
                'action'   => 'Phire\Cache\Event\Cache::bootstrap',
                'priority' => 1000
            ]
        ],
        'uninstall' => function() {
            $config = \Phire\Table\Config::findById('cache_adapter');
            if (isset($config->setting)) {
                $config->delete();
            }
            $config = \Phire\Table\Config::findById('cache_lifetime');
            if (isset($config->setting)) {
                $config->delete();
            }
        }
    ]
];
