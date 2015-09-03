<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/milk/admin/scripts/db_connect.php');

class APIHandler
{
  private $_database = null;

  protected function database()
  {
    if ($this->_database == null)
    {
      $this->_database = connect_db();
    }
    return $this->_database;
  }

  public function execute()
  {

  }

  protected function error($message)
  {
    $error = array('error' => $message);
    return new APIJSONResult($error);
  }
}

class APIResult
{
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