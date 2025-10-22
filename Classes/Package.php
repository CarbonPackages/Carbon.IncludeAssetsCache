<?php

namespace Carbon\IncludeAssetsCache;

use Neos\Flow\Cache\CacheManager;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Core\Booting\Sequence;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Monitor\FileMonitor;
use Neos\Flow\Package\Package as BasePackage;
use Neos\Flow\Package\PackageManager;
use Neos\Utility\Files;

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
                $configFromSettings = $this->getConfig($bootstrap);
                $packageConfig = $configFromSettings['Packages'] ?? null;
                $defaultPathConfig = $configFromSettings['Default']['Path']['Cached'] ?? null;
                $enabled = $configFromSettings['Cache']['enableFileMonitoring'] ?? false;
                $monitorFileExtensions = $configFromSettings['Cache']['monitorFiles'] ?? [];
                $monitorFileExtensions = array_keys(array_filter($monitorFileExtensions));

                if (!$enabled || empty($packageConfig) || empty($defaultPathConfig) || !is_array($defaultPathConfig)) {
                    return;
                }

                $packagesConfigToWatch = [];
                // Gather all config who has Cached set to true
                foreach ($packageConfig as $key => $config) {
                    $cached = $config['Cached'] ?? false;
                    $packageName = $config['Package'] ?? null;
                    if ($cached === true && !empty($packageName)) {
                        if (isset($packagesConfigToWatch[$packageName])) {
                            // Merge with existing config
                            $packagesConfigToWatch[$packageName] = array_merge_recursive($packagesConfigToWatch[$packageName], $config);
                            continue;
                        }
                        $packagesConfigToWatch[$packageName] = $config;
                    }
                }

                // No packages to watch
                if (empty($packagesConfigToWatch)) {
                    return;
                }

                $assetsFileMonitor = FileMonitor::createFileMonitorAtBoot('Carbon_Include_Assets_Files', $bootstrap);
                /**
                 * @var PackageManager $packageManager
                 */
                $packageManager = $bootstrap->getEarlyInstance(PackageManager::class);

                $restrictToPackages = array_keys($packagesConfigToWatch);
                foreach ($packageManager->getFlowPackages() as $packageKey => $package) {
                    $resourcesPath = $package->getResourcesPath();

                    // Skip if package is not in the list of packages to watch
                    if (!in_array($packageKey, $restrictToPackages)) {
                        continue;
                    }

                    // Get the path configuration for this package
                    $customPathConfig = $packagesConfigToWatch[$packageKey]['Path']['Cached'] ?? [];
                    $pathConfig = array_merge_recursive($defaultPathConfig, $customPathConfig);

                    // There are two types of path configurations: Inline and File
                    foreach ($pathConfig as $directory) {
                        $directory = trim($directory, '/');
                        $path = Files::getNormalizedPath(Files::getUnixStylePath($resourcesPath . $directory));
                        if (!is_dir($path)) {
                            continue;
                        }
                        if (empty($monitorFileExtensions)) {
                            $assetsFileMonitor->monitorDirectory($path);
                            continue;
                        }

                        foreach ($monitorFileExtensions as $extension) {
                            $assetsFileMonitor->monitorDirectory($path, sprintf('.*\.%s$', $extension));
                        }
                    }
                }
                $assetsFileMonitor->detectChanges();
                $assetsFileMonitor->shutdownObject();
            }

            if ($step->getIdentifier() === 'neos.flow:cachemanagement') {
                $cacheManager = $bootstrap->getEarlyInstance(CacheManager::class);
                $listener = new DynamicFileMonitorListener($cacheManager);
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
    protected function getConfig(Bootstrap $bootstrap): ?array
    {
        $configurationManager = $bootstrap->getEarlyInstance(ConfigurationManager::class);
        return [
            'Packages' => $configurationManager->getConfiguration(
                ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
                'Carbon.IncludeAssets.Packages'
            ),
            'Default' => $configurationManager->getConfiguration(
                ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
                'Carbon.IncludeAssets.Default'
            ),
            'Cache' => $configurationManager->getConfiguration(
                ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
                'Carbon.IncludeAssetsCache'
            ),
        ];
    }
}
