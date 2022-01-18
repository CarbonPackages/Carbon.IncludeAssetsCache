<?php
namespace Carbon\IncludeAssetsCache\Command;

use Carbon\IncludeAssetsCache\Service\CacheService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Fusion\Core\Cache\ContentCache;

/**
 *
 * @Flow\Scope("singleton")
 */
class CacheCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var ContentCache
     */
    protected $contentCache;

    public function clearIncludeAssetsCommand() {
        \Neos\Flow\var_dump($this->contentCache->flushByTag('Carbon_Include_Assets'));
    }
}