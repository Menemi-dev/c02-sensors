<?php
namespace CO2SensorAPI\Tests;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class SensorControllerTest extends TestCase
{
  private $client;

  /**
   * Creates a new Guzzle Client for HTTP testing
   */
  public function setUp(): void
  {
    $this->client = new Client(['base_uri'=>'http://127.0.0.1:8000/index.php/']);
  }

  /**
   * Sends a measure up to 2000 ppm and checks that sensor status is OK
   */
  public function testStatusOk()
  {
    $data = [
      'form_params'=>[
        'co2' => 2000
      ]
    ];

    $response = $this->client->request('POST', 'api/v1/sensors/966ce591-dec0-4e60-930c-41b51490687a/mesurements', $data);
    $this->assertEquals(200, $response->getStatusCode());
    $data = json_decode($response->getBody(true), true);
    $this->assertArrayHasKey('time', $data);

    $response = $this->client->request('GET', 'api/v1/sensors/966ce591-dec0-4e60-930c-41b51490687a');
    $data = json_decode($response->getBody(true), true);
    $this->assertEquals('OK', $data['status']);
  }

  /**
   * Sends a measure higher than 2000 ppm and checks that sensor status is set to WARN
   */
  public function testStatusWarn()
  {
    $data = [
      'form_params'=>[
        'co2' => 2100
      ]
    ];

    $response = $this->client->request('POST', 'api/v1/sensors/966ce591-dec0-4e60-930c-41b51490687a/mesurements', $data);
    $this->assertEquals(200, $response->getStatusCode());

    $response = $this->client->request('GET', 'api/v1/sensors/966ce591-dec0-4e60-930c-41b51490687a');
    $data = json_decode($response->getBody(true), true);
    $this->assertEquals('WARN', $data['status']);
  }

  /**
   * Sends two more measures higher than 2000 ppm and checks that the sensor status is set to ALERT
   */
  public function testStatusAlert()
  {
    for ($i=0; $i<2; $i++) {
      $data = [
        'form_params'=>[
          'co2' => 2100
        ]
      ];

      $response = $this->client->request('POST', 'api/v1/sensors/966ce591-dec0-4e60-930c-41b51490687a/mesurements', $data);
      $this->assertEquals(200, $response->getStatusCode());
    }

    $response = $this->client->request('GET', 'api/v1/sensors/966ce591-dec0-4e60-930c-41b51490687a');
    $data = json_decode($response->getBody(true), true);
    $this->assertEquals('ALERT', $data['status']);
  }

  /**
   * Checks if an alert was stored after the sensor reached the status ALERT
   */
  public function testAlertStart()
  {
    $response = $this->client->request('GET', 'api/v1/sensors/966ce591-dec0-4e60-930c-41b51490687a/alerts?limit=20');

    $data = json_decode($response->getBody(true), true);
    if($data != NULL) {
      $this->assertArrayHasKey('startTime', $data);
      $this->assertNotNull($data['startTime']);
      $this->assertArrayHasKey('mesurement1', $data);
      $this->assertNotNull($data['mesurement1']);
      $this->assertNull($data['endTime']);
    }
  }

  /**
   * Sends 3 measures lower than 2000 ppm and checks that the sensor status is set to OK
   */
  public function testBackToStatusOK()
  {
    for ($i=0; $i<3; $i++) {
      $data = [
        'form_params'=>[
          'co2' => 1100
        ]
      ];

      $response = $this->client->request('POST', 'api/v1/sensors/966ce591-dec0-4e60-930c-41b51490687a/mesurements', $data);
      $this->assertEquals(200, $response->getStatusCode());
    }

    $response = $this->client->request('GET', 'api/v1/sensors/966ce591-dec0-4e60-930c-41b51490687a');
    $data = json_decode($response->getBody(true), true);
    $this->assertEquals('OK', $data['status']);
  }

  /**
   * Checks that the alert was closed after changing status to OK
   */
  public function testAlertEnd()
  {
    $response = $this->client->request('GET', 'api/v1/sensors/966ce591-dec0-4e60-930c-41b51490687a/alerts');

    $data = json_decode($response->getBody(true), true);
    if($data != NULL) {
      $this->assertArrayHasKey('mesurement1', $data);
      $this->assertNotNull($data['mesurement1']);
      $this->assertNotNull($data['endTime']);
    }
  }

  /**
   * Tests the metrics calculation result
   */
  public function testMetrics()
  {
    //Test POST request on GET endpoint
    $response = $this->client->request('POST', 'api/v1/sensors/966ce591-dec0-4e60-930c-41b51490687a/metrics', ['http_errors' => false]);
    $this->assertEquals(422, $response->getStatusCode());

    $response = $this->client->request('GET', 'api/v1/sensors/966ce591-dec0-4e60-930c-41b51490687a/metrics');
    $data = json_decode($response->getBody(true), true);
    $this->assertArrayHasKey('maxLast30Days', $data);
    $this->assertEquals('2100', $data['maxLast30Days']);
    $this->assertArrayHasKey('avgLast30Days', $data);
    $this->assertEquals('1657', $data['avgLast30Days']);
  }
}