<?php

namespace Amora\Core\Module\User\Service;

use Amora\App\Value\AppUserRole;
use Amora\Core\Core;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Module\User\Model\UserAction;
use Amora\Core\Module\User\Value\UserActionType;
use Amora\Core\Module\User\Value\UserJourneyStatus;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Module\User\Value\UserStatus;
use Amora\Core\Util\LocalisationUtil;
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

readonly class UserService
{
    public function __construct(
        private Logger $logger,
        private UserDataLayer $userDataLayer,
        private SessionService $sessionService,
        private UserMailService $userMailService,
    ) {
    }

    public function storeUser(User $user): ?User
    {
        return $this->userDataLayer->storeUser($user);
    }

    public function sendUserVerificationEmail(User $user, VerificationType $verificationType): bool
    {
        return match ($verificationType) {
            VerificationType::PasswordCreation =>
            $this->userMailService->sendPasswordCreationEmail($user),
            VerificationType::VerifyEmailAddress =>
            $this->userMailService->sendVerificationEmail(
                user: $user,
                emailToVerify: $user->changeEmailAddressTo ?: $user->email,
                verificationType: VerificationType::VerifyEmailAddress,
            ),
            default => true,
        };
    }

    public function workflowStoreUserAndSendVerificationEmail(
        ?User $createdByUser,
        User $user,
        VerificationType $verificationType,
    ): ?User {
        $res = $this->userDataLayer->db->withTransaction(
            function () use ($createdByUser, $user, $verificationType) {
                $resUser = $this->storeUser($user);
                if (!$resUser) {
                    return new Feedback(false);
                }

                $resAction = $this->storeUserAction(
                    new UserAction(
                        id: null,
                        userId: $resUser->id,
                        createdByUser: $createdByUser,
                        type: UserActionType::Create,
                        createdAt: new DateTimeImmutable(),
                    ),
                );

                if (!$resAction) {
                    return new Feedback(false);
                }

                $resEmail = match ($verificationType) {
                    VerificationType::PasswordCreation =>
                    $this->userMailService->sendPasswordCreationEmail($resUser),
                    VerificationType::VerifyEmailAddress =>
                    $this->userMailService->sendVerificationEmail(
                        user: $resUser,
                        emailToVerify: $resUser->email,
                        verificationType: VerificationType::VerifyEmailAddress,
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

    public function verifyUser(string $email, string $unHashedPassword): ?User
    {
        if (empty($unHashedPassword)) {
            return null;
        }

        $res = $this->getUserForEmail(email: $email);
        if (!$res) {
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
        if (isset($timezone) && !in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
            $this->logger->logError('Timezone not valid');
            return new Feedback(false);
        }

        if (isset($languageIsoCode) && !Language::tryFrom(strtoupper($languageIsoCode))) {
            $this->logger->logError('Language ID not valid: ' . $languageIsoCode);
            return new Feedback(false);
        }

        $localisationUtil = Core::getLocalisationUtil($existingUser->language);

        if (isset($currentPassword)) {
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
        }

        if (isset($newPassword) || isset($repeatPassword)) {
            if (empty($newPassword) || empty($repeatPassword)) {
                $this->logger->logError(
                    'Submitted payload not valid: one of the password fields is empty.'
                );

                return new Feedback(false);
            }

            if (strlen($newPassword) < Core::USER_PASSWORD_MIN_LENGTH) {
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
        ?array $userIds = [],
        ?string $email = null,
        ?string $searchText = null,
        ?string $identifier = null,
        array $statusIds = [],
        array $roleIds = [],
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->userDataLayer->filterUserBy(
            userIds: $userIds,
            email: $email,
            searchText: $searchText,
            identifier: $identifier,
            statusIds: $statusIds,
            roleIds: $roleIds,
            queryOptions: $queryOptions,
        );
    }

    public function getUserForId(int $userId, bool $includeDisabled = true): ?User
    {
        $res = $this->filterUserBy(
            userIds: [$userId],
            statusIds: $includeDisabled ? [] : [UserStatus::Enabled->value],
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getUserForEmail(string $email, bool $includeDisabled = false): ?User
    {
        $res = $this->filterUserBy(
            email: $email,
            statusIds: $includeDisabled ? [] : [UserStatus::Enabled->value],
        );

        return empty($res[0]) ? null : $res[0];
    }

    public function getUserVerification(
        string $verificationIdentifier,
        ?VerificationType $type = null,
        ?bool $isEnabled = null,
    ): ?UserVerification {
        return $this->userDataLayer->getUserVerification(
            verificationIdentifier: $verificationIdentifier,
            type: $type,
            isEnabled: $isEnabled,
        );
    }

    public function verifyEmailAddress(
        ?User $verifiedByUser,
        string $verificationIdentifier,
        LocalisationUtil $localisationUtil
    ): Feedback {
        $verification = $this->getUserVerification(
            verificationIdentifier: $verificationIdentifier,
            type: VerificationType::VerifyEmailAddress,
            isEnabled: true,
        );

        if (!$verification) {
            return new Feedback(
                isSuccess: false,
                message: $localisationUtil->getValue('authenticationEmailVerifiedError'),
            );
        }

        $user = $this->getUserForId($verification->userId);
        if (!$user) {
            return new Feedback(
                isSuccess: false,
                message: $localisationUtil->getValue('globalGenericError')
            );
        }

        if (
            ($user->changeEmailAddressTo && $user->changeEmailAddressTo !== $verification->email) ||
            $verification->hasExpired()
        ) {
            return new Feedback(
                isSuccess: false,
                message: $localisationUtil->getValue('authenticationEmailVerifiedExpired')
            );
        }

        $res = $this->userDataLayer->db->withTransaction(
            function () use ($verifiedByUser, $user, $verification) {
                $resUser = $this->updateUserFields(
                    userId: $user->id,
                    newJourneyStatus: UserJourneyStatus::RegistrationComplete,
                    newEmail: $verification->email,
                    deleteChangeEmailTo: true,
                );

                if (empty($resUser)) {
                    return new Feedback(false);
                }

                $resVerification = $this->userDataLayer->markVerificationAsVerified($verification->id);
                if (empty($resVerification)) {
                    return new Feedback(false);
                }

                $resAction = $this->storeUserAction(
                    new UserAction(
                        id: null,
                        userId: $user->id,
                        createdByUser: $verifiedByUser ?? $user,
                        type: UserActionType::VerifyEmail,
                        createdAt: new DateTimeImmutable(),
                    ),
                );

                if (!$resAction) {
                    return new Feedback(false);
                }

                return new Feedback(true);
            }
        );

        return new Feedback(
            isSuccess: $res->isSuccess,
            message: $res->isSuccess
                ? $localisationUtil->getValue('authenticationEmailVerified')
                : $localisationUtil->getValue('globalGenericError'),
        );
    }

    public function workflowUpdatePassword(
        User $updatedByUser,
        int $userId,
        string $newPassword,
        UserVerification $verification
    ): bool {
        $res = $this->userDataLayer->db->withTransaction(
            function () use ($updatedByUser, $userId, $newPassword, $verification) {
                $resUpdate = $this->userDataLayer->updateUserFields(
                    userId: $userId,
                    newHashedPassword: StringUtil::hashPassword($newPassword),
                );

                if (!$resUpdate) {
                    return new Feedback(false);
                }

                $sessionUpdate = $this->sessionService->expireAllSessionsForUser($userId);
                if (!$sessionUpdate) {
                    return new Feedback(false);
                }

                $resDisable = $this->userDataLayer->markVerificationAsVerified($verification->id);
                if (!$resDisable) {
                    return new Feedback(false);
                }

                $resAction = $this->storeUserAction(
                    new UserAction(
                        id: null,
                        userId: $userId,
                        createdByUser: $updatedByUser,
                        type: UserActionType::UpdatePassword,
                        createdAt: new DateTimeImmutable(),
                    ),
                );

                if (!$resAction) {
                    return new Feedback(false);
                }

                return new Feedback(true);
            }
        );

        return $res->isSuccess;
    }

    public function workflowCreatePassword(
        User $updatedByUser,
        User $user,
        string $verificationIdentifier,
        string $newPassword
    ): bool {
        $res = $this->userDataLayer->db->withTransaction(
            function () use ($updatedByUser, $user, $verificationIdentifier, $newPassword) {
                $updateRes = $this->userDataLayer->updateUserFields(
                    userId: $user->id,
                    newJourneyStatus: UserJourneyStatus::RegistrationComplete,
                    newHashedPassword: StringUtil::hashPassword($newPassword),
                );

                if (!$updateRes) {
                    return new Feedback(false);
                }

                $verification = $this->getUserVerification(
                    verificationIdentifier: $verificationIdentifier,
                    type: VerificationType::PasswordCreation,
                    isEnabled: true,
                );

                if (!$verification || $verification->hasExpired()) {
                    return new Feedback(false);
                }

                $resVerification = $this->userDataLayer->markVerificationAsVerified($verification->id);
                if (empty($resVerification)) {
                    return false;
                }

                $sessionUpdate = $this->sessionService->expireAllSessionsForUser($user->id);
                if (!$sessionUpdate) {
                    return new Feedback(false);
                }

                $journeyRes = $this->userDataLayer->updateUserFields(
                    userId: $user->id,
                    newJourneyStatus: UserJourneyStatus::RegistrationComplete,
                );

                if (!$journeyRes) {
                    return new Feedback(false);
                }

                $resAction = $this->storeUserAction(
                    new UserAction(
                        id: null,
                        userId: $user->id,
                        createdByUser: $updatedByUser,
                        type: UserActionType::PasswordCreation,
                        createdAt: new DateTimeImmutable(),
                    ),
                );

                if (!$resAction) {
                    return new Feedback(false);
                }

                return new Feedback(true);
            }
        );

        return $res->isSuccess;
    }

    public function workflowUpdateUser(
        User $updatedByUser,
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
        ?UserActionType $actionType = null,
    ): Feedback {
        return $this->userDataLayer->db->withTransaction(
            function () use (
                $updatedByUser,
                $existingUser,
                $name,
                $email,
                $bio,
                $languageIsoCode,
                $timezone,
                $currentPassword,
                $newPassword,
                $repeatPassword,
                $userStatus,
                $userRole,
                $actionType,
            ) {
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

                $hasEmailChanged = isset($email) && $existingUser->email !== StringUtil::normaliseEmail($email);
                $updatedUser = new User(
                    id: $existingUser->id,
                    status: $userStatus ?? $existingUser->status,
                    language: $languageIsoCode
                        ? Language::from(strtoupper($languageIsoCode))
                        : $existingUser->language,
                    role: $userRole ?? $existingUser->role,
                    journeyStatus: $hasEmailChanged
                        ? UserJourneyStatus::PendingEmailVerification
                        : $existingUser->journeyStatus,
                    createdAt: $existingUser->createdAt,
                    updatedAt: new DateTimeImmutable(),
                    email: $existingUser->email,
                    name: $name ?? $existingUser->name,
                    passwordHash: $newPassword
                        ? StringUtil::hashPassword($newPassword)
                        : $existingUser->passwordHash,
                    bio: $bio ?? $existingUser->bio,
                    identifier: $existingUser->identifier ?? $this->generateUniqueIdentifier(),
                    timezone: $timezone
                        ? DateUtil::convertStringToDateTimeZone($timezone)
                        : $existingUser->timezone,
                    changeEmailAddressTo: $hasEmailChanged ? StringUtil::normaliseEmail($email) : null,
                );

                $updatedUser = $this->userDataLayer->updateUser(
                    user: $updatedUser,
                    userId: $existingUser->id,
                );

                if (!$updatedUser) {
                    return new Feedback(false, 'Error updating user');
                }

                if ($newPassword) {
                    $sessionUpdate = $this->sessionService->expireAllSessionsForUser($existingUser->id);
                    if (!$sessionUpdate) {
                        return new Feedback(false, 'Error expiring sessions');
                    }
                }

                if ($updatedUser->changeEmailAddressTo) {
                    $resVerificationEmail = $this->userMailService->sendUpdateEmailVerificationEmail(
                        user: $updatedUser,
                        emailToVerify: $updatedUser->changeEmailAddressTo,
                    );

                    if (!$resVerificationEmail) {
                        return new Feedback(false, 'Error sending verification email');
                    }
                }

                if ($timezone) {
                    $resTimezone = $this->sessionService->updateTimezoneForUserId(
                        userId: $existingUser->id,
                        newTimezone: $updatedUser->timezone,
                    );

                    if (!$resTimezone) {
                        return new Feedback(false, 'Error updating timezone');
                    }

                    Core::updateTimezone($updatedUser->timezone->getName());
                }

                $resAction = $this->storeUserAction(
                    new UserAction(
                        id: null,
                        userId: $updatedUser->id,
                        createdByUser: $updatedByUser,
                        type: $actionType ?? (
                            $updatedUser->changeEmailAddressTo
                                ? UserActionType::UpdateEmailRequest
                                : UserActionType::Update
                            ),
                        createdAt: new DateTimeImmutable(),
                    ),
                );

                if (!$resAction) {
                    return new Feedback(false, 'Error storing action');
                }

                return new Feedback(true);
            }
        );
    }

    public function storeRegistrationInviteRequest(
        string $email,
        Language $language,
    ): UserRegistrationRequest {
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
        } while ($res);

        return $code;
    }

    public function getTotalUsers(): int
    {
        return $this->userDataLayer->getTotalUsers();
    }

    public function generateUniqueIdentifier(bool $makeUppercase = true): string
    {
        $characters = 3;
        $count = 0;
        $max = '1' . str_repeat('0', $characters);

        do {
            if ($count++ > 10) {
                $characters++;
                $count = 0;

                $max = '1' . str_repeat('0', $characters);
            }

            $identifier = StringUtil::generateRandomString($characters, true)
                . '-'
                . str_pad(random_int(1, ((int)$max)) - 1, $characters, '0', STR_PAD_LEFT);

            $existingUser = $this->filterUserBy(identifier: $identifier);
        } while ($existingUser);

        return $makeUppercase ? strtoupper($identifier) : $identifier;
    }

    public function updateUserFields(
        int $userId,
        ?UserStatus $newStatus = null,
        UserRole|AppUserRole|null $newRole = null,
        ?UserJourneyStatus $newJourneyStatus = null,
        ?string $newEmail = null,
        ?string $newChangeEmailTo = null,
        bool $deleteChangeEmailTo = false,
    ): bool {
        return $this->userDataLayer->updateUserFields(
            userId: $userId,
            newStatus: $newStatus,
            newRole: $newRole,
            newJourneyStatus: $newJourneyStatus,
            newEmail: $newEmail,
            newChangeEmailTo: $newChangeEmailTo,
            deleteChangeEmailTo: $deleteChangeEmailTo,
        );
    }

    public function workflowUpdateUserFields(
        User $updatedByUser,
        User $existingUser,
        UserActionType $userActionType,
        ?UserStatus $userStatus = null,
        ?UserRole $userRole = null,
    ): bool {
        $res = $this->userDataLayer->db->withTransaction(
            function () use (
                $updatedByUser,
                $existingUser,
                $userActionType,
                $userStatus,
                $userRole,
            ) {
                $resUpdate = $this->updateUserFields(
                    userId: $existingUser->id,
                    newStatus: $userStatus,
                    newRole: $userRole,
                );

                if (!$resUpdate) {
                    return new Feedback(false);
                }

                $resAction = $this->storeUserAction(
                    new UserAction(
                        id: null,
                        userId: $existingUser->id,
                        createdByUser: $updatedByUser,
                        type: $userActionType,
                        createdAt: new DateTimeImmutable(),
                    ),
                );

                if (!$resAction) {
                    return new Feedback(false);
                }

                return new Feedback(true);
            }
        );

        return $res->isSuccess;
    }

    public function storeUserAction(UserAction $item): ?UserAction
    {
        return $this->userDataLayer->storeUserAction($item);
    }
}
