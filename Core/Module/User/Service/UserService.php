<?php

namespace Amora\Core\Module\User\Service;

use Amora\Core\Core;
use Amora\Core\Database\Model\TransactionResponse;
use Amora\Core\Model\Util\QueryOptions;
use Amora\Core\Module\User\Value\UserJourneyStatus;
use Amora\Core\Util\LocalisationUtil;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Amora\Core\Logger;
use Amora\Core\Model\Response\UserFeedback;
use Amora\Core\Module\User\Datalayer\UserDataLayer;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\User\Model\UserRegistrationRequest;
use Amora\Core\Module\User\Model\UserVerification;
use Amora\Core\Module\User\Value\VerificationType;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;
use Amora\Core\Value\Language;

class UserService
{
    const USER_PASSWORD_MIN_LENGTH = 10;
    const VERIFICATION_LINK_VALID_FOR_DAYS = 7;

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

    public function storeUser(User $user, ?VerificationType $verificationType = null): ?User
    {
        $res = $this->userDataLayer->getDb()->withTransaction(
            function () use ($user, $verificationType) {
                $resUser = $this->userDataLayer->createNewUser($user);
                if (empty($resUser)) {
                    return new TransactionResponse(false);
                }

                $resEmail = match ($verificationType) {
                    VerificationType::PasswordCreation =>
                        $this->userMailService->sendPasswordCreationEmail($resUser),
                    VerificationType::EmailAddress =>
                        $this->userMailService->sendVerificationEmail(
                            $resUser,
                            $resUser->getEmail()
                        ),
                    default => true
                };

                if (!$resEmail) {
                    return new TransactionResponse(false);
                }

                return new TransactionResponse(true, $resUser);
            }
        );

        return $res->isSuccess() ? $res->getResponse() : null;
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
                $updatedUser = $this->userDataLayer->updateUser($user, $user->getId());

                if (empty($updatedUser)) {
                    return new TransactionResponse(false);
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

                return new TransactionResponse(true);
            }
        );

        return $res->isSuccess();
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

        if (!$res->isEnabled) {
            return null;
        }

