<?php

namespace Amora\Core\Database;

use Amora\Core\Database\Model\TransactionResponse;
use PDO;
use Throwable;
use Amora\Core\Logger;

final class MySqlDb
{
    private ?PDO $connection = null;
    private bool $isInTransactionMode = false;

    public function __construct(
        private Logger $logger,
        private string $host,
        private string $user,
        private string $password,
        private string $name
    ) {}

    public function getDbName(): string
    {
        return $this->name;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $this->connect();

        $stmt = $this->connection->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }

    public function fetchOne(string $sql, array $params = [])
    {
        $result = $this->fetchAll($sql, $params);
        if (is_array($result) && count($result)) {
            return $result[0];
        }

        return $result;
    }

    public function fetchColumn(string $sql, array $params = array())
    {
        $this->connect();

        $stmt = $this->connection->prepare($sql);
        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchColumn();
    }

    public function execute(string $sql, array $params = []): bool
    {
        $this->connect();

        $stmt = $this->connection->prepare($sql);
        $res = $stmt->execute($params);

        if (!$res) {
            $this->logger->logError(
                'MySql error executing query' .
                ' - Error code: ' . $stmt->errorCode()
            );
        }

        return $res;
    }

    public function insert(string $tableName, array $data): ?int
    {
        try {
            $this->connect();

            $fields = $this->getTableFields($tableName);

            $sql = "INSERT INTO " . $tableName . " SET ";
            $params = array();

            $i = 0;
            foreach ($data as $key => $value) {
                $value = is_bool($value) ? ($value ? 1 : 0) : $value;
                if (in_array($key, $fields['fields'])) {
                    if ($i > 0) {
                        $sql .= ', ';
                    }
                    $sql .= "`$key` = :$key";
                    $params[':' . $key] = $value;
                    $i++;
                }
            }

            $this->execute($sql, $params);
            $lastInsertedId = $this->connection->lastInsertId();
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error inserting entry into ' . $tableName . ' - Error message: ' . $t->getMessage()
            );
            return null;
        }

