<?php
namespace CO2SensorAPI\Model;

require_once PROJECT_ROOT_PATH . "/Model/Database.php";

class SensorModel extends Database
{
  /**
   * Returns the status value of a sensor
   *
   * @param string $uuid
   * @return array
   */
  public function getStatus($uuid)
  {
    return $this->select("SELECT status FROM Sensors WHERE uuid=?", ['s', [$uuid]]);
  }

  /**
   * Returns the risk value of a sensor
   *
   * @param string $uuid
   * @return array
   */
  public function getRisk($uuid)
  {
    return $this->select("SELECT risk FROM Sensors WHERE uuid=?", ['s', [$uuid]]);
  }

  /**
   * Returns the id of the last alert registered by a sensor
   *
   * @param string $uuid
   * @return array
   */
  public function getLastAlert($uuid)
  {
    return $this->select("SELECT id FROM Alerts WHERE uuid=? ORDER BY id DESC LIMIT 1", ['s', [$uuid]]);
  }

  /**
   * Calculates and returns the max and average value of CO2 in the last 30 days
   *
   * @param string $uuid
   * @return array
   */
  public function getMetrics($uuid)
  {
    return $this->select("SELECT MAX(co2) AS 'maxLast30Days', ROUND(AVG(co2)) AS 'avgLast30Days'
    FROM Mesurements WHERE uuid=? AND DATEDIFF(NOW(), time) between 0 and 30", ["s", [$uuid]]);
  }

  /**
   * Returns the Alerts table data limiting the rows number by limit
   *
   * @param string $uuid
   * @param integer $limit
   * @return array
   */
  public function getAlerts($uuid, $limit)
  {
    return $this->select("SELECT id, startTime, endTime FROM Alerts WHERE uuid=? LIMIT ?", ["si", [$uuid, $limit]]);
  }

  /**
   * Returns the CO2 measures inside an alert
   *
   * @param integer $alert_id
   * @return array
   */
  public function getMeasurementsbyAlerts($alert_id)
  {
    return $this->select("SELECT co2 FROM Mesurements WHERE alert_id=?", ["i", [$alert_id]]);
  }

  /**
   * Inserts a new row on the Mesurements table
   *
   * @param string $uuid
   * @param integer $co2
   * @return integer
   */
  public function setMesurement($uuid, $co2)
  {
    return $this->insert("INSERT INTO Mesurements (co2, uuid) VALUES(?,?)", ['is', [$co2, $uuid]]);
  }

  /**
   * Updates a sensor status value
   *
   * @param string $uuid
   * @param string $status
   */
  public function setStatus($uuid, $status)
  {
    $this->insert("UPDATE Sensors SET status=? WHERE uuid=?", ['ss', [$status, $uuid]]);
  }

  /**
   * Updates a sensor risk value
   *
   * @param string $uuid
   * @param integer $risk
   */
  public function setRisk($uuid, $risk)
  {
    $this->insert("UPDATE Sensors SET risk=? WHERE uuid=?", ['is', [$risk, $uuid]]);
  }

  /**
   * Creates a new Alert input and associates it with the corresponding measure data
   *
   * @param string $uuid
   * @param integer $measure_id
   * @return integer
   */
  public function createAlert($uuid, $measure_id)
  {
    $alert_id = $this->insert("INSERT INTO Alerts (uuid) VALUES(?)", ['s', [$uuid]]);
    $this->insert("UPDATE Mesurements SET alert_id=? WHERE id=?", ['ii', [$alert_id, $measure_id]]);
    return $alert_id;
  }

  /**
   * Associates a measure data with an alert
   *
   * @param integer $alert_id
   * @param integer $measure_id
   */
  public function setAlert($alert_id, $measure_id)
  {
    $this->insert("UPDATE Mesurements SET alert_id=? WHERE id=?", ['ii', [$alert_id, $measure_id]]);
  }

  /**
   * Closes an alert by updating the end time
   *
   * @param integer $alert_id
   */
  public function setAlertEndTime($alert_id)
  {
    $this->insert("UPDATE Alerts SET endTime=NOW() WHERE id=?", ['i', [$alert_id]]);
  }
}
