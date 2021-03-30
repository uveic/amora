<?php

namespace Amora\Core\Module\User\Datalayer;

use Amora\Core\Database\MySqlDb;
use Amora\Core\Logger;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\User\Model\UserRegistrationRequest;
use Amora\Core\Module\User\Model\UserVerification;
use Amora\Core\Module\User\Value\VerificationType;
use Amora\Core\Util\DateUtil;

class UserDataLayer
{
    const USER_TABLE = 'user';
    const USER_VERIFICATION_TABLE = 'user_verification';
    const USER_REGISTRATION_REQUEST_TABLE = 'user_registration_request';

    private MySqlDb $db;
    private Logger $logger;

    public function __construct(MySqlDb $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    private function getUsers(
        ?bool $includeDisabled = true,
        ?int $userId = null,
        ?string $email = null
    ): array {
        $params = [];
        $sql = '
            SELECT
                u.id,
                u.language_id,
                u.role_id,
                u.journey_id,
                u.created_at,
                u.updated_at,
                u.email,
                u.name,
                u.password_hash,
                u.bio,
                u.is_enabled,
                u.verified,
                u.timezone,
                u.change_email_to
            FROM ' . self::USER_TABLE . ' AS u
            WHERE 1
        ';

        if (!$includeDisabled) {
            $sql .= ' AND u.is_enabled = :enabled';
            $params[':enabled'] = 1;
        }

        if (isset($userId)) {
            $sql .= ' AND u.id = :user_id';
            $params[':user_id'] = $userId;
        }

        if (isset($email)) {
            $sql .= ' AND u.email = :email';
            $params[':email'] = $email;
        }

        $sql .= ' ORDER BY u.id ASC';

        $res = $this->db->fetchAll($sql, $params);

        $output = [];
        foreach ($res as $item) {
            $output[] = User::fromArray($item);
        }

        return $output;
    }

    public function getDb(): MySqlDb
    {
        return $this->db;
    }

    public function getAllUsers(): array
    {
        return $this->getUsers();
    }

    public function getUserForId(int $id, $includeDisabled = false): ?User
    {
        $res = $this->getUsers($includeDisabled, $id);
        return empty($res[0]) ? null : $res[0];
    }

    public function getUserForEmail(string $email, $includeDisabled = false): ?User
    {
        $res = $this->getUsers($includeDisabled, null, $email);
        return empty($res[0]) ? null : $res[0];
    }

    public function updateUser(User $user, int $userId): User
    {
        $userArray = $user->asArray();
        unset($userArray['created_at']);
        unset($userArray['id']);
        $res = $this->db->update(self::USER_TABLE, $userId, $userArray);

        if (empty($res)) {
            $this->logger->logError('Error updating user. User ID: ' . $userId);
        }

        $user->setId($userId);
        return $user;
    }

    public function createNewUser(User $user): User
    {
        $resUser = $this->db->insert(self::USER_TABLE, $user->asArray());

        if (empty($resUser)) {
            $this->logger->logError('Error inserting user');
        }

        $user->setId((int)$resUser);

        return $user;
    }

    public function deleteUser(User $user): bool
    {
        return $this->db->delete(self::USER_TABLE, ['id' => $user->getId()]);
    }

    public function storeUserVerification(UserVerification $data): UserVerification
    {
        $res = $this->db->insert(self::USER_VERIFICATION_TABLE, $data->asArray());

        if (empty($res)) {
            $this->logger->logError('Error inserting user verification data');
        }

        $data->setId((int)$res);

        return $data;
    }

    public function disableVerificationDataForUserId(int $userId): bool
    {
        return $this->db->execute(
            '
                UPDATE ' . self::USER_VERIFICATION_TABLE . '
                SET is_enabled = 0
                WHERE user_id = :userId
            ',
            [
                ':userId' => $userId,
            ]
        );
    }

    public function getUserVerification(
        string $verificationIdentifier,
        ?int $typeId = null,
        ?bool $isEnabled = null
    ): ?UserVerification {
        $sql = '
            SELECT
                u.id,
                u.user_id,
                u.type_id,
                u.email,
                u.created_at,
                u.verified_at,
                u.verification_identifier,
                u.is_enabled
            FROM ' . self::USER_VERIFICATION_TABLE . ' AS u
            WHERE 1
                AND u.verification_identifier = :verificationIdentifier
        ';

        $params = [
            ':verificationIdentifier' => $verificationIdentifier
        ];

        if (isset($typeId)) {
            $sql .= ' AND u.type_id = :typeId';
            $params[':typeId'] = $typeId;
        }

        if (isset($isEnabled)) {
            $sql .= ' AND u.is_enabled = :isEnabled';
            $params[':isEnabled'] = $isEnabled ? 1 : 0;
        }

        $res = $this->db->fetchOne($sql, $params);

        return $res ? UserVerification::fromArray($res) : null;
    }

    public function markUserAsVerified(User $user, UserVerification $verification): bool
    {
        $data = [
            'updated_at' => DateUtil::getCurrentDateForMySql(),
            'verified' => 1,
            'change_email_to' => null
        ];

        if ($verification->getEmail()) {
            $data['email'] = $verification->getEmail();
        }

        $res = $this->db->update(self::USER_TABLE, $user->getId(), $data);

        if (empty($res)) {
            return false;
        }

        $res2 = $this->db->update(
            self::USER_VERIFICATION_TABLE,
            $verification->getId(),
            [
                'is_enabled' => 0,
                'verified_at' => DateUtil::getCurrentDateForMySql()
            ]
        );

        if (empty($res2)) {
            return false;
        }

        return true;
    }

    public function updatePassword(int $userId, string $hashedPassword): bool
    {
        return $this->db->update(
            self::USER_TABLE,
            $userId,
            [
                'password_hash' => $hashedPassword,
                'updated_at' => DateUtil::getCurrentDateForMySql(),
            ]
        );
    }

    public function updateUserJourney(int $userId, int $userJourneyId): bool
    {
        return $this->db->update(
            self::USER_TABLE,
            $userId,
            [
                'journey_id' => $userJourneyId,
                'updated_at' => DateUtil::getCurrentDateForMySql(),
            ]
        );
    }

    public function getUserRegistrationRequest(
        ?string $code = null,
        ?string $email = null
    ): ?UserRegistrationRequest {
        if (empty($code) && empty($email)) {
            return null;
        }

        $params = [];
        $sql = '
            SELECT
                urr.id,
                urr.email,
                urr.language_id,
                urr.created_at,
                urr.processed_at,
                urr.request_code,
                urr.user_id
            FROM ' . self::USER_REGISTRATION_REQUEST_TABLE . ' AS urr
            WHERE 1
        ';

        if ($code) {
            $sql .= ' AND urr.request_code = :code';
            $params[':code'] = $code;
        }

        if ($email) {
            $sql .= ' AND urr.email = :email';
            $params[':email'] = $email;
        }

        $res = $this->db->fetchOne($sql, $params);

        if ($res) {
            return UserRegistrationRequest::fromArray($res);
        }

        return null;
    }

    public function storeRegistrationInviteRequest(
        UserRegistrationRequest $data
    ): UserRegistrationRequest {
        $res = $this->db->insert(self::USER_REGISTRATION_REQUEST_TABLE, $data->asArray());

        if (empty($res)) {
            $this->logger->logError('Error inserting user registration request data');
        }

        $data->setId((int)$res);

        return $data;
    }
}
