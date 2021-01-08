<?php

namespace uve\core\module\action\service;

use Throwable;
use uve\core\Core;
use uve\core\Logger;
use uve\core\model\Request;
use uve\core\module\action\datalayer\ActionDataLayer;
use uve\core\module\action\model\Action;
use uve\core\module\user\model\Session;
use uve\core\util\DateUtil;

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

    public function logAction(Request $request, ?Session $session = null): void
    {
        try {
            $logEnabled = Core::getConfigValue('action_logger_enabled');
            if (!$logEnabled) {
                return;
            }

            $this->storeActionFromValues(
                substr($request->getPath(), 0, 2000),
                substr($request->getReferrer(), 0, 2000),
                $session ? $session->getUser()->getId() : null,
                $session ? $session->getSessionId() : null,
                $request->getSourceIp(),
                substr($request->getUserAgent(), 0, 255),
                substr($request->getClientLanguage(), 0, 255),
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
