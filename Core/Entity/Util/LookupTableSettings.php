<?php

namespace Amora\Core\Entity\Util;

use Amora\Core\Database\MySqlDb;

class LookupTableSettings
{
    public function __construct(
        private MySqlDb $database,
        private string $tableName,
        private array $tableFieldsToValues,
    ) {}

    public function getDatabase(): MySqlDb
    {
        return $this->database;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getTableFieldsToValues(): array
    {
        return $this->tableFieldsToValues;
    }
}
