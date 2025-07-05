<?php

namespace Amora\Core\Module\User\Service;

use Amora\App\Value\AppUserRole;
use Amora\Core\Core;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Module\User\Value\UserJourneyStatus;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Module\User\Value\UserStatus;
use Amora\Core\Util\LocalisationUtil;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Amora\Core\Util\Logger;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Module\User\DataLayer\UserDataLayer;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\User\Model\UserRegistrationRequest;
use Amora\Core\Module\User\Model\UserVerification;
use Amora\Core\Module\User\Value\VerificationType;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;
use Amora\App\Value\Language;

class UserService
{
    const int USER_PASSWORD_MIN_LENGTH = 10;
    const int VERIFICATION_LINK_VALID_FOR_DAYS = 7;

    public function __construct(
        private readonly Logger $logger,
        private readonly UserDataLayer $userDataLayer,
        private readonly SessionService $sessionService,
        private readonly UserMailService $userMailService,
    ) {}

    public function storeUser(User $user, ?VerificationType $verificationType = null): ?User
    {
        $res = $this->userDataLayer->getDb()->withTransaction(
            function () use ($user, $verificationType) {
                $resUser = $this->userDataLayer->createNewUser($user);
                if (empty($resUser)) {
                    return new Feedback(false);
                }

                $resEmail = match ($verificationType) {
                    VerificationType::PasswordCreation =>
                    $this->userMailService->sendPasswordCreationEmail($resUser),
                    VerificationType::EmailAddress =>
                    $this->userMailService->sendVerificationEmail(
                        user: $resUser,
                        emailToVerify: $resUser->email,
                    ),
                    default => true
                };

                if (!$resEmail) {
                    return new Feedback(false);
                }

                return new Feedback(true, $resUser);
            }
        );

        return $res->isSuccess ? $res->response : null;
    }

    public function deleteUser(User $user): bool
    {
        return $this->userDataLayer->deleteUser($user);
    }

    private function updateUser(
        User $user,
        bool $updateSessionTimezone = false,
    ): bool {
        $res = $this->userDataLayer->getDb()->withTransaction(
            function() use ($user, $updateSessionTimezone) {
                $updatedUser = $this->userDataLayer->updateUser($user, $user->id);

                if (empty($updatedUser)) {
                    return new Feedback(false);
                }

                if ($user->changeEmailAddressTo) {
                    $this->userMailService->sendUpdateEmailVerificationEmail(
                        user: $user,
                        emailToVerify: $user->changeEmailAddressTo,
                    );
                }

                if ($updateSessionTimezone) {
                    $this->sessionService->updateTimezoneForUserId(
                        $user->id,
                        $user->timezone
                    );

                    Core::updateTimezone($user->timezone->getName());
                }

                return new Feedback(true);
            }
        );

        return $res->isSuccess;
    }

    public function verifyUser(string $email, string $unHashedPassword): ?User
    {
        if (empty($unHashedPassword)) {
            return null;
        }

        $res = $this->userDataLayer->getUserForEmail($email);
        if (empty($res)) {
            return null;
        }

        if (empty($res->passwordHash)) {
            return null;
        }

        $validPass = StringUtil::verifyPassword($unHashedPassword, $res->passwordHash);
        if (empty($validPass)) {
            return null;
        }

        if (!$res->isEnabled()) {
            return null;
        }

        return $res;
    }

    private function validateUpdateUserEndpoint(
        User $existingUser,
        ?string $email,
        ?string $languageIsoCode,
        ?string $timezone,
        ?string $currentPassword,
        ?string $newPassword,
        ?string $repeatPassword
    ): Feedback {
        if (isset($timezone) && !in_array($timezone, DateTimeZone::listIdentifiers())) {
            $this->logger->logError('Timezone not valid');
            return new Feedback(false);
        }

        if (isset($languageIsoCode) && Language::tryFrom(strtoupper($languageIsoCode)) === null) {
            $this->logger->logError('Language ID not valid: ' . $languageIsoCode);
            return new Feedback(false);
        }

        $localisationUtil = Core::getLocalisationUtil($existingUser->language);

        if (isset($currentPassword) || isset($newPassword) || isset($repeatPassword)) {
            if (empty($currentPassword) || empty($newPassword) || empty($repeatPassword)) {
                $this->logger->logError(
                    'Submitted payload not valid: one of the password fields is empty.'
                );

                return new Feedback(false);
            }

            $validPass = StringUtil::verifyPassword(
                unHashedPassword: $currentPassword,
                hashedPassword: $existingUser->passwordHash,
            );

            if (!$validPass) {
                return new Feedback(
                    isSuccess: false,
                    message: $localisationUtil->getValue('authenticationPassNotValid'),
                );
            }

            if (strlen($newPassword) < self::USER_PASSWORD_MIN_LENGTH) {
                return new Feedback(
                    false,
                    $localisationUtil->getValue('authenticationPasswordTooShort')
                );
            }

            if ($newPassword !== $repeatPassword) {
                return new Feedback(
                    isSuccess: false,
                    message: $localisationUtil->getValue('authenticationPasswordsDoNotMatch'),
                );
            }
        }

        if ($email) {
            if (!StringUtil::isEmailAddressValid($email)) {
                return new Feedback(
                    isSuccess: false,
                    message: $localisationUtil->getValue('authenticationEmailNotValid'),
                );
            }

            $userForEmail = $this->getUserForEmail($email);
            if ($userForEmail && $userForEmail->id !== $existingUser->id) {
                return new Feedback(
                    isSuccess: false,
                    message: $localisationUtil->getValue('authenticationRegistrationErrorExistingEmail'),
                );
            }
        }

        return new Feedback(true);
    }

