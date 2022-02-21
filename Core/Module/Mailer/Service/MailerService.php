<?php

namespace Amora\Core\Module\Mailer\Service;

use Amora\Core\Module\Mailer\Datalayer\MailerDataLayer;
use Amora\Core\Module\Mailer\Model\MailerItem;

class MailerService
{
    public function __construct(
        private MailerDataLayer $mailerDataLayer,
    ) {}

    public function storeMail(MailerItem $mailerItem): MailerItem
    {
        return $this->mailerDataLayer->storeMail($mailerItem);
    }
}
