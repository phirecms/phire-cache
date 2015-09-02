<?php

namespace Phire\Cache\Event;

use Phire\Cache\Model;
use Pop\Application;

class Cache
{

    /**
     * Load content from cache
     *
     * @param  Application $application
     * @return void
     */
    public static function load(Application $application)
    {
        if ((!$_POST) && ($application->router()->getController() instanceof \Phire\Content\Controller\IndexController)) {
            $sess  = $application->services()->get('session');
            $uri   = $application->router()->getController()->request()->getRequestUri();
            $cache = (new Model\Cache())->getCacheAdapter();

            if ((null !== $cache) && !isset($sess->user)) {
                if ($cache->load($uri) !== false) {
                    $content = $cache->load($uri);
                    $application->router()->getController()->response()->setBody($content['body']);
                    $application->router()->getController()->send(200, ['Content-Type' => $content['content-type']]);
                    exit();
                }
            }
        }
    }

    /**
     * Save content to cache
     *
     * @param  Application $application
     * @return void
     */
    public static function save(Application $application)
    {
        if ((!$_POST) &&
            ($application->router()->getController() instanceof \Phire\Content\Controller\IndexController) &&
            ($application->router()->getController()->response()->getCode() == 200)) {

            $sess  = $application->services()->get('session');
            $uri   = $application->router()->getController()->request()->getRequestUri();
            $cache = (new Model\Cache())->getCacheAdapter();

            if ((null !== $cache) && !isset($sess->user)) {
                $body  = $application->router()->getController()->response()->getBody();
                $body .= PHP_EOL . PHP_EOL .
                    '<!-- Generated by the Phire Cache module on ' . date('M j, Y H:i:s') . '. //-->' .
                    PHP_EOL . PHP_EOL;

                $cache->save($uri, [
                    'content-type' => $application->router()->getController()->response()->getHeader('Content-Type'),
                    'body'         => $body
                ]);
            }
        }
    }

}