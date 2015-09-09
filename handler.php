<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/milk/admin/scripts/db_connect.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/result.php');

class APIHandler
{
  private $_database = null;
  private $_mid = null;

  protected function database()
  {
    if ($this->_database == null)
    {
      $this->_database = connect_db();
    }
    return $this->_database;
  }

  public function pre_execute()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
      return $this->error('POST method required');

    if (!isset($_SERVER['HTTP_X_MOTHER_USERNAME']))
      return $this->error('X-Mother-Username header required');

    if (!isset($_SERVER['HTTP_X_MOTHER_PASSWORD']))
      return $this->error('X-Mother-Password header required');

    $db = $this->database();

    $username = $this->escape($_SERVER['HTTP_X_MOTHER_USERNAME']);
    $password = $this->escape($_SERVER['HTTP_X_MOTHER_PASSWORD']);

    $user_count_result = $db->query("SELECT COUNT(*) FROM `bbcs_v3`.`mother` WHERE MID = '$username'");
    $user_count = $user_count_result->fetch_row();
    if ($user_count[0] == 0)
      return $this->error('unknown user account');
    $user_count_result->close();

    $user_result = $db->query("SELECT m.MID AS MID FROM `bbcs_v3`.`mother` m INNER JOIN `bbcs_v3`.`mother_details` md ON m.MID = md.MID WHERE m.MID = '$username' AND md.password = MD5('$password')");
    if ($user_result->num_rows == 0)
      return $this->error('invalid password');

    $row = $user_result->fetch_assoc();
    $this->_mid = $row['MID'];
    $user_result->close();
    
    return null;
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

  protected function motherID()
  {
    return $this->_mid;
  }
}

?>