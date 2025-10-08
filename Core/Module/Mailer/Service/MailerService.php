<?php

namespace Amora\Core\Module\Mailer\Service;

use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Module\Mailer\App\MailerApp;
use Amora\Core\Module\Mailer\DataLayer\MailerDataLayer;
use Amora\Core\Module\Mailer\Model\MailerItem;

readonly class MailerService
{
    public function __construct(
        private MailerDataLayer $mailerDataLayer,
        private MailerApp $mailerApp,
        private bool $sendMailSynchronously,
    ) {}

    public function storeMail(MailerItem $mailerItem): ?MailerItem
    {
        $storedItem = $this->mailerDataLayer->storeMail($mailerItem);
        if (!$storedItem) {
            return null;
        }

        if ($this->sendMailSynchronously) {
            $res = $this->mailerApp->processMailItem(
                item: $storedItem,
                updateProcessedAt: true,
            );

            if (!$res) {
                return null;
            }
        }

        return $storedItem;
    }

    public function filterMailerItemBy(
        array $ids = [],
        array $templateIds = [],
        ?bool $hasError = null,
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->mailerDataLayer->filterMailerItemBy(
            ids: $ids,
            templateIds: $templateIds,
            hasError: $hasError,
            queryOptions: $queryOptions,
        );
    }

    public function getMailerItemForId(int $id): ?MailerItem
    {
        $res = $this->filterMailerItemBy(ids: [$id]);
        return $res[0] ?? null;
    }
}
