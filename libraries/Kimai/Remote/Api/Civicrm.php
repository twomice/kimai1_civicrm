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
     *
     * @param type $apiKey
     * @param type $projectIds
     * @param type $minTimestamp
     * @param type $limit
     */
    public function getUpdates($apiKey, $projectIds, $minTimestamp, $limit = 25)
    {

    }

    /**
     * Make any required sql table alterations, skipping any that have already been done.
     * @param type $apiKey
     */
    public function primeTables($apiKey)
    {

    }

    /**
     * Record that a given queue message was received by civicrm.
     * @param type $apiKey
     * @param type $queueId
     */
    public function updateQueue($apiKey, $queueId)
    {

    }

}
