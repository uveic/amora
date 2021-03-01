<?php

namespace Amora\Core\Module\Mailer\Service;

use Amora\Core\Logger;
use Amora\Core\Module\Mailer\Datalayer\MailerDataLayer;
use Amora\Core\Module\Mailer\Model\MailerItem;

class MailerService
{
    private MailerDataLayer $mailerDataLayer;
    private Logger $logger;

    public function __construct(
        Logger $logger,
        MailerDataLayer $mailerDataLayer
    ) {
        $this->mailerDataLayer = $mailerDataLayer;
        $this->logger = $logger;
    }

    public function storeMail(MailerItem $mailerItem): MailerItem
    {
        return $this->mailerDataLayer->storeMail($mailerItem);
    }
}
