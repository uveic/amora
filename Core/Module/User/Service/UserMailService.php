<?php

namespace Amora\Core\Module\User\Service;

use Amora\Core\Core;
use Amora\Core\Database\Model\TransactionResponse;
use Amora\Core\Logger;
use Amora\Core\Module\Mailer\Model\MailerItem;
use Amora\Core\Module\Mailer\Service\MailerService;
use Amora\Core\Module\Mailer\Value\MailerTemplate;
use Amora\Core\Module\User\Datalayer\UserDataLayer;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\User\Model\UserVerification;
use Amora\Core\Module\User\Value\VerificationType;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\Language;

class UserMailService
{
    public function __construct(
        private Logger $logger,
        private UserDataLayer $userDataLayer,
        private MailerService $mailerService,
    ) {}

    private function sendEmailAndDisablePreviousVerifications(
        User $user,
        UserVerification $verification,
        MailerItem $mailerItem
    ): bool {
        $this->userDataLayer->disableVerificationDataForUserId($user->getId());
        $res = $this->userDataLayer->storeUserVerification($verification);

        if (empty($res)) {
            return false;
        }

        $res2 = $this->mailerService->storeMail($mailerItem);
        return empty($res2) ? false : true;
    }

    public function sendUpdateEmailVerificationEmail(User $user, string $emailToVerify): bool
    {
        $verificationIdentifier = $this->getUniqueVerificationIdentifier();
        $verification = new UserVerification(
            id: null,
            userId: $user->getId(),
            typeId: VerificationType::EMAIL_ADDRESS,
            email: $emailToVerify,
            createdAt: DateUtil::getCurrentDateForMySql(),
            verifiedAt: null,
            verificationIdentifier: $verificationIdentifier,
            isEnabled: true
        );

        $mailerItem = $this->buildEmailUpdateVerificationEmail($user, $emailToVerify, $verificationIdentifier);

        return $this->sendEmailAndDisablePreviousVerifications($user, $verification, $mailerItem);
    }

    public function sendVerificationEmail(User $user, string $emailToVerify): bool
    {
        $res = $this->userDataLayer->getDb()->withTransaction(
            function () use ($user, $emailToVerify) {
                $verificationIdentifier = $this->getUniqueVerificationIdentifier();
                $verification = new UserVerification(
                    id: null,
                    userId: $user->getId(),
                    typeId: VerificationType::EMAIL_ADDRESS,
                    email: $emailToVerify,
                    createdAt: DateUtil::getCurrentDateForMySql(),
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
                    return new TransactionResponse(false);
                }

                return new TransactionResponse(true);
            }
        );

        return $res->isSuccess();
    }

    public function sendPasswordResetEmail(User $user): bool
    {
        $res = $this->userDataLayer->getDb()->withTransaction(
            function () use ($user) {
                $verificationIdentifier = $this->getUniqueVerificationIdentifier();
                $mailerItem = $this->buildPasswordResetEmail($user, $verificationIdentifier);

                $verification = new UserVerification(
                    null,
                    $user->getId(),
                    VerificationType::PASSWORD_RESET,
                    null,
                    DateUtil::getCurrentDateForMySql(),
                    null,
                    $verificationIdentifier,
                    true
                );

                $resEmail = $this->sendEmailAndDisablePreviousVerifications(
                    $user,
                    $verification,
                    $mailerItem
                );

                if (!$resEmail) {
                    return new TransactionResponse(false);
                }

                return new TransactionResponse(true);
            }
        );

        return $res->isSuccess();
    }

