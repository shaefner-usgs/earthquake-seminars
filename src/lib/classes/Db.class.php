<?php

/**
 * Database connector and queries for Seminars app
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Db {
  private static $db;

  public function __construct() {
    global $DB_DSN, $DB_PASS, $DB_USER;

    try {
      $this->db = new PDO($DB_DSN, $DB_USER, $DB_PASS);
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      print '<p class="alert error">ERROR 1: ' . $e->getMessage() . '</p>';
    }
  }

  /**
   * Perform db query
   *
   * @param $sql {String}
   *     SQL query
   * @param $params {Array} default is NULL
   *     key-value substitution params for SQL query
   *
   * @return $stmt {Object} - PDOStatement object
   */
  private function _execQuery ($sql, $params=NULL) {
    try {
      $stmt = $this->db->prepare($sql);

      // bind sql params
      if (is_array($params)) {
        foreach ($params as $key => $value) {
          $type = $this->_getType($value);
          $stmt->bindValue($key, $value, $type);
        }
      }
      $stmt->execute();

      return $stmt;
    } catch(Exception $e) {
      print '<p class="alert error">ERROR 2: ' . $e->getMessage() . '</p>';
    }
  }

  /**
   * Get data type for a sql parameter (PDO::PARAM_* constant)
   *
   * @param $var {?}
   *     variable to identify type of
   *
   * @return $type {Integer}
   */
  private function _getType ($var) {
    $varType = gettype($var);
    $pdoTypes = array(
      'boolean' => PDO::PARAM_BOOL,
      'integer' => PDO::PARAM_INT,
      'NULL' => PDO::PARAM_NULL,
      'string' => PDO::PARAM_STR
    );

    $type = $pdoTypes['string']; // default
    if (isset($pdoTypes[$varType])) {
      $type = $pdoTypes[$varType];
    }

    return $type;
  }

  /**
   * Query db to get seminar committee members
   *
   * @param $who {String}
   *     defaults to current committee members only
   *
   * @return {Function}
   */
  public function queryCommittee ($who=NULL) {
    if ($who === 'all') {
      $order = ' `role` DESC, `name` ASC';
      $where = ' `role` LIKE "committee%"';
    } else {
      $order = ' `name` ASC';
      $where = ' `role` = "committee"';
    }

    $sql = "SELECT * FROM seminars_staff
      WHERE $where
      ORDER BY $order";

    return $this->_execQuery($sql);
  }

  /**
   * Query db to get past seminars w/ videos for podcast
   *
   * @return {Function}
   */
  public function queryPodcastVideos () {
    // look for seminars at least 90 mins old
    $datetime = date('Y-m-d H:i:s', strtotime('-90 mins'));

    $sql = "SELECT * FROM seminars_list
      WHERE `publish` = 'yes' AND `video` = 'yes' AND `datetime` < '$datetime'
      ORDER BY `datetime` DESC
      LIMIT 12";

    return $this->_execQuery($sql);
  }

  /**
   * Query db to get details for given seminar
   *
   * @param $id {Int}
   *
   * @return {Function}
   */
  public function querySeminar ($id) {
    $sql = 'SELECT * FROM seminars_list
      WHERE `id` = :id';

    return $this->_execQuery($sql, [
      'id' => $id
    ]);
  }

  /**
   * Query db to get a list of seminars (defaults to upcoming seminars)
   *
   * @param $filter {Mixed} default is NULL
   *     year: filter list to a given year (only past seminars included)
   *     datetime: filter list to a specific time
   *
   * @return {Function}
   */
  public function querySeminars ($filter=NULL) {
    $today = date('Y-m-d');
    $where = "`publish` = 'yes'";

    $params = [
      'today' => $today
    ];

    if ($filter) {
      if (preg_match('/^\d{4}$/', $filter)) { // year
        $params['filter'] = "$filter%";
        // Only include past seminars
        $where .= ' AND `datetime` LIKE :filter AND `datetime` < :today';
      }
      else { // assume datetime
        unset($params['today']);
        $params['filter'] = $filter;
        $where .= ' AND `datetime` = :filter';
      }
    }
    else { // default
      $where .= ' AND `datetime` >= :today';
    }

    $sql = "SELECT * FROM seminars_list
      WHERE $where
      ORDER BY `datetime` ASC";

    return $this->_execQuery($sql, $params);
  }
}
