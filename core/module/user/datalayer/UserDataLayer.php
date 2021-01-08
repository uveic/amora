<?php

namespace uve\core\module\user\datalayer;

use uve\core\database\MySqlDb;
use uve\core\Logger;
use uve\core\module\user\model\User;
use uve\core\module\user\model\UserVerification;
use uve\core\util\DateUtil;

class UserDataLayer
{
    const USER_TABLE_NAME = 'user';
    const USER_VERIFICATION_TABLE_NAME = 'user_verification';

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
                u.previous_email_address
            FROM ' . self::USER_TABLE_NAME . ' AS u
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
        $res = $this->db->update(self::USER_TABLE_NAME, $userId, $userArray);

        if (empty($res)) {
            $this->logger->logError('Error updating user. User ID: ' . $userId);
        }

        $user->setId($userId);
        return $user;
    }

    public function createNewUser(User $user): User
    {
        $resUser = $this->db->insert(self::USER_TABLE_NAME, $user->asArray());

        if (empty($resUser)) {
            $this->logger->logError('Error inserting user');
        }

        $user->setId((int)$resUser);

        return $user;
    }

    public function deleteUser(User $user): bool
    {
        return $this->db->delete(self::USER_TABLE_NAME, ['id' => $user->getId()]);
    }

    public function storeUserVerification(UserVerification $data): UserVerification
    {
        $res = $this->db->insert(self::USER_VERIFICATION_TABLE_NAME, $data->asArray());

        if (empty($res)) {
            $this->logger->logError('Error inserting user verification data');
        }

        $data->setId((int)$res);

        return $data;
    }

    public function disableVerificationDataForUserId(int $userId, int $typeId): bool
    {
        $sql = '
            UPDATE ' . self::USER_VERIFICATION_TABLE_NAME . '
            SET is_enabled = 0
            WHERE user_id = :user_id
                AND type_id = :type_id
        ';

        $params = [
            ':user_id' => $userId,
            ':type_id' => $typeId
        ];
        return $this->db->execute($sql, $params);
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
                u.created_at,
                u.verified_at,
                u.verification_identifier,
                u.is_enabled
            FROM ' . self::USER_VERIFICATION_TABLE_NAME . ' AS u
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

    public function markUserAsVerified(int $userId): bool
    {
        $res = $this->db->withTransaction(function () use ($userId) {
            $res = $this->db->execute(
                'UPDATE ' . self::USER_TABLE_NAME . ' SET verified = 1 WHERE id = :userId',
                [':userId' => $userId]
            );

            if (empty($res)) {
                return ['success' => false];
            }

            $res = $this->db->execute(
                '
                    UPDATE ' . self::USER_VERIFICATION_TABLE_NAME . '
                    SET is_enabled = 0,
                        verified_at = :verifiedAt
                    WHERE user_id = :userId
                 ',
                [
                    ':userId' => $userId,
                    ':verifiedAt' => DateUtil::getCurrentDateForMySql()
                ]
            );

            if (empty($res)) {
                return ['success' => false];
            }

            return ['success' => true];
        });

        return empty($res['success']) ? false : true;
    }

    public function updatePassword(int $userId, string $hashedPassword): bool
    {
        $sql = '
            UPDATE ' . self::USER_TABLE_NAME . '
            SET password_hash = :newPassword,
                updated_at = :now
            WHERE id = :userId
        ';
        $params = [
            ':userId' => $userId,
            ':newPassword' => $hashedPassword,
            ':now' => DateUtil::getCurrentDateForMySql()
        ];

        return $this->db->execute($sql, $params);
    }
}
