<?php
namespace CO2SensorAPI\Model;

class Database
{
  protected $connection = null;

  /**
   * Sets up a connection to the DB
   */
  public function __construct()
  {
    try {
      $this->connection = new \mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE_NAME);

      if (mysqli_connect_errno()) {
        throw new \Exception("Could not connect to database.");
      }
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  /**
   * Selects records from the DB according to the query
   *
   * @param string $query
   * @param array $params
   * @return mixed
   */
  public function select($query = "", $params = [])
  {
    try {
      $stmt = $this->executeStatement($query, $params);
      $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
      $stmt->close();
      return $result;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
    return false;
  }

  /**
   * Inserts and updates records from the DB according to the query
   *
   * @param string $query
   * @param array $params
   * @return mixed
   */
  public function insert($query = "", $params = [])
  {
    try {
      $stmt = $this->executeStatement($query, $params);
      $id = $stmt->insert_id;
      $stmt->close();
      return $id;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
    return false;
  }

  /**
   * Prepares and executes an SQL statement
   *
   * @param string $query
   * @param array $params
   * @return object
   */
  private function executeStatement($query = "", $params = [])
  {
    try {
      $stmt = $this->connection->prepare($query);
      if ($stmt === false) {
        throw new \Exception("Unable to do prepared statement: " . $query);
      }

      if ($params) {
        $stmt->bind_param($params[0], ...$params[1]);
      }
      $stmt->execute();
      return $stmt;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }
}
