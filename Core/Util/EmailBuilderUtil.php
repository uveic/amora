<?php

namespace Amora\Core\Util;

use Amora\Core\Core;
use Amora\Core\Module\Mailer\Model\MailerItem;
use Amora\Core\Module\Mailer\Value\MailerTemplate;
use Amora\Core\Module\User\Model\User;
use DateTimeImmutable;

final readonly class EmailBuilderUtil
{
    public static function buildPasswordCreationEmail(
        User $user,
        string $verificationIdentifier,
    ): MailerItem {
        $localisationUtil = Core::getLocalisationUtil($user->language, false);
        $linkUrl = UrlBuilderUtil::buildPublicCreatePasswordUrl(
            language: $user->language,
            verificationIdentifier: $verificationIdentifier,
        );
        $siteName = $localisationUtil->getValue('siteName');

        $emailSubject = $localisationUtil->getValue('emailPasswordCreationSubject');
        $emailContent = sprintf(
            $localisationUtil->getValue('emailPasswordCreationContent'),
            $user->name,
            $user->email,
            $linkUrl,
            $siteName,
        );

        return new MailerItem(
            id: null,
            userId: $user->id,
            template: MailerTemplate::PasswordCreation,
            replyToEmailAddress: Core::getConfig()->mailer->replyTo?->email,
            senderEmailAddress: Core::getConfig()->mailer->from->email,
            senderName: null,
            receiverEmailAddress: $user->email,
            receiverName: $user->name,
            subject: $emailSubject,
            contentHtml: $emailContent,
            fieldsJson: null,
            createdAt: new DateTimeImmutable(),
        );
    }

    public static function buildPasswordResetEmail(User $user, string $verificationIdentifier): MailerItem
    {
        $localisationUtil = Core::getLocalisationUtil($user->language, false);
        $linkUrl = UrlBuilderUtil::buildPublicPasswordResetUrl(
            language: $user->language,
            verificationIdentifier: $verificationIdentifier,
        );
        $siteName = $localisationUtil->getValue('siteName');
        $emailSubject = $localisationUtil->getValue('emailPasswordChangeSubject');
        $emailContent = sprintf(
            $localisationUtil->getValue('emailPasswordChangeContent'),
            $linkUrl,
            $siteName
        );

        return new MailerItem(
            id: null,
            userId: $user->id,
            template: MailerTemplate::PasswordReset,
            replyToEmailAddress: Core::getConfig()->mailer->replyTo?->email,
            senderEmailAddress: Core::getConfig()->mailer->from->email,
            senderName: null,
            receiverEmailAddress: $user->email,
            receiverName: $user->name,
            subject: $emailSubject,
            contentHtml: $emailContent,
            fieldsJson: null,
            createdAt: new DateTimeImmutable(),
        );
    }

    public static function buildVerificationEmail(
        User $user,
        string $emailToVerify,
        string $verificationIdentifier,
    ): MailerItem {
        $localisationUtil = Core::getLocalisationUtil($user->language, false);
        $linkUrl = UrlBuilderUtil::buildPublicVerificationEmailUrl(
            language: $user->language,
            verificationIdentifier: $verificationIdentifier,
        );
        $siteName = $localisationUtil->getValue('siteName');

        if ($user->changeEmailAddressTo) {
            $emailSubject = sprintf(
                $localisationUtil->getValue('emailUpdateVerificationSubject'),
                $siteName
            );
            $emailContent = sprintf(
                $localisationUtil->getValue('emailUpdateVerificationContent'),
                $linkUrl,
                $siteName
            );
        } else {
            $emailSubject = $localisationUtil->getValue('emailConfirmationSubject');
            $emailContent = sprintf(
                $localisationUtil->getValue('emailConfirmationContent'),
                $linkUrl,
                $siteName
            );
        }

        return new MailerItem(
            id: null,
            userId: $user->id,
            template: MailerTemplate::AccountVerification,
            replyToEmailAddress: Core::getConfig()->mailer->replyTo?->email,
            senderEmailAddress: Core::getConfig()->mailer->from->email,
            senderName: null,
            receiverEmailAddress: $emailToVerify,
            receiverName: $user->name,
            subject: $emailSubject,
            contentHtml: $emailContent,
            fieldsJson: null,
            createdAt: new DateTimeImmutable(),
        );
    }

    public static function buildEmailUpdateVerificationEmail(
        User $user,
        string $emailToVerify,
        string $verificationIdentifier
    ): MailerItem {
        $localisationUtil = Core::getLocalisationUtil($user->language, false);
        $linkUrl = UrlBuilderUtil::buildPublicEmailUpdateUrl(
            language: $user->language,
            verificationIdentifier: $verificationIdentifier,
        );
        $siteName = $localisationUtil->getValue('siteName');

        $emailSubject = $localisationUtil->getValue('emailUpdateVerificationSubject');
        $emailContent = sprintf(
            $localisationUtil->getValue('emailUpdateVerificationContent'),
            $linkUrl,
            $siteName,
        );

        return new MailerItem(
            id: null,
            userId: $user->id,
            template: MailerTemplate::AccountVerification,
            replyToEmailAddress: Core::getConfig()->mailer->replyTo?->email,
            senderEmailAddress: Core::getConfig()->mailer->from->email,
            senderName: null,
            receiverEmailAddress: $emailToVerify,
            receiverName: $user->name,
            subject: $emailSubject,
            contentHtml: $emailContent,
            fieldsJson: null,
            createdAt: new DateTimeImmutable(),
        );
    }
}
