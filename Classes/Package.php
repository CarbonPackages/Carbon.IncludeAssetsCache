<?php
namespace Carbon\IncludeAssetsCache;

use Neos\Flow\Cache\CacheManager;
use Neos\Flow\Core\Booting\Sequence;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Monitor\FileMonitor;
use Neos\Flow\Package\Package as BasePackage;
use Neos\Flow\Package\PackageManager;

class Package extends BasePackage
{

    /**
     * Invokes custom PHP code directly after the package manager has been initialized.
     *
     * @param Bootstrap $bootstrap The current bootstrap
     * @return void
     */

    public function boot(Bootstrap $bootstrap)
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();
        $dispatcher->connect(Sequence::class, 'afterInvokeStep', function ($step) use ($bootstrap, $dispatcher) {
            if ($step->getIdentifier() === 'neos.flow:systemfilemonitor') {
                $assetsFileMonitor = FileMonitor::createFileMonitorAtBoot('Carbon_Include_Assets_Files', $bootstrap);
                /**
                 * @var PackageManager $packageManager
                 */
                $packageManager = $bootstrap->getEarlyInstance(PackageManager::class);
                foreach ($packageManager->getFlowPackages() as $packageKey => $package) {
                    $assetsPaths = [
                        $package->getResourcesPath() . 'Private/Templates/InlineAssets',
                        $package->getResourcesPath() . 'Public/Styles',
                        $package->getResourcesPath() . 'Public/Scripts',
                    ];
                    foreach ($assetsPaths as $assetsPath) {
                        if (is_dir($assetsPath)) {
                            $assetsFileMonitor->monitorDirectory($assetsPath, '.*\.css');
                            $assetsFileMonitor->monitorDirectory($assetsPath, '.*\.js');
                        }
                    }
                }
                $assetsFileMonitor->detectChanges();
                $assetsFileMonitor->shutdownObject();
            }

            if ($step->getIdentifier() === 'neos.flow:cachemanagement') {
                $cacheManager = $bootstrap->getEarlyInstance(CacheManager::class);
                $listener = new DynamicCssFileMonitorListener($cacheManager);
                $dispatcher->connect(FileMonitor::class, 'filesHaveChanged', $listener, 'flushDynamicAssetCacheOnFileChanges');
            }
        });
    }
}
