<?php

namespace Amora\Core\Database;

use Amora\Core\Entity\Response\Feedback;
use PDO;
use Throwable;
use Amora\Core\Util\Logger;

final class MySqlDb
{
    public function __construct(
        private readonly Logger $logger,
        public readonly string $host,
        public readonly string $user,
        public readonly string $password,
        public readonly string $name,
        private ?PDO $connection = null,
        private bool $isInTransactionMode = false,
    ) {
    }

    public function updateTimezone(): bool
    {
        return $this->execute("SET time_zone = '" . date('P') . "';");
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        try {
            $this->connect();

            $stmt = $this->connection->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            if (!empty($params)) {
                $stmt->execute($params);
            } else {
                $stmt->execute();
            }

            return $stmt->fetchAll();
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error fetching data. - Error message: ' . $t->getMessage()
                . ' - Trace: ' . $t->getTraceAsString()
            );

            return [];
        }
    }

    public function fetchOne(string $sql, array $params = []): mixed
    {
        $result = $this->fetchAll($sql, $params);
        return $result[0] ?? null;
    }

    public function fetchColumn(string $sql, array $params = [])
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
            $params = [];

            $i = 0;
            foreach ($data as $key => $value) {
                $value = is_bool($value) ? ($value ? 1 : 0) : $value;
                if (in_array($key, $fields['fields'], true)) {
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

    public function update(string $tableName, int|string $id, array $data): bool
    {
        $this->connect();

        $fields = $this->getTableFields($tableName);

        $sql = "UPDATE " . $tableName . " SET ";
        $params = [];

        $i = 0;
        foreach ($data as $key => $value) {
            $value = is_bool($value) ? ($value ? 1 : 0) : $value;
            if (in_array($key, $fields['fields'], true)) {
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
        array $fields = [],
        array $where = []
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
        $params = [];

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

        foreach ($result as $value) {
            $fields[] = $value['Field'];

            if ($value['Key'] === 'PRI') {
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
        $result = [];

        foreach ($tables as $table) {
            $result[] = array_values($table)[0];
        }

        return $result;
    }

    public function withTransaction(callable $f): Feedback
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
            return new Feedback(
                isSuccess: false,
                message: 'MySqlDb transaction: provided parameter is not Callable',
            );
        }

        $this->beginTransaction();

        try {
            $res = $f();

            if (!$res instanceof Feedback) {
                $this->logger->logError(
                    'Not valid response from callable function in MySqlDb transaction'
                );
                return new Feedback(
                    isSuccess: false,
                    message: 'Not valid response from callable function in MySqlDb transaction',
                );
            }

            if (!$res->isSuccess) {
                if ($res->message) {
                    $this->logger->logError(
                        'Rolling back transaction. Message: ' . $res->message
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
            return new Feedback(
                isSuccess: false,
                message: 'Error in MySqlDb transaction: ' . $t->getMessage(),
            );
        }
    }

    private function beginTransaction(): void
    {
        if ($this->isInTransactionMode) {
            $this->logger->logWarning(
                'A not committed transaction has been started before.' .
                ' Please, commit/roll back the previous transaction before starting a new one.' .
                ' Ignoring current request...'
            );
            return;
        }

        $this->connect();
        $res = $this->execute('START TRANSACTION;');

        if (!$res) {
            return;
        }

        $this->isInTransactionMode = true;
    }

    private function commitTransaction(): void
    {
        if (!$this->isInTransactionMode) {
            $this->logger->logWarning(
                'You are trying to commit a transaction that has not been started before.' .
                ' Ignoring request...'
            );

            return;
        }

        $this->connect();
        $res = $this->execute('COMMIT;');

        if (!$res) {
            return;
        }

        $this->isInTransactionMode = false;
    }

    private function rollBackTransaction(): void
    {
        if (!$this->isInTransactionMode) {
            $this->logger->logError(
                'You are trying to roll back a transaction that has not been started.' .
                ' Ignoring request...'
            );

            return;
        }

        $this->connect();
        $res = $this->execute('ROLLBACK;');

        if (!$res) {
            return;
        }

        $this->isInTransactionMode = false;
    }

    private function connect(): void
    {
        if ($this->connection) {
            return;
        }

        try {
            $this->connection = new PDO(
                "mysql:host=$this->host;dbname=$this->name",
                $this->user,
                $this->password,
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->exec("SET names utf8mb4;SET time_zone = '" . date('P') . "';");
        } catch (Throwable $t) {
            $this->logger->logInfo(
                'Error establishing DB connection: ' . $t->getMessage()
            );

            echo 'Error establishing DB connection...';
            die;
        }
    }
}
