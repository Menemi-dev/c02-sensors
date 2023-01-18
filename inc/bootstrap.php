<?php
define("PROJECT_ROOT_PATH", __DIR__ . "/../");
// include composer's autoloader
require_once PROJECT_ROOT_PATH . "/vendor/autoload.php";
// include main configuration file
require_once PROJECT_ROOT_PATH . "/inc/config.php";
// include the base controller file
require_once PROJECT_ROOT_PATH . "/Controller/BaseController.php";
// include the model files
require_once PROJECT_ROOT_PATH . "/Model/SensorModel.php";