<?php

namespace Carbon\IncludeAssetsCache;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cache\CacheManager;

/**
 * @Flow\Proxy(false)
 */
class DynamicCssFileMonitorListener
{
    /**
     * @var CacheManager
     */
    protected $flowCacheManager;

    /**
     * @param CacheManager $flowCacheManager
     */
    public function __construct(CacheManager $flowCacheManager)
    {
        $this->flowCacheManager = $flowCacheManager;
    }

    /**
     * @param $fileMonitorIdentifier
     * @param array $changedFiles
     * @return void
     */
    public function flushDynamicAssetCacheOnFileChanges($fileMonitorIdentifier, array $changedFiles)
    {
        if ($fileMonitorIdentifier !== 'Carbon_Include_Assets_Files') {
            return;
        }

        if ($changedFiles === []) {
            return;
        }

        $this->flowCacheManager->getCache('Neos_Fusion_Content')->flushByTag('Carbon_Include_Assets');
    }
}
