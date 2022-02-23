<?php

include_once __DIR__ . '/Db.class.php'; // db connector, queries
include_once __DIR__ . '/Seminar.class.php'; // model

/**
 * ESC seminar collection.
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class SeminarCollection {
  private $_db;
  public $seminars;

  public function __construct () {
    $this->_db = new Db();
    $this->seminars = [];
  }

  /**
   * Add the given array of seminars to the collection.
   *
   * @param $seminars {Array}
   */
  private function _add ($seminars) {
    foreach($seminars as $seminar) {
      $this->seminars[] = $seminar;
    }
  }

  /**
   * Query the database for a specific seminar and return the result.
   *
   * @param $filter {String}
   *     id or datetime
   *
   * @return {Array}
   */
  private function _getSeminar ($filter) {
    $rsSeminars = $this->_db->querySeminar($filter);
    $rsSeminars->setFetchMode(PDO::FETCH_CLASS, 'Seminar');

    return $rsSeminars->fetchAll();
  }

  /**
   * Query the database for a list of seminars and return the result.
   *
   * @param $year {String} default is NULL
   *
   * @return {Array}
   */
  private function _getSeminars ($year=NULL) {
    $rsSeminars = $this->_db->querySeminars($year);
    $rsSeminars->setFetchMode(PDO::FETCH_CLASS, 'Seminar');

    return $rsSeminars->fetchAll();
  }

  /**
   * Add the 15 most recent past seminars to the collection.
   */
  public function addRecent () {
    $rsSeminars = $this->_db->queryRecent();
    $rsSeminars->setFetchMode(PDO::FETCH_CLASS, 'Seminar');

    $this->_add($rsSeminars->fetchAll());
  }

  /**
   * Add the seminar matching the given datetime to the collection.
   *
   * @param $datetime {String}
   */
  public function addSeminarAtTime ($datetime) {
    $seminars = $this->_getSeminar($datetime);

    $this->_add($seminars);
  }

  /**
   * Add the seminar matching the given id to the collection.
   *
   * @param $id {String}
   */
  public function addSeminarWithId ($id) {
    $seminars = $this->_getSeminar($id);

    $this->_add($seminars);
  }

  /**
   * Add all upcoming seminars to the collection.
   */
  public function addUpcoming () {
    $seminars = $this->_getSeminars();

    $this->_add($seminars);
  }

  /**
   * Add all seminars matching the given year to the collection (excluding
   * upcoming seminars).
   *
   * @param $year {String}
   */
  public function addYear ($year) {
    $seminars = $this->_getSeminars($year);

    $this->_add($seminars);
  }
}
