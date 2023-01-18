<?php
namespace CO2SensorAPI\Controller;

class BaseController
{
  /**
   * Get querystring params.
   *
   * @return array
   */
  protected function getQueryStringParams()
  {
    parse_str($_SERVER['QUERY_STRING'], $query);
    return $query;
  }
  /**
   * Send API output.
   *
   * @param mixed $data
   * @param string $httpHeader
   */
  protected function sendOutput($data, $httpHeaders = array())
  {
    if (is_array($httpHeaders) && count($httpHeaders)) {
      foreach ($httpHeaders as $httpHeader) {
        header($httpHeader);
      }
    }
    echo $data;
    exit;
  }
}