    public function filterUserBy(
        ?bool $includeDisabled = true,
        ?int $userId = null,
        ?string $email = null,
        ?string $searchText = null,
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->userDataLayer->filterUserBy(
            includeDisabled: $includeDisabled,
            userId: $userId,
            email: $email,
            searchText: $searchText,
            queryOptions: $queryOptions,
        );
    }

    public function getUserForId(int $userId, $includeDisabled = false): ?User
    {
        return $this->userDataLayer->getUserForId($userId, $includeDisabled);
    }

    public function getUserForEmail(string $email, $includeDisabled = false): ?User
    {
        return $this->userDataLayer->getUserForEmail($email, $includeDisabled);
    }

    public function verifyEmailAddress(
        string $verificationIdentifier,
        LocalisationUtil $localisationUtil
    ): Feedback {
        $verification = $this->userDataLayer->getUserVerification(
            $verificationIdentifier,
            VerificationType::EmailAddress,
        );

        if (empty($verification) || !$verification->isEnabled) {
            return new Feedback(
                isSuccess: false,
                message: $localisationUtil->getValue('globalGenericError'),
            );
        }

        $message = $verification->type === VerificationType::EmailAddress
            ? $localisationUtil->getValue('authenticationEmailVerifiedExpired')
            : $localisationUtil->getValue('authenticationEmailVerifiedError');

        $user = $this->getUserForId($verification->userId);
        if (empty($user)) {
            return new Feedback(
                isSuccess: false,
                message: $localisationUtil->getValue('globalGenericError')
            );
        }

        $now = new DateTime();
        $dateDiff = $now->diff($verification->createdAt);
        if ($dateDiff->days > self::VERIFICATION_LINK_VALID_FOR_DAYS) {
            return new Feedback(
                isSuccess: false,
                message: $message,
            );
        }

        $res = $this->userDataLayer->getDb()->withTransaction(
            function () use ($user, $verification) {
                return new Feedback(
                    isSuccess: $this->userDataLayer->markUserAsVerified($user, $verification),
                );
            }
        );

        return new Feedback(
            isSuccess: $res->isSuccess,
            message: $res->isSuccess
                ? $localisationUtil->getValue('authenticationEmailVerified')
                : $localisationUtil->getValue('globalGenericError'),
        );
    }

    public function validatePasswordResetVerificationPage(
        string $verificationIdentifier
    ): ?UserVerification {
        $verification = $this->userDataLayer->getUserVerification(
            verificationIdentifier: $verificationIdentifier,
            type: VerificationType::PasswordReset,
            isEnabled: true,
        );

        if (empty($verification) || !$verification->isEnabled) {
            return null;
        }

        $user = $this->getUserForId($verification->userId);
        if (empty($user)) {
            return null;
        }

        $now = new DateTimeImmutable();
        $secondsSinceCreation = abs($now->getTimestamp() - $verification->createdAt->getTimestamp());
        if ($secondsSinceCreation > VerificationType::RESET_LINK_VALID_FOR_SECONDS) {
            return null;
        }

        return $verification;
    }

