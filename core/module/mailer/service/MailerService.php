<?php

namespace uve\core\module\mailer\service;

use uve\core\Logger;
use uve\core\module\mailer\datalayer\MailerDataLayer;
use uve\core\module\mailer\model\MailerItem;

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
