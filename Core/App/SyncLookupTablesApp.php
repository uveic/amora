<?php

namespace Amora\Core\App;

use Amora\Core\Model\Util\LookupTableSettings;
use Exception;
use Amora\Core\Database\MySqlDb;
use Amora\Core\Logger;

/**
 * Class SyncLookupTablesApp
 *
 * Sync value Classes with lookup tables in DB
 * Source of truth: PHP value classes
 *
 * Configuration file for this script in ../../bin/sync_lookup_tables.php
 *
 * Following rules must be matched:
 * - The PHP class must contain a field named 'id' which is the ID and PRIMARY KEY of the table
 * - Existing values in the DB can't be deleted
 * - Existing values in the DB 'name' field can't be reassigned to a different ID - The script will fail
 * - New values in PHP classes will be inserted
 * - Changes in existing fields will be updated
 */

final class SyncLookupTablesApp
{
    private Logger $logger;

    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }

    /**
     *
     * $lookupTablesConfig example:
     *  ['id' => 1, 'name' => 'Pending'],
     *  ['id' => 2, 'name' => 'Processing']
     *
     * @param array $lookupTablesConfig
     */
    public function run(array $lookupTablesConfig): void
    {
        if (empty($lookupTablesConfig)) {
            $this->logger->logInfo('No lookup tables to sync');
            return;
        }

        $this->logger->logInfo('Syncing lookup tables...');

        /** @var LookupTableSettings $lookupTable */
        foreach ($lookupTablesConfig as $lookupTable) {
            $this->syncLookupTable(
                $lookupTable->getDatabase(),
                $lookupTable->getTableName(),
                array_values($lookupTable->getTableFieldsToValues())
            );
        }

        $this->logger->logInfo('Lookup tables synced');
    }

    /**
     * Sync the values in the lookup table with the DB
     *
     * @param MySqlDb $db
     * @param string $tableName
     * @param array $valueByDbFieldName - { field_name_on_db: string => value: string|int }
     * @return void
     */
    private function syncLookupTable(
        MySqlDb $db,
        string $tableName,
        array $valueByDbFieldName
    ): void {
        if (empty($tableName)) {
            $this->logger->logError('Table name is empty - Check array configuration');
            exit(1);
        }

        if (empty($valueByDbFieldName[0])) {
            $this->logger->logError(
                "Field to value array is empty for this table: $tableName - Check value class"
            );
            exit(1);
        }

        if (empty($valueByDbFieldName[0]['id'])) {
            $this->logger->logError(
                "Values Class for table '$tableName' does not have an 'id' field - Aborting..."
            );
            exit(1);
        }

        if (!empty($valueByDbFieldName[0]['name'])) {
            $names = array_column($valueByDbFieldName, 'name');
            $countValuesRes = array_count_values($names);
            foreach ($countValuesRes as $key => $value) {
                if ($value > 1) {
                    $this->logger->logError(
                        "Duplicated value '$key' for field 'name' in table: '$tableName' - Aborting..."
                    );
                    exit(1);
                }
            }
        }

        $tableFields = array_keys($valueByDbFieldName[0]);

        try {
            $currentValuesInDbTable = $db->select($tableName, $tableFields);
        } catch (Exception $e) {
            $this->logger->logError(
                "Table name '$tableName' or field names '" .
                implode(', ', $tableFields) . "' misspelled or missed on DB"
            );
            exit(1);
        }

        $currentValuesInDbTableByName = [];
        $currentValuesInDbTableById = [];
        foreach ($currentValuesInDbTable as $dbValue) {
            $currentValuesInDbTableById[$dbValue['id']] = $dbValue;
            if (!empty($dbValue['name'])) {
                $currentValuesInDbTableByName[$dbValue['name']] = $dbValue['id'];
            }
        }

        $configFileValuesByName = [];
        $configFileValuesById = [];
        foreach ($valueByDbFieldName as $item) {
            if (empty($item['id'])) {
                $this->logger->logError(
                    "Values Class for table '$tableName' does not have an 'id' field - Aborting..."
                );
                exit(1);
            }
            $configFileValuesById[$item['id']] = $item;
            if (!empty($item['name'])) {
                $configFileValuesByName[$item['name']] = $item['id'];
            }
        }

        foreach ($configFileValuesByName as $name => $id) {
            if (!empty($currentValuesInDbTableByName) &&
                !empty($currentValuesInDbTableByName[$name]) &&
                $currentValuesInDbTableByName[$name] != $id
            ) {
                $this->logger->logError(
                    "Incorrect values for lookup table '$tableName' - " .
                    "Value in 'name' field has been assigned to a previously existing ID. " .
                    "Please check the class in PHP and make sure existing values haven't been " .
                    "reassigned to previously existing IDs" .
                    " - Value in DB: {$currentValuesInDbTableByName[$name]} => $name" .
                    " - Value in config file: $id => $name" .
                    " - Aborting..."
                );
                exit(1);
            }
        }

        foreach ($currentValuesInDbTableById as $rowId => $item) {
            if (empty($configFileValuesById[$rowId])) {
                // The value is on the DB but it's not on the value Class
                $this->logger->logError(
                    "Missed field ID '$rowId' in table '$tableName' - Aborting..."
                );
                exit(1);
            }
        }

        foreach ($configFileValuesById as $rowId => $item) {
            if (empty($currentValuesInDbTableById[$rowId])) {
                // The value is in the Class but not in the DB
                $this->insertRowInDb($db, $tableName, $item);
            } else {
                // The value is in both (DB and Class)
                $this->compareAndUpdate(
                    $rowId,
                    $currentValuesInDbTableById[$rowId],
                    $item,
                    $tableName,
                    $db
                );
            }
        }
    }

    /**
     * Compare current values in DB with values in config file - Update field if necessary
     *
     * @param int|string $rowId
     * @param array $ours - Values in DB
     * @param array $theirs - Values in config file
     * @param string $tableName
     * @param MySqlDb $db
     * @return boolean - Always true
     */
    private function compareAndUpdate(
        int|string $rowId,
        array $ours,
        array $theirs,
        string $tableName,
        MySqlDb $db
    ): bool {
        if (count($ours) !== count($theirs)) {
            $this->logger->logError(
                "Number of fields in DB and configuration file doesn't match for this table: $tableName"
            );
            exit(1);
        }

        foreach ($theirs as $fieldName => $fieldValue) {
            if ($fieldName != 'id' && $ours[$fieldName] != $theirs[$fieldName]) {
                $db->update($tableName, $rowId, [$fieldName => $fieldValue]);

                $this->logger->logInfo(
                    "Updating row ID '$rowId' in '$tableName' - Field: $fieldName - New value: {$theirs[$fieldName]}"
                );
            }
        }

        return true;
    }

    private function insertRowInDb(MySqlDb $db, string $tableName, array $values): bool
    {
        $db->insert($tableName, $values);
        $this->logger->logInfo(
            "Inserting row in $tableName - Fields: " .
            implode(', ', array_keys($values)) .
            " - Values: " . implode(', ', array_values($values))
        );

        return true;
    }
}
