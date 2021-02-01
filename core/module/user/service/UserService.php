<?php

namespace uve\core\module\user\service;

use DateTime;
use DateTimeZone;
use uve\core\Logger;
use uve\core\model\response\UserFeedback;
use uve\core\module\user\datalayer\UserDataLayer;
use uve\core\module\user\model\User;
use uve\core\module\user\model\UserRegistrationRequest;
use uve\core\module\user\model\UserVerification;
use uve\core\module\user\value\VerificationType;
use uve\core\util\DateUtil;
use uve\core\util\StringUtil;
use uve\core\value\Language;

class UserService
{
    const USER_PASSWORD_MIN_LENGTH = 10;

    private UserDataLayer $userDataLayer;
    private Logger $logger;
    private SessionService $sessionService;
    private UserMailService $userMailService;

    public function __construct(
        Logger $logger,
        UserDataLayer $userDataLayer,
        SessionService $sessionService,
        UserMailService $userMailService
    ) {
        $this->userDataLayer = $userDataLayer;
        $this->logger = $logger;
        $this->sessionService = $sessionService;
        $this->userMailService = $userMailService;
    }

    public function storeUser(User $user): User
    {
        return $this->userDataLayer->createNewUser($user);
    }

    public function deleteUser(User $user): bool
    {
        return $this->userDataLayer->deleteUser($user);
    }

    private function updateUser(
        User $user,
        bool $newEmailAddress = false,
        bool $updateSessionTimezone = false
    ): ?User {
        $res = $this->userDataLayer->getDb()->withTransaction(
            function() use ($user, $newEmailAddress, $updateSessionTimezone) {
                $updatedUser = $this->userDataLayer->updateUser($user, $user->getId());

                if (empty($updatedUser)) {
                    return ['success' => false];
                }

                if ($newEmailAddress) {
                    $this->userMailService->buildAndSendVerificationEmail(
                        $user,
                        VerificationType::UPDATE_EMAIL_ADDRESS
                    );
                }

                if ($updateSessionTimezone) {
                    $this->sessionService->updateTimezoneForUserId(
                        $user->getId(),
                        $user->getTimezone()
                    );
                }

                return [
                    'success' => true,
                    'user' => $updatedUser
                ];
            }
        );

        return empty($res['success']) ? null : $res['user'];
    }

    public function verifyUser(string $email, string $unHashedPassword): ?User
    {
        $res = $this->userDataLayer->getUserForEmail($email);
        if (empty($res)) {
            return null;
        }

        $validPass = StringUtil::verifyPassword($unHashedPassword, $res->getPasswordHash());
        if (empty($validPass)) {
            return null;
        }

        return $res;
    }

    private function validateUpdateUserEndpoint(
        string $existingHashedPassword,
        int $existingUserId,
        ?string $email,
        ?int $languageId,
        ?string $timezone,
        ?string $currentPassword,
        ?string $newPassword,
        ?string $repeatPassword
    ): UserFeedback {
        if (isset($currentPassword) || isset($newPassword) || isset($repeatPassword)) {
            if (empty($currentPassword) || empty($newPassword) || empty($repeatPassword)) {
                return new UserFeedback(
                    true,
                    'Submitted payload not valid: one of the password fields is empty.'
                );
            }

            $validPass = StringUtil::verifyPassword($currentPassword, $existingHashedPassword);
            if (!$validPass) {
                return new UserFeedback(
                    true,
                    'O contrasinal actual non é válido.'
                );
            }

            if (strlen($newPassword) < self::USER_PASSWORD_MIN_LENGTH) {
                return new UserFeedback(
                    true,
                    'A lonxitude mínima do contrasinal son ' .
                    UserService::USER_PASSWORD_MIN_LENGTH .
                    ' caracteres. Corríxeo e volve a intentalo.'
                );
            }

            if ($newPassword !== $repeatPassword) {
                return new UserFeedback(
                    true,
                    'Os contrasinais novos non coinciden.'
                );
            }
        }

        if ($email) {
            if (!StringUtil::isEmailAddressValid($email)) {
                return new UserFeedback(
                    true,
                    'Correo electrónico non válido'
                );
            }

            $existingUser = $this->getUserForEmail($email);
            if (!empty($existingUser) && $existingUser->getId() !== $existingUserId) {
                return new UserFeedback(
                    true,
                    'O correo electrónico xa está a ser usado por outra conta.'
                );
            }
        }

        if (isset($timezone)
            && !in_array($timezone, DateTimeZone::listIdentifiers(DateTimeZone::ALL))
        ) {
            return new UserFeedback(
                true,
                'Timezone not valid'
            );
        }

        if (isset($languageId) && !in_array($languageId, array_column(Language::getAll(), 'id'))) {
            return new UserFeedback(
                true,
                'Language not supported'
            );
        }

        return new UserFeedback(false, null);
    }

