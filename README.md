# CO2 Sensors

This is a service capable of collecting data from hundreds of thousands of sensors and alert if the CO2 concentrations reach critical levels.

### Notes
* The service won't add new sensors, so be sure that the sensors you test are on the database

## Getting Started

### Dependencies

* PHP >= 7.4.3

### Executing program

* In the root folder run
```
php -S localhost:8000
```

* The base URI is http://localhost:8000/index.html

* To run the status endpoint use http://localhost:8000/index.html/api/v1/sensors/{uuid}

## Testing

* To generate a test database and tables run
```
php Tests/database.php
```

* Test the sever using the generated sensor UUID http://localhost:8000/index.html/api/v1/sensors/966ce591-dec0-4e60-930c-41b51490687a

* In the root folder run
```
composer update
```

* Run the unit test file
```
php vendor/bin/phpunit Tests/SensorControllerTest.php
```