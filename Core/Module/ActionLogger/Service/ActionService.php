<?php

namespace Amora\Core\Module\Action\Service;

use Throwable;
use Amora\Core\Core;
use Amora\Core\Logger;
use Amora\Core\Model\Request;
use Amora\Core\Module\Action\Datalayer\ActionDataLayer;
use Amora\Core\Module\Action\Model\Action;
use Amora\Core\Util\DateUtil;

class ActionService
{
    private ActionDataLayer $actionDataLayer;
    private Logger $logger;

    public function __construct(
        Logger $logger,
        ActionDataLayer $actionDataLayer
    ) {
        $this->actionDataLayer = $actionDataLayer;
        $this->logger = $logger;
    }

    public function logAction(Request $request): void
    {
        try {
            $logEnabled = Core::getConfigValue('actionLoggerEnabled');
            if (!$logEnabled) {
                return;
            }

            $session = $request->session;

            $this->storeActionFromValues(
                url: substr($request->getPath(), 0, 2000),
                referrer: $request->referrer ? substr($request->referrer, 0, 2000) : null,
                userId: $session?->user->id,
                sessionId: $session?->sessionId,
                ip: $request->sourceIp,
                userAgent: $request->userAgent ? substr($request->userAgent, 0, 255) : null,
                clientLanguage: $request->clientLanguage ? substr($request->clientLanguage, 0, 255) : null,
            );
        } catch (Throwable $t) {
            $this->logger->logError('Error logging action');
        }
    }

    private function storeActionFromValues(
        string $url,
        ?string $referrer,
        ?int $userId,
        ?string $sessionId,
        ?string $ip,
        ?string $userAgent,
        ?string $clientLanguage = null
    ): Action {
        return $this->actionDataLayer->storeAction(
            new Action(
                null,
                $userId,
                $sessionId,
                DateUtil::getCurrentDateForMySql(),
                $url,
                $referrer,
                $ip,
                $userAgent,
                $clientLanguage
            )
        );
    }
}
