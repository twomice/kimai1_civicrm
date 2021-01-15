<?php
/**
 * This file is part of kimai1-civicrm.
 *
 * This project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; Version 3, 29 June 2007
 *
 * This project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kimai; If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Extends Kimai_Remote_Database as needed to provide more methods for kimai1-civicrm APIs.
 */
class Kimai_Remote_Database_Civicrm extends Kimai_Remote_Database
{

    /**
     * @var Kimai_Config|null
     */
    private $kga = null;

    /**
     * @var string
     */
    private $tablePrefix = null;

    /**
     * @var Kimai_Database_Mysql
     */
    private $dbLayer = null;

    /**
     * @var MySQL
     */
    private $conn = null;

    /**
     * Kimai_Remote_Database constructor.
     * @param Kimai_Config $kga
     * @param Kimai_Database_Mysql $database
     */
    public function __construct($kga, $database)
    {
        $this->kga = $kga;
        $this->dbLayer = $database;
        $this->tablePrefix = $this->dbLayer->getTablePrefix();
        $this->conn = $this->dbLayer->getConnectionHandler();

        return parent::__construct($kga, $database);
    }

    /**
     * Update server_prefix_timeSheet table
     * Create server_prefix_civicrm_queue and server_prefixcivicrm_timesheet_ever table
     *
     * @return array
     */
    public function doPrimeUpdates()
    {
        $civicrmTimesheetEver = $this->getCivicrmTimesheetEver();
        $civicrmQueue = $this->getCivicrmQueue();
        $timeSheetTable = $this->getTimeSheetTable();
        $columnToInsert = 'modified';

        // Add default messages for the tables, this will appear if tables are already created.
        $result[$civicrmTimesheetEver] = "{$civicrmTimesheetEver} table is already created.";
        $result[$civicrmQueue] = "{$civicrmQueue} table is already created.";
        $result[$timeSheetTable] = "{$timeSheetTable} table has already a `modified` column.";

        // Check table if already exist
        if (!$this->checkTableExist($civicrmTimesheetEver)) {
            // if table doesn't exist, add function to create table and see if it succeed
            $result[$civicrmTimesheetEver] = $this->addCivicrmTimesheetEver();
        }

        // Check table if already exist
        if (!$this->checkTableExist($civicrmQueue)) {
            // if table doesn't exist, add function to create table and see if it succeed
            $result[$civicrmQueue] = $this->addCivicrmQueue();
        }

        // Check column if already exist in a table
        if (!$this->checkColumnExist($timeSheetTable, $columnToInsert)) {
            // if column doesn't exist in a table, add function to insert column table and see if it succeed
            $result[$timeSheetTable] = $this->addColumnInTable($timeSheetTable, $columnToInsert);
        }

        // Return result to the primeUpdates in API
        return $result;
    }

    /**
     * @return string table name including prefix
     */
    public function getCivicrmTimesheetEver()
    {
        return $this->kga['server_prefix'] . 'civicrm_timesheet_ever';
    }

    /**
     * @return string table name including prefix
     */
    public function getCivicrmQueue()
    {
        return $this->kga['server_prefix'] . 'civicrm_queue';
    }

    /**
     * This custom function will check if table is already exist
     * @param $tableName
     * @return array
     */
    public function checkTableExist($tableName)
    {
        $query = 'SELECT * FROM ' . $tableName;
        $result = $this->conn->Query($query);

        return $result;
    }

    /**
     * This custom function will check if column is already exist in a table
     * @param $tableName
     * @param $columnName
     * @return array
     */
    public function checkColumnExist($tableName, $columnName)
    {
        $query = $query = "SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE table_name = '$tableName'
                AND column_name LIKE '$columnName'";

        $this->conn->Query($query);

        return $this->conn->RowArray(0, MYSQLI_ASSOC);
    }


    /**
     * Create custom table and return success or fail message
     *
     * @return string
     */
    public function addCivicrmTimesheetEver()
    {
        $query = "CREATE TABLE `{$this->getCivicrmTimesheetEver()}` (
            `timeEntryID` int(11) NOT NULL COMMENT 'pseudo fk to civicrm_timeSheet.timeEntryID',
            `delete_timestamp` timestamp NULL DEFAULT NULL COMMENT 'timestamp when civicrm_timeSheet.timeEntryID was discovered to be deleted, or NULL if never deleted',
            PRIMARY KEY (`timeEntryID`)
        )";

