<?php

class APIResult
{
  public static function Error($message)
  {
    $error = array('error' => $message);
    return new APIJSONResult($error);
  }

  public function pre_execute()
  {

  }

  public function execute()
  {
    die('APIResponse::execute is an abstract method');
  }
}

class APIJSONResult extends APIResult
{
  private $_value;

  function __construct($value) {
    $this->_value = $value;
  }

  public function pre_execute()
  {
    header('Content-Type: application/json');
  }

  public function execute()
  {
    echo(json_encode($this->_value));
  }
}

?>