<?php

namespace Carbon\IncludeAssetsCache\Command;

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

    public function clearIncludeAssetsCommand()
    {
        $numberOfEntries = $this->contentCache->flushByTag('Carbon_Include_Assets');

        if ($numberOfEntries === 0) {
            $this->outputFormatted('No entries found in the include assets cache.');
            return;
        }
        $entriesPlural = $numberOfEntries === 1 ? 'entry' : 'entries';
        $this->outputFormatted('<success><b>%s %s</b> removed from the include assets cache.</success>', [$numberOfEntries, $entriesPlural]);
    }
}
