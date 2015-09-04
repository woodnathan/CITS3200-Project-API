<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/milk/admin/scripts/db_connect.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/result.php');

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

  public function post_execute()
  {
    if ($this->_database != null)
      $this->_database->close();
  }

  protected function error($message)
  {
    return APIResult::Error($message);
  }

  protected function escape($value)
  {
    $db = $this->database();
    return mysqli_real_escape_string($db, stripslashes($value));
  }
}

?>