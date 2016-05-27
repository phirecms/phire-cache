<?php
/**
 * phire-cache routes
 */
return [
    APP_URI => [
        '/cache[/]' => [
            'controller' => 'Phire\Cache\Controller\IndexController',
            'action'     => 'index',
            'acl'        => [
                'resource'   => 'cache',
                'permission' => 'index'
            ]
        ]
    ]
];