    public function validateCreateUserPasswordPage(
        string $verificationIdentifier
    ): ?UserVerification {
        $verification = $this->userDataLayer->getUserVerification(
            verificationIdentifier: $verificationIdentifier,
            type: VerificationType::PasswordCreation,
            isEnabled: true,
        );

        if (empty($verification) || !$verification->isEnabled) {
            return null;
        }

        $user = $this->getUserForId($verification->userId);
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

    public function workflowCreatePassword(
        User $user,
        string $verificationIdentifier,
        string $newPassword
    ): bool {
        $res = $this->userDataLayer->getDb()->withTransaction(
            function () use ($user, $verificationIdentifier, $newPassword) {
                $updateRes = $this->userDataLayer->updatePassword(
                    userId: $user->id,
                    hashedPassword: StringUtil::hashPassword($newPassword),
                );

                if (!$updateRes) {
                    return new Feedback(false);
                }

                $verification = $this->userDataLayer->getUserVerification(
                    verificationIdentifier: $verificationIdentifier,
                    type: VerificationType::PasswordCreation,
                );

                if (empty($verification) || !$verification->isEnabled) {
                    return new Feedback(false);
                }

                $markRes = $this->userDataLayer->markUserAsVerified($user, $verification);
                if (empty($markRes)) {
                    return new Feedback(false);
                }

                $journeyRes = $this->userDataLayer->updateUserJourney(
                    userId: $user->id,
                    userJourney: UserJourneyStatus::Registration,
                );

                return new Feedback($journeyRes);
            }
        );

        return $res->isSuccess;
    }

    public function workflowUpdateUser(
        User $existingUser,
        ?string $name = null,
        ?string $email = null,
        ?string $bio = null,
        ?string $languageIsoCode = null,
        ?string $timezone = null,
        ?string $currentPassword = null,
        ?string $newPassword = null,
        ?string $repeatPassword = null,
        ?UserStatus $userStatus = null,
        UserRole|AppUserRole|null $userRole = null,
    ): Feedback {
        $name = StringUtil::sanitiseText($name);
        $email = StringUtil::sanitiseText($email);
        $bio = StringUtil::sanitiseText($bio);
        $languageIsoCode = StringUtil::sanitiseText($languageIsoCode);
        $timezone = StringUtil::sanitiseText($timezone);

        $validation = $this->validateUpdateUserEndpoint(
            existingUser: $existingUser,
            email: $email,
            languageIsoCode: $languageIsoCode,
            timezone: $timezone,
            currentPassword: $currentPassword,
            newPassword: $newPassword,
            repeatPassword: $repeatPassword,
        );

        if (!$validation->isSuccess) {
            return $validation;
        }

        $hasEmailChanged = isset($email)
            && $existingUser->email !== StringUtil::normaliseEmail($email);
        $res = $this->updateUser(
            user: new User(
                id: $existingUser->id,
                status: $userStatus ?? $existingUser->status,
                language: $languageIsoCode
                    ? Language::from(strtoupper($languageIsoCode))
                    : $existingUser->language,
                role: $userRole ?? $existingUser->role,
                journeyStatus: $existingUser->journeyStatus,
                createdAt: $existingUser->createdAt,
                updatedAt: new DateTimeImmutable(),
                email: $existingUser->email,
                name: $name ?? $existingUser->name,
                passwordHash: $newPassword
                    ? StringUtil::hashPassword($newPassword)
                    : $existingUser->passwordHash,
                bio: $bio ?? $existingUser->bio,
                timezone: $timezone
                    ? DateUtil::convertStringToDateTimeZone($timezone)
                    : $existingUser->timezone,
                changeEmailAddressTo: $hasEmailChanged ? StringUtil::normaliseEmail($email) : null,
            ),
            updateSessionTimezone: isset($timezone),
        );

        if (empty($res)) {
            return new Feedback(false, 'Error updating user');
        }

        return $validation;
    }

    public function storeRegistrationInviteRequest(
        string $email,
        Language $language,
    ): UserRegistrationRequest
    {
        $res = $this->userDataLayer->getUserRegistrationRequest(null, $email);
        if ($res) {
            return $res;
        }

        $requestCode = $this->getUniqueUserRegistrationRequestCode();
        return $this->userDataLayer->storeRegistrationInviteRequest(
            new UserRegistrationRequest(
                id: null,
                email: $email,
                language: $language,
                createdAt: new DateTimeImmutable(),
                processedAt: null,
                requestCode: $requestCode,
                userId: null,
            )
        );
    }

    private function getUniqueUserRegistrationRequestCode(): string
    {
        do {
            $code = StringUtil::generateRandomString(64);
            $res = $this->userDataLayer->getUserRegistrationRequest($code);
        } while(!empty($res));

        return $code;
    }

    public function getTotalUsers(): int
    {
        return $this->userDataLayer->getTotalUsers();
    }
}
