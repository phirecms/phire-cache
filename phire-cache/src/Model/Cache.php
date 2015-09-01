<?php

namespace Phire\Cache\Model;

use Phire\Model\AbstractModel;
use Phire\Table;

class Cache extends AbstractModel
{

    /**
     * Get cache config
     *
     * @return array
     */
    public function getConfig()
    {
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
            $cacheLifetime      = 0;
            $cacheLifetimeValue = 0;
            $cacheLifetimeUnit  = null;
        }

        $config = [
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
        if (isset($post['cache_adapter']) && !empty($post['cache_adapter'])) {
            $config = Table\Config::findById('cache_adapter');
            if (isset($config->value)) {
                $config->value = $post['cache_adapter'];
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
    }

}
