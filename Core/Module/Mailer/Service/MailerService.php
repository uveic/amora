<?php

namespace Amora\Core\Module\Mailer\Service;

use Amora\Core\Module\Mailer\App\MailerApp;
use Amora\Core\Module\Mailer\DataLayer\MailerDataLayer;
use Amora\Core\Module\Mailer\Model\MailerItem;

class MailerService
{
    public function __construct(
        private readonly MailerDataLayer $mailerDataLayer,
        private readonly MailerApp $mailerApp,
        private readonly bool $sendMailSynchronously,
    ) {}

    public function storeMail(MailerItem $mailerItem): MailerItem
    {
        $storedItem = $this->mailerDataLayer->storeMail($mailerItem);

        if ($this->sendMailSynchronously) {
            $this->mailerApp->processMailItem($mailerItem);
        }

        return $storedItem;
    }
}
