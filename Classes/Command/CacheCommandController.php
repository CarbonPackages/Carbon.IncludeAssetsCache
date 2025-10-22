<?php

namespace Carbon\IncludeAssetsCache\Command;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Fusion\Core\Cache\ContentCache;
use Psr\Log\LoggerInterface;

#[Flow\Scope('singleton')]
class CacheCommandController extends CommandController
{
    #[Flow\Inject]
    protected ContentCache $contentCache;

    #[Flow\Inject('Carbon.IncludeAssetsCache:Logger', false)]
    protected LoggerInterface $logger;

    /**
     * Clear cache for include assets
     *
     * @return void
     */
    public function clearIncludeAssetsCommand()
    {
        $numberOfEntries = $this->contentCache->flushByTag('Carbon_Include_Assets');

        if ($numberOfEntries === 0) {
            $this->outputFormatted('No entries found in the include assets cache.');
            $this->logger->debug(
                'No entries found in the include assets cache.',
                LogEnvironment::fromMethodName(__METHOD__)
            );
            return;
        }
        $entriesPlural = $numberOfEntries === 1 ? 'entry' : 'entries';
        $this->outputFormatted('<success><b>%s %s</b> removed from the include assets cache.</success>', [$numberOfEntries, $entriesPlural]);
        $this->logger->info(
            sprintf(
                '%s %s removed from the include assets cache.',
                $numberOfEntries,
                $entriesPlural
            ),
            LogEnvironment::fromMethodName(__METHOD__)
        );
    }
}
