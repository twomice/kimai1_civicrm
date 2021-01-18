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

class Kimai_Remote_Api_Civicrm extends Kimai_Remote_Api
{
    /**
     * @var \Kimai_Remote_Database
     */
    private $backend = null;

    /**
     * @var array
     */
    private $user = null;

    /**
     * @var \Kimai_Config
     */
    private $kga = null;

    /**
     * @var Kimai_Database_Mysql
     */
    private $oldDatabase = null;

    public function __construct()
    {
        $kga = Kimai_Registry::getConfig();
        $database = Kimai_Registry::getDatabase();

        // remember the most important stuff
        $this->kga = $kga;
        $this->backend = new Kimai_Remote_Database_Civicrm($kga, $database);
        $this->oldDatabase = $database;
    }

    /**
     *
     * @param type $apiKey
     * @param type $limit Maximum number of returned queue messages.
     */
    public function getUpdates($apiKey, $limit = 25)
    {
        // if (!$this->init($apiKey, 'getUpdates')) {
        //     return $this->getAuthErrorResult();
        // }

        $row = $this->backend->doGetUpdates($limit);
        return $this->getSuccessResult($row);
    }

    /**
     * Make any required sql table alterations, skipping any that have already been done.
     * @param type $apiKey
     */
    public function primeUpdates($apiKey)
    {
        // if (!$this->init($apiKey, 'primeUpdates')) {
        //     return $this->getAuthErrorResult();
        // }

        $row = $this->backend->doPrimeUpdates();
        return $this->getSuccessResult($row);
    }

    /**
     * Record that a given queue message was received by civicrm.
     * @param type $apiKey
     * @param type $queueId
     */
    public function confirmQueueMessage($apiKey, $messageId)
    {
        // if (!$this->init($apiKey, 'confirmQueueMessage')) {
        //     return $this->getAuthErrorResult();
        // }

        $result = $this->backend->doConfirmQueueMessage($messageId);
        if ($result) {
            return $this->getSuccessResult($result);
        }

        return $this->getErrorResult('ERROR: Invalid ID');
    }

}
