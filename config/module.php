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
                'name'     => 'app.route.post',
                'action'   => 'Phire\Cache\Event\Cache::load',
                'priority' => 1000
            ],
            [
                'name'     => 'app.dispatch.post',
                'action'   => 'Phire\Cache\Event\Cache::save',
                'priority' => 1000
            ]
        ],
        'install' => function() {
            mkdir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/cache');
            chmod($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/cache', 0777);
            copy(
                $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/index.html',
                $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/cache/index.html'
            );
            chmod($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/cache/index.html', 0777);
        },
        'uninstall' => function() {
            $config = \Phire\Table\Config::findById('cache_adapter');
            if (isset($config->setting)) {
                $config->delete();
            }
            $config = \Phire\Table\Config::findById('cache_lifetime');
            if (isset($config->setting)) {
                $config->delete();
            }
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/cache')) {
                $dir = new \Pop\File\Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/cache');
                $dir->emptyDir(true);
            }
        },
        'exclude' => []
    ]
];
