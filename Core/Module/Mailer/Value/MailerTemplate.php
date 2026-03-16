<?php

namespace Amora\Core\Module\Mailer\Value;

enum MailerTemplate: int
{
    case AccountVerification = 2000;
    case PasswordCreation = 2001;
    case PasswordReset = 2002;
}
