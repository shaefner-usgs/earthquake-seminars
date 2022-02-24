<?php

/**
 * Database connector and queries.
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Db {
  private $_db;

  public function __construct() {
    global $DB_DSN, $DB_PASS, $DB_USER;

    try {
      $this->_db = new PDO($DB_DSN, $DB_USER, $DB_PASS);
      $this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      print '<p class="alert error">ERROR: ' . $e->getMessage() . '</p>';
    }
  }

  /**
   * Perform a db query.
   *
   * @param $sql {String}
   *     SQL query
   * @param $params {Array} default is NULL
   *     key-value substitution params for SQL query
   *
   * @return $stmt {Object}
   *     PDOStatement object upon success
   */
  private function _execQuery ($sql, $params=NULL) {
    try {
      $stmt = $this->_db->prepare($sql);

      // bind SQL params
      if (is_array($params)) {
        foreach ($params as $key => $value) {
          $type = $this->_getType($value);

          $stmt->bindValue($key, $value, $type);
        }
      }

      $stmt->execute();

      return $stmt;
    } catch(Exception $e) {
      print '<p class="alert error">ERROR: ' . $e->getMessage() . '</p>';
    }
  }

  /**
   * Get the data type for a SQL parameter (PDO::PARAM_* constant).
   *
   * @param $var {?}
   *     variable to identify type of
   *
   * @return $type {Integer}
   */
  private function _getType ($var) {
    $pdoTypes = [
      'boolean' => PDO::PARAM_BOOL,
      'integer' => PDO::PARAM_INT,
      'NULL' => PDO::PARAM_NULL,
      'string' => PDO::PARAM_STR
    ];
    $type = $pdoTypes['string']; // default
    $varType = gettype($var);

    if (isset($pdoTypes[$varType])) {
      $type = $pdoTypes[$varType];
    }

    return $type;
  }

  /**
   * Query the db to get the seminar committee members.
   *
   * @param $who {String} default is NULL
   *     defaults to current committee members only
   *
   * @return {Function}
   */
  public function queryCommittee ($who=NULL) {
    if ($who === 'all') {
      $order = '`role` DESC, `name` ASC';
      $where = '`role` LIKE "committee%"';
    } else {
      $order = '`name` ASC';
      $where = '`role` = "committee"';
    }

    $sql = "SELECT * FROM seminars_staff
      WHERE $where
      ORDER BY $order";

    return $this->_execQuery($sql);
  }

  /**
   * Query the db to get the 15 most recent past seminars for the podcast feed.
   *
   * @return {Function}
   */
  public function queryRecent () {
    $datetime = date('Y-m-d H:i:s', strtotime('-90 mins')); // 90+ min ago
    $sql = "SELECT * FROM seminars_list
      WHERE `publish` = 'yes' AND `video` = 'yes' AND `datetime` < '$datetime'
      ORDER BY `datetime` DESC
      LIMIT 15";

    return $this->_execQuery($sql);
  }

  /**
   * Query the db to get the seminar that matches the given filter.
   *
   * @param $filter {String}
   *     seminar id or datetime
   *
   * @return {Function}
   */
  public function querySeminar ($filter) {
    $params = [
      'filter' => $filter
    ];
    $where = '`datetime` = :filter'; // default

    if (preg_match('/^\d+$/', $filter)) { // id value
      $where = '`id` = :filter';
    }

    $sql = "SELECT * FROM seminars_list WHERE $where";

    return $this->_execQuery($sql, $params);
  }

  /**
   * Query the db to get a list of all seminars for the given year (excluding
   * upcoming seminars if $year is the current year). The list of upcoming
   * seminars is returned by default.
   *
   * @param $year {String} default is NULL
   *
   * @return {Function}
   */
  public function querySeminars ($year=NULL) {
    $params = [
      'today' => date('Y-m-d')
    ];
    $where = '`publish` = "yes"';

    if (preg_match('/^\d{4}$/', $year)) {
      $params['today'] .= ' 23:59:59'; // include today's seminar
      $params['year'] = "$year%";
      $where .= ' AND `datetime` LIKE :year AND `datetime` < :today';
    } else { // default
      $where .= ' AND `datetime` >= :today';
    }

    $sql = "SELECT * FROM seminars_list
      WHERE $where
      ORDER BY `datetime` ASC";

    return $this->_execQuery($sql, $params);
  }
}
