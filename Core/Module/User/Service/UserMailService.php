<?php

namespace Amora\Core\Module\User\Service;

use Amora\App\Util\AppEmailBuilderUtil;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Module\Mailer\Model\MailerItem;
use Amora\Core\Module\Mailer\Service\MailerService;
use Amora\Core\Module\User\DataLayer\UserDataLayer;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\User\Model\UserVerification;
use Amora\Core\Module\User\Value\VerificationType;
use Amora\Core\Util\StringUtil;
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

        $mailerItem = AppEmailBuilderUtil::buildEmailUpdateVerificationEmail(
            user: $user,
            emailToVerify: $emailToVerify,
            verificationIdentifier: $verificationIdentifier,
        );

        return $this->sendEmailAndDisablePreviousVerifications($user, $verification, $mailerItem);
    }

    public function workflowSendVerificationEmail(
        User $user,
        string $emailToVerify,
        VerificationType $verificationType,
    ): bool {
        $res = $this->userDataLayer->db->withTransaction(
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

        return $this->sendEmailAndDisablePreviousVerifications(
            user: $user,
            verification: new UserVerification(
                id: null,
                userId: $user->id,
                type: $verificationType,
                email: $emailToVerify,
                createdAt: new DateTimeImmutable(),
                verifiedAt: null,
                verificationIdentifier: $verificationIdentifier,
                isEnabled: true
            ),
            mailerItem: AppEmailBuilderUtil::buildVerificationEmail(
                user: $user,
                emailToVerify: $emailToVerify,
                verificationIdentifier: $verificationIdentifier,
            ),
        );
    }

    public function workflowSendPasswordResetEmail(User $user): bool
    {
        $res = $this->userDataLayer->db->withTransaction(
            function () use ($user) {
                $verificationIdentifier = $this->getUniqueVerificationIdentifier();

                $resEmail = $this->sendEmailAndDisablePreviousVerifications(
                    user: $user,
                    verification: new UserVerification(
                        id: null,
                        userId: $user->id,
                        type: VerificationType::PasswordReset,
                        email: null,
                        createdAt: new DateTimeImmutable(),
                        verifiedAt: null,
                        verificationIdentifier: $verificationIdentifier,
                        isEnabled: true,
                    ),
                    mailerItem: AppEmailBuilderUtil::buildPasswordResetEmail($user, $verificationIdentifier),
                );

                return new Feedback($resEmail);
            }
        );

        return $res->isSuccess;
    }

    public function workflowSendPasswordCreationEmail(User $user): bool
    {
        $res = $this->userDataLayer->db->withTransaction(
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

        return $this->sendEmailAndDisablePreviousVerifications(
            user: $user,
            verification: new UserVerification(
                id: null,
                userId: $user->id,
                type: VerificationType::PasswordCreation,
                email: null,
                createdAt: new DateTimeImmutable(),
                verifiedAt: null,
                verificationIdentifier: $verificationIdentifier,
                isEnabled: true
            ),
            mailerItem: AppEmailBuilderUtil::buildPasswordCreationEmail(
                user: $user,
                verificationIdentifier: $verificationIdentifier,
            ),
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