    public function sendPasswordCreationEmail(User $user): bool
    {
        $verificationIdentifier = $this->getUniqueVerificationIdentifier();
        $verification = new UserVerification(
            id: null,
            userId: $user->getId(),
            typeId: VerificationType::PASSWORD_CREATION,
            email: null,
            createdAt: DateUtil::getCurrentDateForMySql(),
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
        $languageIsoCode = Language::getIsoCodeForId($user->getLanguageId());
        $localisationUtil = Core::getLocalisationUtil($languageIsoCode, false);
        $linkUrl = UrlBuilderUtil::getPublicPasswordResetUrl(
            languageIsoCode: $languageIsoCode,
            verificationIdentifier: $verificationIdentifier,
        );
        $siteName = $localisationUtil->getValue('siteName');
        $emailSubject = sprintf(
            $localisationUtil->getValue('emailPasswordChangeSubject'),
            $siteName
        );
        $emailContent = sprintf(
            $localisationUtil->getValue('emailPasswordChangeContent'),
            $siteName,
            $linkUrl,
            $siteName
        );

        return new MailerItem(
            null,
            MailerTemplate::PASSWORD_RESET,
            null,
            null,
            $user->getEmail(),
            $user->getName(),
            $emailSubject,
            $emailContent,
            null,
            DateUtil::getCurrentDateForMySql()
        );
    }

    private function buildVerificationEmail(
        User $user,
        string $emailToVerify,
        string $verificationIdentifier
    ): MailerItem {
        $languageIsoCode = Language::getIsoCodeForId($user->getLanguageId());
        $localisationUtil = Core::getLocalisationUtil($languageIsoCode, false);
        $linkUrl = UrlBuilderUtil::getPublicVerificationEmailUrl(
            languageIsoCode: $languageIsoCode,
            verificationIdentifier: $verificationIdentifier,
        );
        $siteName = $localisationUtil->getValue('siteName');

        $emailSubject = sprintf(
            $localisationUtil->getValue('emailConfirmationSubject'),
            $siteName
        );
        $emailContent = sprintf(
            $localisationUtil->getValue('emailConfirmationContent'),
            $linkUrl,
            $siteName
        );

        return new MailerItem(
            id: null,
            templateId: MailerTemplate::ACCOUNT_VERIFICATION,
            replyToEmailAddress: null,
            senderName: null,
            receiverEmailAddress: $emailToVerify,
            receiverName: $user->getName(),
            subject: $emailSubject,
            contentHtml: $emailContent,
            fieldsJson: null,
            createdAt: DateUtil::getCurrentDateForMySql()
        );
    }

    private function buildEmailUpdateVerificationEmail(
        User $user,
        string $emailToVerify,
        string $verificationIdentifier
    ): MailerItem {
        $languageIsoCode = Language::getIsoCodeForId($user->getLanguageId());
        $localisationUtil = Core::getLocalisationUtil($languageIsoCode, false);
        $linkUrl = UrlBuilderUtil::getPublicEmailUpdateUrl(
            languageIsoCode: $languageIsoCode,
            verificationIdentifier: $verificationIdentifier
        );
        $siteName = $localisationUtil->getValue('siteName');

        $emailSubject = $localisationUtil->getValue('emailUpdateVerificationSubject');
        $emailContent = sprintf(
            $localisationUtil->getValue('emailUpdateVerificationContent'),
            $siteName,
            $linkUrl,
            $siteName,
        );

        return new MailerItem(
            id: null,
            templateId: MailerTemplate::ACCOUNT_VERIFICATION,
            replyToEmailAddress: null,
            senderName: null,
            receiverEmailAddress: $emailToVerify,
            receiverName: $user->getName(),
            subject: $emailSubject,
            contentHtml: $emailContent,
            fieldsJson: null,
            createdAt: DateUtil::getCurrentDateForMySql()
        );
    }

    private function buildPasswordCreationEmail(
        User $user,
        string $verificationIdentifier
    ): MailerItem {
        $languageIsoCode = Language::getIsoCodeForId($user->getLanguageId());
        $localisationUtil = Core::getLocalisationUtil($languageIsoCode, false);
        $linkUrl = UrlBuilderUtil::getPublicCreatePasswordUrl(
            languageIsoCode: $languageIsoCode,
            verificationIdentifier: $verificationIdentifier,
        );
        $siteName = $localisationUtil->getValue('siteName');

        $emailSubject = sprintf(
            $localisationUtil->getValue('emailPasswordCreationSubject'),
            $siteName
        );
        $emailContent = sprintf(
            $localisationUtil->getValue('emailPasswordCreationContent'),
            $user->getName(),
            $siteName,
            $user->getEmail(),
            $linkUrl,
            $siteName,
        );

        return new MailerItem(
            id: null,
            templateId: MailerTemplate::PASSWORD_CREATION,
            replyToEmailAddress: null,
            senderName: null,
            receiverEmailAddress: $user->getEmail(),
            receiverName: $user->getName(),
            subject: $emailSubject,
            contentHtml: $emailContent,
            fieldsJson: null,
            createdAt: DateUtil::getCurrentDateForMySql()
        );
    }

    private function getUniqueVerificationIdentifier(): string
    {
        do {
            $verificationIdentifier = StringUtil::getRandomString(64);
            $verification = $this->userDataLayer->getUserVerification($verificationIdentifier);
        } while(!empty($verification));

        return $verificationIdentifier;
    }
}
