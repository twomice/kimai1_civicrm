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
class Kimai_Remote_Database_Civicrm extends Kimai_Remote_Database {

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
  public function __construct($kga, $database) {
    $this->kga = $kga;
    $this->dbLayer = $database;
    $this->tablePrefix = $this->dbLayer->getTablePrefix();
    $this->conn = $this->dbLayer->getConnectionHandler();

    return parent::__construct($kga, $database);
  }

  /**
   * create exp entry
   *
   * @param array $data
   * @return int
   */
  public function doPrimeUpdates() {
    $query = 'select database()';

    $this->conn->Query($query);

    return $this->conn->RowArray(0, MYSQLI_ASSOC);
  }

}