        $success = $this->conn->Query($query);

        if (!$success) {
            return "Failed to create {$this->getCivicrmTimesheetEver()} table";
        }

        return "{$this->getCivicrmTimesheetEver()} table is created.";
    }

    /**
     * Create custom table and return success or fail message
     *
     * @return string
     */
    public function addCivicrmQueue()
    {
        $query = "CREATE TABLE `{$this->getCivicrmQueue()}` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `confirmed` timestamp NULL DEFAULT NULL COMMENT 'NULL if never confirmed, or timestamp when message was confirmed as received and processed by civicrm.',
            `action` text NOT NULL COMMENT 'update or delete',
            `timeEntryID` int(10) NOT NULL,
            `start` int(10) NULL default '0',
            `end` int(10) NULL default '0',
            `duration` int(6) NULL default '0',
            `userID` int(10) NULL,
            `projectID` int(10) NULL,
            `activityID` int(10) NULL,
            `description` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
            `comment` TEXT NULL DEFAULT NULL,
            `commentType` TINYINT(1) NULL DEFAULT '0',
            `cleared` TINYINT(1) NULL DEFAULT '0',
            `location` VARCHAR(50),
            `trackingNumber` varchar(30),
            `rate` DECIMAL( 10, 2 ) NULL DEFAULT '0',
            `fixedRate` DECIMAL( 10, 2 ) DEFAULT NULL,
            `budget` DECIMAL( 10, 2 ) NULL,
            `approved` DECIMAL( 10, 2 ) NULL,
            `statusID` SMALLINT NULL,
            `billable` TINYINT NULL,
            `modified` timestamp NULL,
            INDEX ( `userID` ),
            INDEX ( `projectID` ),
            INDEX ( `activityID` )
        )";

        $success = $this->conn->Query($query);

        if (!$success) {
            return "Failed to create {$this->getCivicrmQueue()} table";
        }

        return "{$this->getCivicrmQueue()} table is created.";
    }

    /**
     * Add a column in an existing table and return success or fail message
     * @param $tableName
     * @param $columnName
     * @return string
     */
    public function addColumnInTable($tableName, $columnName)
    {
        $query = "ALTER TABLE {$tableName} ADD {$columnName} timestamp NULL ON UPDATE CURRENT_TIMESTAMP";

        $success = $this->conn->Query($query);

        if (!$success) {
            return "Failed to add $columnName column in $tableName";
        }

        return "$columnName is added as a column in $tableName";
    }

    /**
     * Update server_prefix_civicrm_timesheet_ever to the changes in server_prefix_timesheet
     * @return array
     */
    public function doGetUpdates($limit)
    {
        // Check if server_prefix_civicrm_timesheet_ever has a value in its column
        if (!$this->checkCivicrmTimesheetEverData()) {
            // Copy all data in server_prefix_timesheet to server_prefix_civicrm_timesheet_ever if there is no value
            $query = "INSERT INTO {$this->getCivicrmTimesheetEver()} (timeEntryID) SELECT timeEntryID FROM {$this->getTimeSheetTable()}";
            $this->conn->Query($query);

            return "{$this->getTimeSheetTable()} data has successfully copied in {$this->getCivicrmTimesheetEver()}";
        } else {
            // Get the latest modified value in server_prefix_civicrm_queue
            $cutoffQuery = "SELECT IFNULL (MAX(modified), 0) AS queueCutoff FROM {$this->getCivicrmQueue()}";
            $this->conn->Query($cutoffQuery);
            $queueCutoff = $this->conn->RowArray(0, MYSQLI_ASSOC);

            // Update data in server_prefix_timesheet to server_prefix_civicrm_timesheet_ever if there is no value
            // Filter new data
            $newTimeSheet = "SELECT timeEntryID FROM {$this->getCivicrmTimesheetEver()}
                UNION
                SELECT timeEntryID FROM {$this->getTimeSheetTable()}
                EXCEPT
                SELECT timeEntryID FROM {$this->getCivicrmTimesheetEver()}";

            $newtimeSheetQuery = $this->conn->Query($newTimeSheet);
            $newtimeSheetData = $this->conn->RecordsArray(MYSQLI_ASSOC);

            // Filter deleted data
            $deletedTimeSheet = "SELECT timeEntryID FROM {$this->getCivicrmTimesheetEver()}
                UNION
                SELECT timeEntryID FROM {$this->getTimeSheetTable()}
                EXCEPT
                SELECT timeEntryID FROM {$this->getTimeSheetTable()}";

            $deletedTimeSheetQuery = $this->conn->Query($deletedTimeSheet);
            $deletedTimeSheetData = $this->conn->RecordsArray(MYSQLI_ASSOC);

            if ($newtimeSheetData) {
                // Save new data
                foreach ($newtimeSheetData as $key => $data) {
                    $this->conn->InsertRow($this->getCivicrmTimesheetEver(), $newtimeSheetData[$key]);
                }
                // Add newly updated data to server_prefix_civicrm_queue and from server_prefix_timesheet
                $this->addQueueNewTimesheet($queueCutoff['queueCutoff']);
            }

            if ($deletedTimeSheetData) {
                // Update deleted data
                foreach ($deletedTimeSheetData as $key => $data) {
                    $query = "UPDATE {$this->getCivicrmTimesheetEver()} SET `delete_timestamp` = NOW() WHERE `timeEntryID` = {$data['timeEntryID']}";
                    $this->conn->Query($query);
                }
                // Add deleted data to server_prefix_civicrm_queue and delete it in server_prefix_civicrm_timesheet_ever
                $this->addQueueDeletedTimesheet($queueCutoff['queueCutoff']);
            }

            return $this->getQueuedData($limit);
        }
    }

    /**
     * Check if server_prefix_civicrm_timesheet_ever has a value in its column
     * @return array
     */
    public function checkCivicrmTimesheetEverData()
    {
        $queryCheck = "SELECT * FROM {$this->getCivicrmTimesheetEver()}";
        $this->conn->Query($queryCheck);

        return $this->conn->RowArray(0, MYSQLI_ASSOC);
    }

    /**
     * Queued newly created timesheet in server_prefix_civicrm_queue
     * @param $queueCutoff
     */
    public function addQueueNewTimesheet($queueCutoff)
    {
        $query = "INSERT INTO `{$this->getCivicrmQueue()}` (action, timeEntryID, start, end, duration, userID, projectID, activityID, description, comment, commentType, cleared, location, trackingNumber, rate, fixedRate, budget, approved, statusID, billable, modified)
            SELECT 'update', timeEntryID, start, end, duration, userID, projectID, activityID, description, comment, commentType, cleared, location, trackingNumber, rate, fixedRate, budget, approved, statusID, billable, modified
            FROM {$this->getTimeSheetTable()}
            WHERE `modified` > '{$queueCutoff}'";

        $this->conn->Query($query);
    }

    /**
     * Queued deleted timesheet in server_prefix_civicrm_queue
     * @param $queueCutoff
     */
    public function addQueueDeletedTimesheet($queueCutoff)
    {
        // Copy all deleted timesheet from server_prefix_civicrm_timesheet_ever to server_prefix_civicrm_queue
        $query = "INSERT INTO `{$this->getCivicrmQueue()}` (timeEntryID, action, modified) 
            SELECT timeEntryID, 'delete', delete_timestamp 
            FROM {$this->getCivicrmTimesheetEver()} 
            WHERE `delete_timestamp` > '{$queueCutoff}'";
        $this->conn->Query($query);

        // Delete data in server_prefix_civicrm_timesheet_ever after adding it in to server_prefix_civicrm_queue
        $deleteQuery = "DELETE FROM {$this->getCivicrmTimesheetEver()} WHERE `delete_timestamp` > '{$queueCutoff}'";
        $this->conn->Query($deleteQuery);
    }

    /**
     * Queued deleted timesheet in server_prefix_civicrm_queue
     * @param $queueCutoff
     * @return array of queued timesheet
     */
    public function getQueuedData($limit)
    {
        $query = "SELECT * FROM `{$this->getCivicrmQueue()}` WHERE `confirmed` IS NULL ORDER BY modified DESC LIMIT {$limit}";
        $this->conn->Query($query);
        $queuedData['queued_data'] = $this->conn->RecordsArray(MYSQLI_ASSOC);

        // return false if there is no queued data found
        if (!$queuedData) {
            $queuedData['queued_data'] = 0;
        }

        return $queuedData;
    }
}
