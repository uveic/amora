<?php

namespace Amora\Core\Module\User\Model;

use Amora\Core\Module\User\Value\UserActionType;
use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class UserAction
{
    public function __construct(
        public ?int $id,
        public readonly int $userId,
        public readonly ?User $createdByUser,
        public readonly UserActionType $type,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'created_by_user_id' => $this->createdByUser?->id,
            'type_id' => $this->type->value,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
        ];
    }
}
