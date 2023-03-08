<?php

namespace Amora\Core\Module\User\Datalayer;

use Amora\Core\Database\MySqlDb;
use Amora\Core\Util\Logger;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Module\DataLayerTrait;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\User\Model\UserRegistrationRequest;
use Amora\Core\Module\User\Model\UserVerification;
use Amora\Core\Module\User\Value\UserJourneyStatus;
use Amora\Core\Module\User\Value\VerificationType;
use Amora\Core\Util\DateUtil;

class UserDataLayer
{
    use DataLayerTrait;

    const USER_TABLE = 'core_user';
    const USER_VERIFICATION_TABLE = 'core_user_verification';
    const USER_VERIFICATION_TYPE_TABLE = 'core_user_verification_type';
    const USER_REGISTRATION_REQUEST_TABLE = 'core_user_registration_request';

    const USER_ROLE_TABLE = 'core_user_role';
    const USER_JOURNEY_STATUS_TABLE = 'core_user_journey_status';

    public function __construct(
        private readonly MySqlDb $db,
        private readonly Logger $logger,
    ) {}

    private function getUsers(
        ?bool $includeDisabled = true,
        ?int $userId = null,
        ?string $email = null,
        ?string $searchText = null,
        ?QueryOptions $queryOptions = null,
    ): array {
        if (!isset($queryOptions)) {
            $queryOptions = new QueryOptions();
        }

        $orderByMapping = [
            'created_at' => 'u.created_at',
            'updated_at' => 'u.updated_at',
            'name' => 'u.name',
        ];

        $params = [];
        $baseSql = 'SELECT ';
        $fields = [
            'u.id AS user_id',
            'u.language_iso_code AS user_language_iso_code',
            'u.role_id AS user_role_id',
            'u.journey_id AS user_journey_id',
            'u.created_at AS user_created_at',
            'u.updated_at AS user_updated_at',
            'u.email AS user_email',
            'u.name AS user_name',
            'u.password_hash AS user_password_hash',
            'u.bio AS user_bio',
            'u.is_enabled AS user_is_enabled',
            'u.verified AS user_verified',
            'u.timezone AS user_timezone',
            'u.change_email_to AS user_change_email_to',
        ];

        $joins = ' FROM ' . self::USER_TABLE . ' AS u';
        $where = ' WHERE 1';

        if (!$includeDisabled) {
            $where .= ' AND u.is_enabled = :enabled';
            $params[':enabled'] = 1;
        }

        if (isset($userId)) {
            $where .= ' AND u.id = :user_id';
            $params[':user_id'] = $userId;
        }

        if (isset($email)) {
            $where .= ' AND u.email = :email';
            $params[':email'] = $email;
        }

        if (isset($searchText)) {
            $where .= " AND (u.email LIKE :searchText OR u.name LIKE :searchText)";
            $params[':searchText'] = '%' . $searchText . '%';
        }

        $orderByAndLimit = $this->generateOrderByAndLimitCode($queryOptions, $orderByMapping);

        $sql = $baseSql . implode(', ', $fields) . $joins . $where . $orderByAndLimit;

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

    public function filterUsersBy(
        ?string $searchText = null,
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->getUsers(
            searchText: $searchText,
            queryOptions: $queryOptions,
        );
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

        $user->id = $userId;
        return $user;
    }

    public function createNewUser(User $user): User
    {
        $resUser = $this->db->insert(self::USER_TABLE, $user->asArray());

        if (empty($resUser)) {
            $this->logger->logError('Error inserting user');
        }

        $user->id = (int)$resUser;

        return $user;
    }

    public function deleteUser(User $user): bool
    {
        return $this->db->delete(self::USER_TABLE, ['id' => $user->id]);
    }

    public function storeUserVerification(UserVerification $data): UserVerification
    {
        $res = $this->db->insert(self::USER_VERIFICATION_TABLE, $data->asArray());

        if (empty($res)) {
            $this->logger->logError('Error inserting user verification data');
        }

        $data->id = (int)$res;

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
        ?VerificationType $type = null,
        ?bool $isEnabled = null
    ): ?UserVerification {
        $sql = '
            SELECT
                u.id AS user_verification_id,
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

        if (isset($type)) {
            $sql .= ' AND u.type_id = :typeId';
            $params[':typeId'] = $type->value;
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

        if ($verification->email) {
            $data['email'] = $verification->email;
        }

        $res = $this->db->update(self::USER_TABLE, $user->id, $data);

        if (empty($res)) {
            return false;
        }

        $res2 = $this->db->update(
            self::USER_VERIFICATION_TABLE,
            $verification->id,
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

    public function updateUserJourney(int $userId, UserJourneyStatus $userJourney): bool
    {
        return $this->db->update(
            self::USER_TABLE,
            $userId,
            [
                'journey_id' => $userJourney->value,
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
                urr.id AS user_registration_request_id,
                urr.email,
                urr.language_iso_code,
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
        UserRegistrationRequest $data,
    ): UserRegistrationRequest {
        $res = $this->db->insert(self::USER_REGISTRATION_REQUEST_TABLE, $data->asArray());

        if (empty($res)) {
            $this->logger->logError('Error inserting user registration request data');
        }

        $data->id = (int)$res;

        return $data;
    }

    public function getTotalUsers(): int
    {
        return (int)$this->db->fetchColumn(
            '
                SELECT
                    COUNT(*) AS total
                FROM ' . self::USER_TABLE . ' AS u
                WHERE u.is_enabled IN (1);
            ',
        );
    }
}