        return empty($lastInsertedId) ? null : (int)$lastInsertedId;
    }

    public function update(string $tableName, int $id, array $data): bool
    {
        $this->connect();

        $fields = $this->getTableFields($tableName);

        $sql = "UPDATE " . $tableName . " SET ";
        $params = array();

        $i = 0;
        foreach ($data as $key => $value) {
            $value = is_bool($value) ? ($value ? 1 : 0) : $value;
            if (in_array($key, $fields['fields'])) {
                if ($i > 0) {
                    $sql .= ', ';
                }
                $sql .= "`$key` = :$key";
                $params[':' . $key] = $value;
                $i++;
            }
        }

        $sql .= " WHERE " . $fields['primaryKey'] . " = :primaryKeyId";
        $params[':primaryKeyId'] = $id;

        return $this->execute($sql, $params);
    }

    public function select(
        string $tableName,
        array $fields = array(),
        array $where = array()
    ): array {
        $this->connect();

        $sql = "SELECT ";

        if ($fields) {
            $i = 0;
            foreach ($fields as $value) {
                if ($i > 0) {
                    $sql .= ", ";
                }
                $sql .= $value;
                $i++;
            }
        } else {
            $sql .= "*";
        }

        $sql .= " FROM $tableName";
        $params = array();

        if ($where) {
            $sql .= " WHERE ";
            $i = 0;
            foreach ($where as $key => $value) {
                if ($i > 0) {
                    $sql .= " AND ";
                }

                $sql .= "`$key` = :$key";
                $params[':' . $key] = $value;
                $i++;
            }
        }

        return $this->fetchAll($sql, $params);
    }

    public function delete(string $tableName, array $where): bool
    {
        if (empty($tableName) || empty($where)) {
            $this->logger->logInfo('MySqlDb::delete => Parameters not valid');

            return false;
        }

        try {
            $params = [];
            $sql = "DELETE FROM $tableName WHERE ";
            $i = 0;
            foreach ($where as $key => $value) {
                if ($i > 0) {
                    $sql .= " AND ";
                }

                $sql .= "`$key` = :$key";
                $params[':' . $key] = $value;
                $i++;
            }

            $res = $this->execute($sql, $params);
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error deleting entry from ' . $tableName . ' - Error message: ' . $t->getMessage()
            );
            return false;
        }

        return $res;
    }

    private function getTableFields(string $tableName): array
    {
        $primaryKey = null;
        $fields = null;

        $this->connect();

        $sql = "DESCRIBE " . $tableName;
        $result = $this->fetchAll($sql);

        foreach ($result as $key => $value) {
            $fields[] = $value['Field'];

            if ($value['Key'] == 'PRI') {
                $primaryKey = $value['Field'];
            }
        }

        return array(
            'primaryKey' => $primaryKey,
            'fields' => $fields
        );
    }

    public function getTables(): array
    {
        $sql = "SHOW TABLES";
        $tables = $this->fetchAll($sql);
        $result = array();

        foreach ($tables as $key => $value) {
            $result[] = array_values($value)[0];
        }

        return $result;
    }

    /**
     * Execute a function received as a parameter in transaction mode
     *
     * @param Callable $f
     * @return mixed|bool
     */
    public function withTransaction(callable $f): ?TransactionResponse
    {
        if ($this->isInTransactionMode) {
            $this->logger->logWarning(
                'A not committed transaction has been started before.' .
                ' Please, commit/roll back the previous transaction before starting a new one.' .
                ' Ignoring current request...'
            );
        }

        if (!is_callable($f)) {
            $this->logger->logError('MySqlDb transaction: provided parameter is not Callable');
            return null;
        }

        $this->beginTransaction();

        try {
            $res = $f();

            if (!$res instanceof TransactionResponse) {
                $this->logger->logError(
                    'Not valid response from callable function in MySqlDb transaction'
                );
                return null;
            }

            if (!$res->isSuccess()) {
                if ($res->getMessage()) {
                    $this->logger->logError(
                        'Rolling back transaction. Message: ' . $res->getMessage()
                    );
                }

                $this->rollBackTransaction();
                return $res;
            }

            $this->commitTransaction();
            return $res;
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error in MySqlDb transaction: ' . $t->getMessage() . PHP_EOL .
                ' - Trace: ' . $t->getTraceAsString()
            );
            $this->rollBackTransaction();
            return null;
        }
    }

    private function beginTransaction(): bool
    {
        if ($this->isInTransactionMode) {
            $this->logger->logWarning(
                'A not committed transaction has been started before.' .
                ' Please, commit/roll back the previous transaction before starting a new one.' .
                ' Ignoring current request...'
            );
            return true;
        }

        $this->connect();
        $res = $this->execute('START TRANSACTION;');

        if (!$res) {
            return false;
        }

        $this->isInTransactionMode = true;

        return true;
    }

    private function commitTransaction(): bool
    {
        if (!$this->isInTransactionMode) {
            $this->logger->logWarning(
                'You are trying to commit a transaction that has not been started before.' .
                ' Ignoring request...'
            );

            return false;
        }

        $this->connect();
        $res = $this->execute('COMMIT;');

        if (!$res) {
            return false;
        }

        $this->isInTransactionMode = false;

        return true;
    }

    private function rollBackTransaction(): bool
    {
        if (!$this->isInTransactionMode) {
            $this->logger->logError(
                'You are trying to roll back a transaction that has not been started.' .
                ' Ignoring request...'
            );

            return false;
        }

        $this->connect();
        $res = $this->execute('ROLLBACK;');

        if (!$res) {
            return false;
        }

        $this->isInTransactionMode = false;

        return true;
    }

    private function connect(): void
    {
        if (!empty($this->connection)) {
            return;
        }

        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->name}",
                $this->user,
                $this->password
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->exec("SET names utf8mb4;SET time_zone = '" . date('P') . "';");
        } catch (Throwable $t) {
            $this->logger->logInfo(
                'Error establishing DB connection: ' . $t->getMessage()
            );
        }
    }
}
