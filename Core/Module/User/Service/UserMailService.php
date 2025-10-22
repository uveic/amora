<?php

namespace Amora\Core\Module\User\Service;

use Amora\Core\Core;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Module\Mailer\Model\MailerItem;
use Amora\Core\Module\Mailer\Service\MailerService;
use Amora\Core\Module\Mailer\Value\MailerTemplate;
use Amora\Core\Module\User\DataLayer\UserDataLayer;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\User\Model\UserVerification;
use Amora\Core\Module\User\Value\VerificationType;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;
use DateTimeImmutable;

readonly class UserMailService
{
    public function __construct(
        private UserDataLayer $userDataLayer,
        private MailerService $mailerService,
    ) {
    }

    private function sendEmailAndDisablePreviousVerifications(
        User $user,
        UserVerification $verification,
        MailerItem $mailerItem
    ): bool {
        $resDisable = $this->userDataLayer->disableAllVerificationsForUserId(
            userId: $user->id,
            verificationType: $verification->type,
        );

        if (!$resDisable) {
            return false;
        }

        $resVerification = $this->userDataLayer->storeUserVerification($verification);

        if (!$resVerification) {
            return false;
        }

        $resStore = $this->mailerService->storeMail($mailerItem);
        return (bool)$resStore;
    }

    public function sendUpdateEmailVerificationEmail(User $user, string $emailToVerify): bool
    {
        $verificationIdentifier = $this->getUniqueVerificationIdentifier();
        $verification = new UserVerification(
            id: null,
            userId: $user->id,
            type: VerificationType::VerifyEmailAddress,
            email: $emailToVerify,
            createdAt: new DateTimeImmutable(),
            verifiedAt: null,
            verificationIdentifier: $verificationIdentifier,
            isEnabled: true
        );

        $mailerItem = $this->buildEmailUpdateVerificationEmail($user, $emailToVerify, $verificationIdentifier);

        return $this->sendEmailAndDisablePreviousVerifications($user, $verification, $mailerItem);
    }

    public function workflowSendVerificationEmail(
        User $user,
        string $emailToVerify,
        VerificationType $verificationType,
    ): bool {
        $res = $this->userDataLayer->getDb()->withTransaction(
            function () use ($user, $emailToVerify, $verificationType) {
                $res = $this->sendVerificationEmail(
                    user: $user,
                    emailToVerify: $emailToVerify,
                    verificationType: $verificationType,
                );

                if (!$res) {
                    return new Feedback(false);
                }

                return new Feedback(true);
            }
        );

        return $res->isSuccess;
    }

    public function sendVerificationEmail(
        User $user,
        string $emailToVerify,
        VerificationType $verificationType,
    ): bool {
        $verificationIdentifier = $this->getUniqueVerificationIdentifier();
        $verification = new UserVerification(
            id: null,
            userId: $user->id,
            type: $verificationType,
            email: $emailToVerify,
            createdAt: new DateTimeImmutable(),
            verifiedAt: null,
            verificationIdentifier: $verificationIdentifier,
            isEnabled: true
        );

        $mailerItem = $this->buildVerificationEmail(
            $user,
            $emailToVerify,
            $verificationIdentifier
        );

        $resEmail = $this->sendEmailAndDisablePreviousVerifications(
            $user,
            $verification,
            $mailerItem
        );

        if (!$resEmail) {
            return false;
        }

        return true;
    }

    public function workflowSendPasswordResetEmail(User $user): bool
    {
        $res = $this->userDataLayer->getDb()->withTransaction(
            function () use ($user) {
                $verificationIdentifier = $this->getUniqueVerificationIdentifier();
                $mailerItem = $this->buildPasswordResetEmail($user, $verificationIdentifier);

                $verification = new UserVerification(
                    id: null,
                    userId: $user->id,
                    type: VerificationType::PasswordReset,
                    email: null,
                    createdAt: new DateTimeImmutable(),
                    verifiedAt: null,
                    verificationIdentifier: $verificationIdentifier,
                    isEnabled: true,
                );

                $resEmail = $this->sendEmailAndDisablePreviousVerifications(
                    $user,
                    $verification,
                    $mailerItem
                );

                if (!$resEmail) {
                    return new Feedback(false);
                }

                return new Feedback(true);
            }
        );

        return $res->isSuccess;
    }

    public function workflowSendPasswordCreationEmail(User $user): bool
    {
        $res = $this->userDataLayer->getDb()->withTransaction(
            function () use ($user) {
                $res = $this->sendPasswordCreationEmail($user);
                return new Feedback($res);
            }
        );

        return $res->isSuccess;
    }

    public function sendPasswordCreationEmail(User $user): bool
    {
        $verificationIdentifier = $this->getUniqueVerificationIdentifier();
        $verification = new UserVerification(
            id: null,
            userId: $user->id,
            type: VerificationType::PasswordCreation,
            email: null,
            createdAt: new DateTimeImmutable(),
            verifiedAt: null,
            verificationIdentifier: $verificationIdentifier,
            isEnabled: true
        );

        $mailerItem = $this->buildPasswordCreationEmail(
            $user,
            $verificationIdentifier
        );

        $resEmail = $this->sendEmailAndDisablePreviousVerifications(
            user: $user,
            verification: $verification,
            mailerItem: $mailerItem
        );

        if (!$resEmail) {
            return false;
        }

        return true;
    }

    private function buildPasswordResetEmail(User $user, string $verificationIdentifier): MailerItem
    {
        $localisationUtil = Core::getLocalisationUtil($user->language, false);
        $linkUrl = UrlBuilderUtil::buildPublicPasswordResetUrl(
            language: $user->language,
            verificationIdentifier: $verificationIdentifier,
        );
        $siteName = $localisationUtil->getValue('siteName');
        $emailSubject = sprintf(
            $localisationUtil->getValue('emailPasswordChangeSubject'),
            $siteName
        );
        $emailContent = sprintf(
            $localisationUtil->getValue('emailPasswordChangeContent'),
            $linkUrl,
            $siteName
        );

        return new MailerItem(
            id: null,
            template: MailerTemplate::PasswordReset,
            replyToEmailAddress: Core::getConfig()->mailer->replyTo->email,
            senderName: null,
            receiverEmailAddress: $user->email,
            receiverName: $user->name,
            subject: $emailSubject,
            contentHtml: $emailContent,
            fieldsJson: null,
            createdAt: new DateTimeImmutable(),
        );
    }

    private function buildVerificationEmail(
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
            $emailSubject = sprintf(
                $localisationUtil->getValue('emailConfirmationSubject'),
                $siteName
            );
            $emailContent = sprintf(
                $localisationUtil->getValue('emailConfirmationContent'),
                $linkUrl,
                $siteName
            );
        }

        return new MailerItem(
            id: null,
            template: MailerTemplate::AccountVerification,
            replyToEmailAddress: Core::getConfig()->mailer->replyTo->email,
            senderName: null,
            receiverEmailAddress: $emailToVerify,
            receiverName: $user->name,
            subject: $emailSubject,
            contentHtml: $emailContent,
            fieldsJson: null,
            createdAt: new DateTimeImmutable(),
        );
    }

    private function buildEmailUpdateVerificationEmail(
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
            template: MailerTemplate::AccountVerification,
            replyToEmailAddress: Core::getConfig()->mailer->replyTo->email,
            senderName: null,
            receiverEmailAddress: $emailToVerify,
            receiverName: $user->name,
            subject: $emailSubject,
            contentHtml: $emailContent,
            fieldsJson: null,
            createdAt: new DateTimeImmutable(),
        );
    }

    private function buildPasswordCreationEmail(
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
            template: MailerTemplate::PasswordCreation,
            replyToEmailAddress: Core::getConfig()->mailer->replyTo->email,
            senderName: null,
            receiverEmailAddress: $user->email,
            receiverName: $user->name,
            subject: $emailSubject,
            contentHtml: $emailContent,
            fieldsJson: null,
            createdAt: new DateTimeImmutable(),
        );
    }

    private function getUniqueVerificationIdentifier(): string
    {
        do {
            $verificationIdentifier = StringUtil::generateRandomString(64);
            $verification = $this->userDataLayer->getUserVerification(verificationIdentifier: $verificationIdentifier);
        } while ($verification);

        return $verificationIdentifier;
    }
}
