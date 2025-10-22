<?php

namespace Amora\Core\Module\User\Datalayer;

use Amora\App\Value\AppUserRole;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Module\User\Model\UserAction;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Module\User\Value\UserStatus;
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

    public const string USER_TABLE = 'core_user';
    private const string USER_VERIFICATION_TABLE = 'core_user_verification';
    public const string USER_VERIFICATION_TYPE_TABLE = 'core_user_verification_type';
    private const string USER_REGISTRATION_REQUEST_TABLE = 'core_user_registration_request';

    public const string USER_STATUS_TABLE = 'core_user_status';
    public const string USER_ROLE_TABLE = 'core_user_role';
    public const string USER_JOURNEY_STATUS_TABLE = 'core_user_journey_status';

    private const string USER_ACTION_TABLE = 'core_user_action';
    public const string USER_ACTION_TYPE_TABLE = 'core_user_action_type';

    public function __construct(
        private readonly MySqlDb $db,
        private readonly Logger $logger,
    ) {
    }

    public function filterUserBy(
        ?bool $includeDisabled = true,
        ?int $userId = null,
        ?string $email = null,
        ?string $searchText = null,
        ?string $identifier = null,
        array $statusIds = [],
        array $roleIds = [],
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
            'u.status_id AS user_status_id',
            'u.language_iso_code AS user_language_iso_code',
            'u.role_id AS user_role_id',
            'u.journey_id AS user_journey_id',
            'u.created_at AS user_created_at',
            'u.updated_at AS user_updated_at',
            'u.email AS user_email',
            'u.name AS user_name',
            'u.password_hash AS user_password_hash',
            'u.bio AS user_bio',
            'u.identifier AS user_identifier',
            'u.timezone AS user_timezone',
            'u.change_email_to AS user_change_email_to',
        ];

        $joins = ' FROM ' . self::USER_TABLE . ' AS u';
        $where = ' WHERE 1';

        if (!$includeDisabled) {
            $where .= ' AND u.status_id IN (:enabled)';
            $params[':enabled'] = UserStatus::Enabled->value;
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

        if (isset($identifier)) {
            $where .= ' AND u.identifier = :identifier';
            $params[':identifier'] = $identifier;
        }

        if ($statusIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $statusIds, 'u.status_id', 'userStatusId');
        }

        if ($roleIds) {
            $where .= $this->generateWhereSqlCodeForIds($params, $roleIds, 'u.role_id', 'userRoleId');
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

    public function updateUser(User $user, int $userId): ?User
    {
        $userArray = $user->asArray();
        unset($userArray['created_at'], $userArray['id']);
        $res = $this->db->update(self::USER_TABLE, $userId, $userArray);

        if (empty($res)) {
            $this->logger->logError('Error updating user. User ID: ' . $userId);
            return null;
        }

        $user->id = $userId;
        return $user;
    }

    public function storeUser(User $user): ?User
    {
        $newId = $this->db->insert(self::USER_TABLE, $user->asArray());

        if (!$newId) {
            $this->logger->logError('Error inserting user');
        }

        $user->id = (int)$newId;

        return $user;
    }

    public function deleteUser(User $user): bool
    {
        return $this->db->delete(self::USER_TABLE, ['id' => $user->id]);
    }

    public function storeUserVerification(UserVerification $data): ?UserVerification
    {
        $res = $this->db->insert(self::USER_VERIFICATION_TABLE, $data->asArray());

        if (!$res) {
            $this->logger->logError('Error inserting user verification data');
            return null;
        }

        $data->id = $res;

        return $data;
    }

    public function disableAllVerificationsForUserId(int $userId, VerificationType $verificationType): bool
    {
        return $this->db->execute(
            '
                UPDATE ' . self::USER_VERIFICATION_TABLE . '
                SET is_enabled = 0
                WHERE user_id = :userId
                    AND type_id = :verificationTypeId
            ',
            [
                ':userId' => $userId,
                ':verificationTypeId' => $verificationType->value,
            ],
        );
    }

    public function markVerificationAsVerified(int $verificationId): bool
    {
        return $this->db->update(
            tableName: self::USER_VERIFICATION_TABLE,
            id: $verificationId,
            data: [
                'is_enabled' => 0,
                'verified_at' => DateUtil::getCurrentDateForMySql()
            ],
        );
    }

    public function getUserVerification(
        string $verificationIdentifier,
        ?VerificationType $type = null,
        ?bool $isEnabled = null,
    ): ?UserVerification {
        $sql = '
            SELECT
                uv.id AS user_verification_id,
                uv.user_id,
                uv.type_id,
                uv.email,
                uv.created_at,
                uv.verified_at,
                uv.verification_identifier,
                uv.is_enabled
            FROM ' . self::USER_VERIFICATION_TABLE . ' AS uv
            WHERE 1
                AND uv.verification_identifier = :verificationIdentifier
        ';

        $params = [
            ':verificationIdentifier' => $verificationIdentifier
        ];

        if (isset($type)) {
            $sql .= ' AND uv.type_id = :typeId';
            $params[':typeId'] = $type->value;
        }

        if (isset($isEnabled)) {
            $sql .= ' AND uv.is_enabled = :isEnabled';
            $params[':isEnabled'] = $isEnabled ? 1 : 0;
        }

        $res = $this->db->fetchOne($sql, $params);

        return $res ? UserVerification::fromArray($res) : null;
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

        if (!$res) {
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
                WHERE u.status_id IN (:statusEnabled, :statusDisabled);
            ',
            [
                ':statusEnabled' => UserStatus::Enabled->value,
                ':statusDisabled' => UserStatus::Disabled->value,
            ]
        );
    }

    public function updateUserFields(
        int $userId,
        ?UserStatus $newStatus = null,
        UserRole|AppUserRole|null $newRole = null,
        ?UserJourneyStatus $newJourneyStatus = null,
        ?string $newEmail = null,
        ?string $newChangeEmailTo = null,
        bool $deleteChangeEmailTo = false,
        ?string $newHashedPassword = null,
    ): bool {
        $params = [];
        $fields = [];

        if ($newStatus) {
            $fields[] = 'status_id = :newStatusId';
            $params[':newStatusId'] = $newStatus->value;
        }

        if ($newRole) {
            $fields[] = 'role_id = :newRoleId';
            $params[':newRoleId'] = $newRole->value;
        }

        if ($newJourneyStatus) {
            $fields[] = 'journey_id = :newJourneyStatusId';
            $params[':newJourneyStatusId'] = $newJourneyStatus->value;
        }

        if ($newEmail) {
            $fields[] = 'email = :newEmail';
            $params[':newEmail'] = $newEmail;
        }

        if ($deleteChangeEmailTo) {
            $fields[] = 'change_email_to = :newChangeEmailTo';
            $params[':newChangeEmailTo'] = null;
        } elseif ($newChangeEmailTo) {
            $fields[] = 'change_email_to = :newChangeEmailTo';
            $params[':newChangeEmailTo'] = $newChangeEmailTo;
        }

        if ($newHashedPassword) {
            $fields[] = 'password_hash = :hashedPassword';
            $params[':hashedPassword'] = $newHashedPassword;
        }

        if (!$params) {
            return true;
        }

        $params[':userId'] = $userId;
        $params[':updatedAt'] = DateUtil::getCurrentDateForMySql();
        $fields[] = 'updated_at = :updatedAt';

        $sql = '
            UPDATE ' . self::USER_TABLE . '
            SET ' . implode(',', $fields) . '
            WHERE id = :userId
        ';

        return $this->db->execute($sql, $params);
    }

    public function storeUserAction(UserAction $item): ?UserAction
    {
        $newId = $this->db->insert(self::USER_ACTION_TABLE, $item->asArray());

        if (!$newId) {
            $this->logger->logError('Error inserting user action data');
            return null;
        }

        $item->id = $newId;

        return $item;
    }
}
