<?php

namespace Carbon\IncludeAssetsCache;

use Neos\Flow\Cache\CacheManager;
use Neos\Flow\Configuration\ConfigurationManager;
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
                $config = $this->getConfig($bootstrap);

                // Skip if file watcher is disabled
                if (!$config['enableFileWatcher']) {
                    return;
                }

                $assetsFileMonitor = FileMonitor::createFileMonitorAtBoot('Carbon_Include_Assets_Files', $bootstrap);
                /**
                 * @var PackageManager $packageManager
                 */
                $packageManager = $bootstrap->getEarlyInstance(PackageManager::class);

                $restrictToPackages = $config['restrictToPackages'];
                // Make sure we have an array
                if (is_string($restrictToPackages)) {
                    $restrictToPackages = [$restrictToPackages];
                }

                foreach ($packageManager->getFlowPackages() as $packageKey => $package) {
                    $resourcesPath = $package->getResourcesPath();

                    // Skip if package is not in the list of packages to watch
                    if ($restrictToPackages && !in_array($packageKey, $restrictToPackages)) {
                        continue;
                    }

                    foreach ($config['directories'] as $filetype => $directory) {

                        // Skip if a falsy value is set
                        if (!$directory) {
                            continue;
                        }

                        // Make sure we have an array
                        if (!is_array($directory)) {
                            $directory = [$directory];
                        }

                        foreach ($directory as $folder) {
                            // Skip if a falsy value is set
                            if (!$folder) {
                                continue;
                            }
                            $path = $resourcesPath . $folder;
                            if (is_dir($path)) {
                                $assetsFileMonitor->monitorDirectory($path, $filetype == 'ALL' ? null : sprintf('.*\.%s$', $filetype));
                            }
                        }
                    }
                }
                $assetsFileMonitor->detectChanges();
                $assetsFileMonitor->shutdownObject();
            }

            if ($step->getIdentifier() === 'neos.flow:cachemanagement') {
                $config = $this->getConfig($bootstrap);

                // Skip if a falsy value is set
                if (!$config['enableFileWatcher']) {
                    return;
                }

                $cacheManager = $bootstrap->getEarlyInstance(CacheManager::class);
                $listener = new DynamicCssFileMonitorListener($cacheManager);
                $dispatcher->connect(FileMonitor::class, 'filesHaveChanged', $listener, 'flushDynamicAssetCacheOnFileChanges');
            }
        });
    }

    /**
     * Get Configuration
     *
     * @param Bootstrap $bootstrap
     * @return array
     */
    protected function getConfig(Bootstrap $bootstrap): array
    {
        $configurationManager = $bootstrap->getEarlyInstance(ConfigurationManager::class);
        return $configurationManager->getConfiguration(
            ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
            'Carbon.IncludeAssetsCache'
        );
    }
}
