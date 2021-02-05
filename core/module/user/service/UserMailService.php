<?php

namespace uve\core\module\user\service;

use uve\core\Core;
use uve\core\Logger;
use uve\core\module\mailer\model\MailerItem;
use uve\core\module\mailer\service\MailerService;
use uve\core\module\mailer\value\MailerTemplate;
use uve\core\module\user\datalayer\UserDataLayer;
use uve\core\module\user\model\User;
use uve\core\module\user\model\UserVerification;
use uve\core\module\user\value\VerificationType;
use uve\core\util\DateUtil;
use uve\core\util\StringUtil;
use uve\core\value\Language;

class UserMailService
{
    private UserDataLayer $userDataLayer;
    private Logger $logger;
    private MailerService $mailerService;

    public function __construct(
        Logger $logger,
        UserDataLayer $userDataLayer,
        MailerService $mailerService
    ) {
        $this->userDataLayer = $userDataLayer;
        $this->logger = $logger;
        $this->mailerService = $mailerService;
    }

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

        $baseUrl = Core::getConfigValue('base_url');
        $linkUrl = $baseUrl . 'user/verify/' . $verificationIdentifier;
        $siteName = Core::getLocalisationUtil(Language::getIsoCodeForId($user->getLanguageId()))
            ->getValue('siteName');

        $emailSubject = 'Welcome to ' . $siteName . '! Confirm Your Email';
        $emailContent = '<h2>Welcome!</h2>' .
            '<p>By clicking on the following link, you are confirming your email address.</p>' .
            '<p><a href="' . $linkUrl . '">Confirm Your Email</a></p>' .
            '<p>If you’re having trouble with the button above, copy and paste the URL below into your web browser.</p>' .
            '<p>' . $linkUrl . '</p>' .
            '<p>Thanks,<br>' . $siteName . '</p>';

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

        $baseUrl = Core::getConfigValue('base_url');
        $linkUrl = $baseUrl . 'user/reset/' . $verificationIdentifier;
        $siteName = Core::getLocalisationUtil(Language::getIsoCodeForId($user->getLanguageId()))
            ->getValue('siteName');
        $emailSubject = $siteName . ' Password Reset';
        $emailContent = '<p>Hi there,</p>' .
            '<p>We received a request to change the password for your ' . $siteName . ' account.</p>' .
            '<p>If you did not make this request, just ignore this email. Otherwise, please click the link below to reset your password:</p>' .
            '<p><a href="' . $linkUrl . '">Change Password</a></p>' .
            '<p>You can also copy and paste this URL into your web browser:</p>' .
            '<p>' . $linkUrl . '</p>' .
            '<p>Love,<br>' . $siteName . '</p>';

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
