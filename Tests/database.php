<?php
include 'inc/config.php';

try {
  $connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD);

  if (mysqli_connect_errno()) {
    throw new Exception("Could not create initial connection.\n");
  }

  sensors_create_db($connection);
  $connection->close();

  $connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE_NAME);
  if (mysqli_connect_errno()) {
    throw new Exception("Could not create connection.\n");
  }

  sensors_create_table_sensors($connection);
  sensors_create_table_alerts($connection);
  sensors_create_table_mesurements($connection);

  sensors_add_test_sensor($connection);
  $connection->close();

} catch (Exception $e) {
  throw new Exception($e->getMessage());
}

function sensors_create_db($connection)
{
  $sql = "CREATE DATABASE " . DB_DATABASE_NAME;
  if ($connection->query($sql) === TRUE) {
    echo "Database created successfully\n";
  } else {
    throw new Exception("Error creating database: " . $connection->error . "\n");
  }
}

function sensors_create_table_sensors($connection)
{
  $sql = "CREATE TABLE Sensors (
    uuid VARCHAR(36) NOT NULL PRIMARY KEY,
    status VARCHAR(5) DEFAULT 'OK',
    risk SMALLINT DEFAULT 0
  )";

  if ($connection->query($sql) === TRUE) {
    echo "Table Sensors created successfully \n";
  } else {
    throw new Exception("Error creating table: " . $connection->error . "\n");
  }
}

function sensors_create_table_alerts($connection)
{
  $sql = "CREATE TABLE Alerts (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    startTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    endTime TIMESTAMP NULL,
    uuid VARCHAR(36),
    FOREIGN KEY (uuid) REFERENCES Sensors(uuid)
  )";

  if ($connection->query($sql) === TRUE) {
    echo "Table Alerts created successfully \n";
  } else {
    throw new Exception("Error creating table: " . $connection->error . "\n");
  }
}

function sensors_create_table_mesurements($connection)
{
  $sql = "CREATE TABLE Mesurements (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    co2 SMALLINT NOT NULL,
    time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uuid VARCHAR(36),
    alert_id INT NULL,
    FOREIGN KEY (uuid) REFERENCES Sensors(uuid),
    FOREIGN KEY (alert_id) REFERENCES Alerts(id)
  )";

  if ($connection->query($sql) === TRUE) {
    echo "Table Mesurements created successfully \n";
  } else {
    throw new Exception("Error creating table: " . $connection->error . "\n");
  }
}

function sensors_add_test_sensor($connection)
{
  $sql = "INSERT INTO Sensors (uuid)
  VALUES('966ce591-dec0-4e60-930c-41b51490687a')";

  if ($connection->query($sql) === TRUE) {
    echo "Added sensor for testing \n";
  } else {
    throw new Exception("Error adding sensor: " . $connection->error . "\n");
  }
}