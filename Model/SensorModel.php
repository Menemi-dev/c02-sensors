<?php
namespace CO2SensorAPI\Model;

require_once PROJECT_ROOT_PATH . "/Model/Database.php";

class SensorModel extends Database
{
  public function getStatus($uuid)
  {
    return $this->select("SELECT status FROM Sensors WHERE uuid=?", ['s', [$uuid]]);
  }
  public function getRisk($uuid)
  {
    return $this->select("SELECT risk FROM Sensors WHERE uuid=?", ['s', [$uuid]]);
  }
  public function getLastAlert($uuid)
  {
    return $this->select("SELECT id FROM Alerts WHERE uuid=? ORDER BY id DESC LIMIT 1", ['s', [$uuid]]);
  }
  public function getMetrics($uuid)
  {
    return $this->select("SELECT MAX(co2) AS 'maxLast30Days', ROUND(AVG(co2)) AS 'avgLast30Days'
    FROM Mesurements WHERE uuid=? ORDER BY id DESC LIMIT ?", ["si", [$uuid, 30]]);
  }
  public function getAlerts($uuid, $limit)
  {
    return $this->select("SELECT id, startTime, endTime FROM Alerts WHERE uuid=? LIMIT ?", ["si", [$uuid, $limit]]);
  }
  public function getMeasurementsbyAlerts($alert_id)
  {
    return $this->select("SELECT co2 FROM Mesurements WHERE alert_id=?", ["i", [$alert_id]]);
  }
  public function setMesurement($uuid, $co2)
  {
    return $this->insert("INSERT INTO Mesurements (co2, uuid)
        VALUES(?,?)", ['is', [$co2, $uuid]]);
  }
  public function setStatus($uuid, $status)
  {
    return $this->insert("UPDATE Sensors SET status=? WHERE uuid=?", ['ss', [$status, $uuid]]);
  }
  public function setRisk($uuid, $risk)
  {
    return $this->insert("UPDATE Sensors SET risk=? WHERE uuid=?", ['is', [$risk, $uuid]]);
  }
  public function createAlert($uuid, $measure_id)
  {
    $alert_id = $this->insert("INSERT INTO Alerts (uuid) VALUES(?)", ['s', [$uuid]]);
    $this->insert("UPDATE Mesurements SET alert_id=? WHERE id=?", ['ii', [$alert_id, $measure_id]]);
    return $alert_id;
  }
  public function setAlert($alert_id, $measure_id)
  {
    return $this->insert("UPDATE Mesurements SET alert_id=? WHERE id=?", ['ii', [$alert_id, $measure_id]]);
  }
  public function setAlertEndTime($alert_id)
  {
    return $this->insert("UPDATE Alerts SET endTime='" . date('Y-m-d H:i:s') . "' WHERE id=?", ['i', [$alert_id]]);
  }
}