    public function getListOfUsers(): array
    {
        return $this->userDataLayer->getAllUsers();
    }

    public function getUserForId(int $userId, $includeDisabled = false): ?User
    {
        return $this->userDataLayer->getUserForId($userId, $includeDisabled);
    }

    public function getUserForEmail(string $email, $includeDisabled = false): ?User
    {
        return $this->userDataLayer->getUserForEmail($email, $includeDisabled);
    }

    public function validateEmailAddressVerificationPage(string $verificationIdentifier): bool
    {
        $verification = $this->userDataLayer->getUserVerification(
            $verificationIdentifier,
            VerificationType::ACCOUNT
        );

        if (empty($verification)) {
            return false;
        }

        $user = $this->getUserForId($verification->getUserId());
        if (empty($user)) {
            return false;
        }

        if ($user->isVerified()) {
            return true;
        }

        if (!$verification->isEnabled()) {
            return false;
        }

        $verificationDate = new DateTime($verification->getCreatedAt());
        $now = new DateTime();
        $dateDiff = $now->diff($verificationDate);
        if ($dateDiff->days > 7) {
            return false;
        }

        return $this->userDataLayer->markUserAsVerified($verification->getUserId());
    }

    public function validatePasswordResetVerificationPage(
        string $verificationIdentifier
    ): ?UserVerification {
        $verification = $this->userDataLayer->getUserVerification(
            $verificationIdentifier,
            VerificationType::PASSWORD_RESET,
            true
        );

        if (empty($verification)) {
            return null;
        }

        $user = $this->getUserForId($verification->getUserId());
        if (empty($user)) {
            return null;
        }

        return $verification;
    }

    public function updatePassword(int $userId, string $newPassword): bool
    {
        return $this->userDataLayer->updatePassword(
            $userId,
            StringUtil::hashPassword($newPassword)
        );
    }

    public function workflowUpdateUser(
        User $existingUser,
        ?string $name = null,
        ?string $email = null,
        ?string $languageId = null,
        ?string $timezone = null,
        ?string $currentPassword = null,
        ?string $newPassword = null,
        ?string $repeatPassword = null,
        ?bool $isEnabled = null
    ): UserFeedback {
        $validation = $this->validateUpdateUserEndpoint(
            $existingUser->getPasswordHash(),
            $existingUser->getId(),
            $email,
            $languageId,
            $timezone,
            $currentPassword,
            $newPassword,
            $repeatPassword
        );

        if ($validation->isError()) {
            return $validation;
        }

        $hasEmailChanged = isset($email)
            && $existingUser->getEmail() !== StringUtil::normaliseEmail($email);
        $res = $this->updateUser(
            new User(
                $existingUser->getId(),
                $languageId ?? $existingUser->getLanguageId(),
                $existingUser->getRoleId(),
                $existingUser->getJourneyStatusId(),
                $existingUser->getCreatedAt(),
                DateUtil::getCurrentDateForMySql(),
                $email ? StringUtil::normaliseEmail($email) : $existingUser->getEmail(),
                $name ?? $existingUser->getName(),
                $newPassword
                    ? StringUtil::hashPassword($newPassword)
                    : $existingUser->getPasswordHash(),
                $existingUser->getBio(),
                isset($isEnabled) ? $isEnabled : $existingUser->isEnabled(),
                $hasEmailChanged ? false : $existingUser->isVerified(),
                $timezone ?? $existingUser->getTimezone(),
                $hasEmailChanged
                    ? $existingUser->getEmail()
                    : $existingUser->getPreviousEmailAddress()
            ),
            $hasEmailChanged,
            isset($timezone)
        );

        if (empty($res)) {
            return new UserFeedback(true, 'Error updating user');
        }

        return $validation;
    }

    public function storeRegistrationInviteRequest(
        string $email,
        int $languageId
    ): UserRegistrationRequest
    {
        $res = $this->userDataLayer->getUserRegistrationRequest(null, $email);
        if ($res) {
            return $res;
        }

        $requestCode = $this->getUniqueUserRegistrationRequestCode();
        return $this->userDataLayer->storeRegistrationInviteRequest(
            new UserRegistrationRequest(
                null,
                $email,
                $languageId,
                DateUtil::getCurrentDateForMySql(),
                null,
                $requestCode,
                null
            )
        );
    }

    private function getUniqueUserRegistrationRequestCode(): string
    {
        do {
            $code = StringUtil::getRandomString(64);
            $res = $this->userDataLayer->getUserRegistrationRequest($code);
        } while(!empty($res));

        return $code;
    }
}
