<?php
//URI structure http://127.0.0.1:8000/index.php/api/v1/sensors/{uuid}/{method}
use CO2SensorAPI\Controller\SensorController;
require __DIR__ . "/inc/bootstrap.php";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );
if (!isset($uri[5])) {
  header("HTTP/1.1 404 Not Found");
  exit();
}
require PROJECT_ROOT_PATH . "/Controller/SensorController.php";
$objFeedController = new SensorController();
if (!isset($uri[6])) {
  $objFeedController->status($uri[5]);
} else {
  $strMethodName = $uri[6];
  $objFeedController->{$strMethodName}($uri[5]);
}