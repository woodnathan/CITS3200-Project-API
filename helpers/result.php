<?php

/**
 * @brief An abstract class that represents a value to be returned in the response
 */
abstract class APIResult
{
  protected $_value;

  /**
   * @brief The constructor for an APIResult object
   * @param  value The value to be returned as a part of the request
   */
  function __construct($value) {
    $this->_value = $value;
  }

  /**
   * @brief A function that is executed before data is outputted
   * @details This function is useful for setting response headers
   * @return nothing
   */
  public function pre_execute()
  {

  }

  /**
   * @brief The main execution function for the result
   * @details This function is used to output the response
   * @return nothing
   */
  abstract public function execute();
}

/**
 * @brief An APIResult subclass that is used to serialize and output JSON
 */
class APIJSONResult extends APIResult
{
  /**
   * @brief Sets the Content-Type HTTP header to application/json
   * @return nothing
   */
  public function pre_execute()
  {
    header('Content-Type: application/json');
  }

  /**
   * @brief Encodes the result value to JSON and outputs it
   * @return nothing
   */
  public function execute()
  {
    echo(json_encode($this->_value));
  }
}

/**
 * @brief An APIResult subclass that outputs the value as plain text
 */
class APITextResult extends APIResult
{
  /**
   * @brief Sets the Content-Type HTTP header to text/plain
   * @return nothing
   */
  public function pre_execute()
  {
    header('Content-Type: text/plain');
  }

  /**
   * @brief Outputs the result value
   * @return nothing
   */
  public function execute()
  {
    echo($this->_value);
  }
}

?>