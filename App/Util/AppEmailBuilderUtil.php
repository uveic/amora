<?php

namespace Amora\App\Util;

use Amora\Core\Module\Mailer\Model\MailerItem;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Util\EmailBuilderUtil;

final class AppEmailBuilderUtil
{
    public static function buildPasswordCreationEmail(
        User $user,
        string $verificationIdentifier,
    ): MailerItem {
        return EmailBuilderUtil::buildPasswordCreationEmail(
            user: $user,
            verificationIdentifier: $verificationIdentifier,
        );
    }

    public static function buildPasswordResetEmail(User $user, string $verificationIdentifier): MailerItem
    {
        return EmailBuilderUtil::buildPasswordResetEmail(user: $user, verificationIdentifier: $verificationIdentifier);
    }

    public static function buildVerificationEmail(
        User $user,
        string $emailToVerify,
        string $verificationIdentifier,
    ): MailerItem {
        return EmailBuilderUtil::buildVerificationEmail(
            user: $user,
            emailToVerify: $emailToVerify,
            verificationIdentifier: $verificationIdentifier,
        );
    }

    public static function buildEmailUpdateVerificationEmail(
        User $user,
        string $emailToVerify,
        string $verificationIdentifier
    ): MailerItem {
        return EmailBuilderUtil::buildEmailUpdateVerificationEmail(
            user: $user,
            emailToVerify: $emailToVerify,
            verificationIdentifier: $verificationIdentifier,
        );
    }
}
