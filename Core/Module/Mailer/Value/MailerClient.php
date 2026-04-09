<?php

namespace Amora\Core\Module\Mailer\Value;

enum MailerClient: int
{
    case SendGrid = 1;
    case Brevo = 2;
    case Lettermint = 3;
}
