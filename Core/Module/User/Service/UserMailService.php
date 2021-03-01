<?php

namespace Amora\Core\Module\User\Service;

use Amora\Core\Core;
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

    private function sendVerificationEmail(
        User $user,
        string $verificationIdentifier,
        int $verificationTypeId,
        string $linkUrl,
        string $emailSubject,
        string $emailContent
    ): bool {
        $res = $this->userDataLayer->getDb()->withTransaction(
            function () use (
                $user,
                $verificationIdentifier,
                $verificationTypeId,
                $linkUrl,
                $emailSubject,
                $emailContent
            ) {
                $this->userDataLayer->disableVerificationDataForUserId(
                    $user->getId(),
                    $verificationTypeId
                );
                $res = $this->userDataLayer->storeUserVerification(
                    new UserVerification(
                        null,
                        $user->getId(),
                        $verificationTypeId,
                        DateUtil::getCurrentDateForMySql(),
                        null,
                        $verificationIdentifier,
                        true
                    )
                );

                if (empty($res)) {
                    return ['success' => false];
                }

                $resMail = $this->mailerService->storeMail(
                    new MailerItem(
                        null,
                        MailerTemplate::ACCOUNT_VERIFICATION,
                        null,
                        null,
                        $user->getEmail(),
                        $user->getName(),
                        $emailSubject,
                        $emailContent,
                        null,
                        DateUtil::getCurrentDateForMySql()
                    )
                );

                return ['success' => $resMail ? true : false];
            }
        );

        return empty($res['success']) ? false : true;
    }

    public function buildAndSendVerificationEmail(User $user, int $verificationTypeId): bool
    {
        $verificationIdentifier = $this->getUniqueVerificationIdentifier();
        $localisationUtil = Core::getLocalisationUtil(
            Language::getIsoCodeForId($user->getLanguageId())
        );
        $linkUrl = UrlBuilderUtil::getBaseLinkUrl(Language::getIsoCodeForId($user->getLanguageId()))
            . '/user/verify/' . $verificationIdentifier;
        $siteName = Core::getLocalisationUtil(Language::getIsoCodeForId($user->getLanguageId()))
            ->getValue('siteName');

        $emailSubject = sprintf(
            $localisationUtil->getValue('emailVerificationSubject'),
            $siteName
        );
        $emailContent = sprintf(
            $localisationUtil->getValue('emailVerificationContent'),
            $linkUrl,
            $siteName
        );

        return $this->sendVerificationEmail(
            $user,
            $verificationIdentifier,
            $verificationTypeId,
            $linkUrl,
            $emailSubject,
            $emailContent
        );
    }

    public function sendPasswordResetEmail(User $user): bool
    {
        $verificationIdentifier = $this->getUniqueVerificationIdentifier();
        $localisationUtil = Core::getLocalisationUtil(
            Language::getIsoCodeForId($user->getLanguageId())
        );
        $linkUrl = UrlBuilderUtil::getBaseLinkUrl(Language::getIsoCodeForId($user->getLanguageId()))
            . '/user/reset/' . $verificationIdentifier;
        $siteName = Core::getLocalisationUtil(Language::getIsoCodeForId($user->getLanguageId()))
            ->getValue('siteName');
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

        return $this->sendVerificationEmail(
            $user,
            $verificationIdentifier,
            VerificationType::PASSWORD_RESET,
            $linkUrl,
            $emailSubject,
            $emailContent
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
