<?php

/**
 * Database connector and queries for Seminars app
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Db {
  private static $db;

  public function __construct() {
    try {
      $this->db = new PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASS']);
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
   * Query db to get a list of seminars
   *
   * @param $year {Int} default is NULL
   *     filter seminar list to only include seminars in given year
   *
   * @return {Function}
   */
  public function querySeminars ($year=NULL) {
    if ($year) {
      $filter = "$year%";
      $whereClause = ' WHERE `datetime` LIKE :filter';
    }
    $sql = "SELECT * FROM seminars_list
      $whereClause
      ORDER BY `datetime` DESC";

    return $this->_execQuery($sql, [
      'filter' => $filter
    ]);
  }
}
