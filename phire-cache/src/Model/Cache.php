<?php

namespace Phire\Cache\Model;

use Phire\Model\AbstractModel;
use Phire\Table;
use Pop\Cache as C;

class Cache extends AbstractModel
{

    /**
     * Get cache adapter
     *
     * @return mixed
     */
    public function getCacheAdapter()
    {
        $status   = Table\Config::findById('cache_status');
        $adapter  = Table\Config::findById('cache_adapter');
        $lifetime = Table\Config::findById('cache_lifetime');
        $cache    = null;
        $dir      = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/cache';

        if (isset($status->value) && ($status->value) && isset($adapter->value) && isset($lifetime->value)) {
            switch ($adapter->value) {
                case 'File':
                    $cache = new C\Cache(new C\Adapter\File($dir, $lifetime->value));
                    break;
                case 'Sqlite':
                    if (!file_exists($dir . '/' . '.htphirecache.sqlite')) {
                        touch($dir . '/' . '.htphirecache.sqlite');
                        chmod($dir . '/' . '.htphirecache.sqlite', 0777);
                    }
                    $cache = new C\Cache(new C\Adapter\Sqlite($dir . '/' . '.htphirecache.sqlite', $lifetime->value));
                    break;
                case 'Apc':
                    $cache = new C\Cache(new C\Adapter\Apc($lifetime->value));
                    break;
                case 'Memcached':
                    $cache = new C\Cache(new C\Adapter\Memcached($lifetime->value));
                    break;
            }
        }

        return $cache;
    }

    /**
     * Get cache config
     *
     * @return array
     */
    public function getConfig()
    {
        $status   = Table\Config::findById('cache_status');
        $adapter  = Table\Config::findById('cache_adapter');
        $lifetime = Table\Config::findById('cache_lifetime');
        $adapters = [];

        $cacheAdapters = \Pop\Cache\Cache::getAvailableAdapters();

        foreach ($cacheAdapters as $adapt => $avail) {
            if (($adapt !== 'file') && ($avail)) {
                $adapters[ucwords($adapt)] = ucwords($adapt);
            }
        }

        if (isset($lifetime->value)) {
            $cacheStatus   = (int)$status->value;
            $cacheLifetime = $lifetime->value;
            // Days
            if ($cacheLifetime >= 86400) {
                $cacheLifetimeValue = round(($cacheLifetime / 86400), 1);
                $cacheLifetimeUnit  = 'Days';
            // Hours
            } else if (($cacheLifetime < 86400) && ($cacheLifetime >= 3600)) {
                $cacheLifetimeValue = round(($cacheLifetime / 3600), 1);
                $cacheLifetimeUnit  = 'Hours';
            // Minutes
            } else {
                $cacheLifetimeValue = round(($cacheLifetime / 60), 1);
                $cacheLifetimeUnit  = 'Minutes';
            }
        } else {
            $cacheStatus        = 0;
            $cacheLifetime      = 0;
            $cacheLifetimeValue = 0;
            $cacheLifetimeUnit  = null;
        }

        $config = [
            'cache_status'         => $cacheStatus,
            'cache_adapter'        => (isset($adapter->value) ? $adapter->value : null),
            'cache_lifetime'       => $cacheLifetime,
            'cache_lifetime_value' => $cacheLifetimeValue,
            'cache_lifetime_unit'  => $cacheLifetimeUnit,
            'cache_adapters'       => $adapters
        ];

        return $config;
    }

    /**
     * Save cache config
     *
     * @param  array $post
     * @return void
     */
    public function save(array $post)
    {
        if (isset($post['cache_status'])) {
            $config = Table\Config::findById('cache_status');
            if (isset($config->value)) {
                $config->value = (int)$post['cache_status'];
            } else {
                $config = new Table\Config([
                    'setting' => 'cache_status',
                    'value'   => (int)$post['cache_status']
                ]);
            }
            $config->save();
        }

        $oldAdapter = null;
        if (isset($post['cache_adapter']) && !empty($post['cache_adapter'])) {
            $config = Table\Config::findById('cache_adapter');
            if (isset($config->value)) {
                $oldAdapter    = $config->value;
                $config->value = $post['cache_adapter'];

                if ($oldAdapter != $post['cache_adapter']) {
                    $this->clear();
                }
            } else {
                $config = new Table\Config([
                    'setting' => 'cache_adapter',
                    'value'   => $post['cache_adapter']
                ]);
            }
            $config->save();
        }

        $lifetime = null;

        if (isset($post['cache_lifetime_value']) && !empty($post['cache_lifetime_value']) &&
            isset($post['cache_lifetime_unit']) && !empty($post['cache_lifetime_unit'])) {
            switch ($post['cache_lifetime_unit']) {
                case 'Days':
                    $lifetime = round(($post['cache_lifetime_value'] * 86400), 1);
                    break;
                case 'Hours':
                    $lifetime = round(($post['cache_lifetime_value'] * 3600), 1);
                    break;
                case 'Minutes':
                    $lifetime = round(($post['cache_lifetime_value'] * 60), 1);
                    break;
            }
        }

        if (null !== $lifetime) {
            $config = Table\Config::findById('cache_lifetime');
            if (isset($config->value)) {
                $config->value = $lifetime;
            } else {
                $config = new Table\Config([
                    'setting' => 'cache_lifetime',
                    'value'   => $lifetime
                ]);
            }
            $config->save();
        }

        if (isset($post['cache_clear']) && ($post['cache_clear'])) {
            $this->clear();
        }
    }

    /**
     * Clear cache
     *
     * @return void
     */
    public function clear()
    {
        $cache = $this->getCacheAdapter();
        if (null !== $cache) {
            $cache->clear();
            if ($cache->adapter() instanceof C\Adapter\Sqlite) {
                $cache->adapter()->delete();
            }
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/cache/index.html')) {
                copy(
                    $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/index.html',
                    $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/cache/index.html'
                );
                chmod($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/cache/index.html', 0777);
            }
        }
    }

}
