<?php
namespace CO2SensorAPI\Controller;
use CO2SensorAPI\Model\SensorModel;

class SensorController extends BaseController
{
  /**
   * Get sensor status
   *
   * @param string $uuid
   */
  public function status($uuid)
  {
    $strErrorDesc = '';
    $requestMethod = $_SERVER["REQUEST_METHOD"];
    if (strtoupper($requestMethod) == 'GET') {
      try {
        $sensorModel = new SensorModel();
        $result = $sensorModel->getStatus($uuid);
        $responseData = json_encode($result[0]);
      } catch (\Error $e) {
        $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
        $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
      }
    } else {
      $strErrorDesc = 'Method not supported';
      $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
    }
    if (!$strErrorDesc) {
      $this->sendOutput(
        $responseData,
        array('Content-Type: application/json', 'HTTP/1.1 200 OK')
      );
    } else {
      $this->sendOutput(
        json_encode(array('error' => $strErrorDesc)),
        array('Content-Type: application/json', $strErrorHeader)
      );
    }
  }

  /**
   * Get sensor metrics
   *
   * @param string $uuid
   */
  public function metrics($uuid)
  {
    $strErrorDesc = '';
    $requestMethod = $_SERVER["REQUEST_METHOD"];
    if (strtoupper($requestMethod) == 'GET') {
      try {
        $sensorModel = new SensorModel();
        $result = $sensorModel->getMetrics($uuid);
        $responseData = json_encode($result[0]);
      } catch (\Error $e) {
        $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
        $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
      }
    } else {
      $strErrorDesc = 'Method not supported';
      $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
    }
    if (!$strErrorDesc) {
      $this->sendOutput(
        $responseData,
        array('Content-Type: application/json', 'HTTP/1.1 200 OK')
      );
    } else {
      $this->sendOutput(
        json_encode(array('error' => $strErrorDesc)),
        array('Content-Type: application/json', $strErrorHeader)
      );
    }
  }

  /**
   * Get sensor alerts
   *
   * @param string $uuid
   */
  public function alerts($uuid)
  {
    $strErrorDesc = '';
    $requestMethod = $_SERVER["REQUEST_METHOD"];
    $arrQueryStringParams = $this->getQueryStringParams();
    if (strtoupper($requestMethod) == 'GET') {
      try {
        $sensorModel = new SensorModel();
        $intLimit = 30;
        if (isset($arrQueryStringParams['limit']) && $arrQueryStringParams['limit']) {
          $intLimit = $arrQueryStringParams['limit'];
        }
        $arrAlerts = $sensorModel->getAlerts($uuid, $intLimit);
        $result = [];
        foreach($arrAlerts as $alert){
          $result = ['startTime'=>$alert['startTime'], 'endTime'=>$alert['endTime']];
          $measures = $sensorModel->getMeasurementsbyAlerts($alert['id']);
          foreach($measures as $key => $measure){
            $ind = $key+1;
            $result['mesurement'. $ind] = $measure['co2'];
          }
        }
        $responseData = json_encode($result);
      } catch (\Error $e) {
        $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
        $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
      }
    } else {
      $strErrorDesc = 'Method not supported';
      $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
    }
    if (!$strErrorDesc) {
      $this->sendOutput(
        $responseData,
        array('Content-Type: application/json', 'HTTP/1.1 200 OK')
      );
    } else {
      $this->sendOutput(
        json_encode(array('error' => $strErrorDesc)),
        array('Content-Type: application/json', $strErrorHeader)
      );
    }
  }

  /**
   * Creates sensor mesurements on DB and updates sensor status and alerts
   *
   * @param string $uuid
   */
  public function mesurements($uuid)
  {
    $strErrorDesc = '';
    $requestMethod = $_SERVER["REQUEST_METHOD"];
    if (strtoupper($requestMethod) == 'POST') {
      if(!empty($_POST['co2'])) {
        try {
          $sensorModel = new SensorModel();
          $co2 = (int)$_POST['co2'];
          $measure_id = $sensorModel->setMesurement($uuid, $co2);

          $risk = $sensorModel->getRisk($uuid)[0]['risk'];
          $status = $sensorModel->getStatus($uuid)[0]['status'];
          if($co2 > 2000){
            if($risk == 0) $sensorModel->setStatus($uuid, 'WARN');
            if($risk == 2 ) {
              if($status != 'ALERT') {
                $sensorModel->createAlert($uuid, $measure_id);
                $sensorModel->setStatus($uuid, 'ALERT');
                $status = 'ALERT';
              }
            }
            if($risk < 3 ) $risk++;
          } else {
            if($risk > 0 ) $risk--;
            if($risk == 0 ) {
              if($status == 'ALERT') {
                $alert_id = $sensorModel->getLastAlert($uuid)[0]['id'];
                $sensorModel->setAlertEndTime($alert_id);
              }
              $sensorModel->setStatus($uuid, 'OK');
              $status = 'OK';
            }
          }
          if($status == 'ALERT') {
            $alert_id = $sensorModel->getLastAlert($uuid)[0]['id'];
            $sensorModel->setAlert($alert_id, $measure_id);
          }
          $sensorModel->setRisk($uuid, $risk);
        } catch (\Error $e) {
          $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
          $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
        }
      } else {
        $strErrorDesc = 'Missing co2 measure value';
        $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
      }
    } else {
      $strErrorDesc = 'Method not supported';
      $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
    }
    if (!$strErrorDesc) {
      $this->sendOutput(
        json_encode(array('co2' => $co2, 'time'=>date('Y-m-d H:i:s'))),
        array('Content-Type: application/json', 'HTTP/1.1 200 OK')
      );
    } else {
      $this->sendOutput(
        json_encode(array('error' => $strErrorDesc)),
        array('Content-Type: application/json', $strErrorHeader)
      );
    }
  }
}