        return $res;
    }

    private function validateUpdateUserEndpoint(
        User $existingUser,
        ?string $email,
        ?int $languageId,
        ?string $timezone,
        ?string $currentPassword,
        ?string $newPassword,
        ?string $repeatPassword
    ): UserFeedback {
        if (isset($timezone) && !in_array($timezone, DateTimeZone::listIdentifiers())) {
            $this->logger->logError('Timezone not valid');
            return new UserFeedback(false);
        }

        if (isset($languageId) && !in_array($languageId, array_column(Language::getAll(), 'id'))) {
            $this->logger->logError('Language ID not valid: ' . $languageId);
            return new UserFeedback(false);
        }

        $localisationUtil = Core::getLocalisationUtil(
            Language::getIsoCodeForId($existingUser->getLanguageId())
        );

        if (isset($currentPassword) || isset($newPassword) || isset($repeatPassword)) {
            if (empty($currentPassword) || empty($newPassword) || empty($repeatPassword)) {
                $this->logger->logError(
                    'Submitted payload not valid: one of the password fields is empty.'
                );

                return new UserFeedback(false);
            }

            $validPass = StringUtil::verifyPassword(
                unHashedPassword: $currentPassword,
                hashedPassword: $existingUser->passwordHash,
            );

            if (!$validPass) {
                return new UserFeedback(
                    false,
                    $localisationUtil->getValue('authenticationPassNotValid')
                );
            }

            if (strlen($newPassword) < self::USER_PASSWORD_MIN_LENGTH) {
                return new UserFeedback(
                    false,
                    $localisationUtil->getValue('authenticationPasswordTooShort')
                );
            }

            if ($newPassword !== $repeatPassword) {
                return new UserFeedback(
                    false,
                    $localisationUtil->getValue('authenticationPasswordsDoNotMatch')
                );
            }
        }

        if ($email) {
            if (!StringUtil::isEmailAddressValid($email)) {
                return new UserFeedback(
                    false,
                    $localisationUtil->getValue('authenticationEmailNotValid')
                );
            }

            $userForEmail = $this->getUserForEmail($email);
            if ($userForEmail && $userForEmail->getId() !== $existingUser->getId()) {
                return new UserFeedback(
                    false,
                    $localisationUtil->getValue('authenticationRegistrationErrorExistingEmail')
                );
            }
        }

        return new UserFeedback(true);
    }

    public function filterUsersBy(
        ?string $searchText = null,
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->userDataLayer->filterUsersBy(
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
    ): UserFeedback {
        $verification = $this->userDataLayer->getUserVerification(
            $verificationIdentifier,
            VerificationType::EmailAddress,
        );

        if (empty($verification) || !$verification->isEnabled) {
            return new UserFeedback(false, $localisationUtil->getValue('globalGenericError'));
        }

        $message = $verification->type === VerificationType::EmailAddress
            ? $localisationUtil->getValue('authenticationEmailVerifiedExpired')
            : $localisationUtil->getValue('authenticationEmailVerifiedError');

        $user = $this->getUserForId($verification->userId);
        if (empty($user)) {
            return new UserFeedback(
                false,
                $localisationUtil->getValue('globalGenericError')
            );
        }

        if (!$verification->isEnabled) {
            return new UserFeedback(false, $message);
        }

        $now = new DateTime();
        $dateDiff = $now->diff($verification->createdAt);
        if ($dateDiff->days > self::VERIFICATION_LINK_VALID_FOR_DAYS) {
            return new UserFeedback(false, $message);
        }

        $res = $this->userDataLayer->getDb()->withTransaction(
            function () use ($user, $verification) {
                $resVer = $this->userDataLayer->markUserAsVerified($user, $verification);
                return new TransactionResponse($resVer);
            }
        );

        return new UserFeedback(
            $res->isSuccess(),
            $res->isSuccess()
                ? $localisationUtil->getValue('authenticationEmailVerified')
                : $localisationUtil->getValue('globalGenericError')
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
                    $user->getId(),
                    StringUtil::hashPassword($newPassword)
                );

                if (!$updateRes) {
                    return new TransactionResponse(false);
                }

                $verification = $this->userDataLayer->getUserVerification(
                    verificationIdentifier: $verificationIdentifier,
                    type: VerificationType::PasswordCreation,
                );

                if (empty($verification) || !$verification->isEnabled) {
                    return new TransactionResponse(false);
                }

                $markRes = $this->userDataLayer->markUserAsVerified($user, $verification);
                if (empty($markRes)) {
                    return new TransactionResponse(false);
                }

                $journeyRes = $this->userDataLayer->updateUserJourney(
                    userId: $user->id,
                    userJourney: UserJourneyStatus::Registration,
                );

                return new TransactionResponse($journeyRes);
            }
        );

        return $res->isSuccess();
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
            $existingUser,
            $email,
            $languageId,
            $timezone,
            $currentPassword,
            $newPassword,
            $repeatPassword
        );

        if (!$validation->isSuccess()) {
            return $validation;
        }

        $hasEmailChanged = isset($email)
            && $existingUser->getEmail() !== StringUtil::normaliseEmail($email);
        $res = $this->updateUser(
            user: new User(
                id: $existingUser->id,
                languageId: $languageId ?? $existingUser->languageId,
                role: $existingUser->role,
                journeyStatus: $existingUser->journeyStatus,
                createdAt: $existingUser->createdAt,
                updatedAt: new DateTimeImmutable(),
                email: $existingUser->email,
                name: $name ?? $existingUser->name,
                passwordHash: $newPassword
                    ? StringUtil::hashPassword($newPassword)
                    : $existingUser->passwordHash,
                bio: $existingUser->bio,
                isEnabled: $isEnabled ?? $existingUser->isEnabled,
                verified: $existingUser->verified,
                timezone: $timezone
                    ? DateUtil::convertStringToDateTimeZone($timezone)
                    : $existingUser->timezone,
                changeEmailAddressTo: $hasEmailChanged ? StringUtil::normaliseEmail($email) : null,
            ),
            updateSessionTimezone: isset($timezone),
        );

        if (empty($res)) {
            return new UserFeedback(false, 'Error updating user');
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
