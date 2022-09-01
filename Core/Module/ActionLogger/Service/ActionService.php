<?php

namespace Amora\Core\Module\Action\Service;

use DateTimeImmutable;
use Throwable;
use Amora\Core\Core;
use Amora\Core\Util\Logger;
use Amora\Core\Entity\Request;
use Amora\Core\Module\Action\Datalayer\ActionDataLayer;
use Amora\Core\Module\Action\Model\Action;

class ActionService
{
    public function __construct(
        private Logger $logger,
        private ActionDataLayer $actionDataLayer,
    ) {}

    public function logAction(Request $request): void
    {
        try {
            if (!Core::getConfig()->isActionLoggingEnabled) {
                return;
            }

            $this->actionDataLayer->storeAction(
                new Action(
                    id: null,
                    userId: $request->session?->user->id,
                    sessionId: $request->session?->sessionId,
                    createdAt: new DateTimeImmutable(),
                    url: substr($request->getPath(), 0, 2000),
                    referrer: $request->referrer ? substr($request->referrer, 0, 2000) : null,
                    ip: $request->sourceIp,
                    userAgent: $request->userAgent ? substr($request->userAgent, 0, 255) : null,
                    clientLanguage: $request->clientLanguage ? substr($request->clientLanguage, 0, 255) : null,
                )
            );
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error logging action => '
                . PHP_EOL . $t->getMessage()
                . PHP_EOL . $t->getTraceAsString(),
            );
        }
    }
}
