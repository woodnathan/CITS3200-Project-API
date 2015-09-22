<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/milk/admin/scripts/db_connect.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/result.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/error.php');

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
      throw new APIError(APIError::POST_METHOD_REQUIRED);

    if (!isset($_SERVER['HTTP_X_MOTHER_USERNAME']))
      throw new APIError(APIError::USERNAME_HEADER_REQUIRED);

    if (!isset($_SERVER['HTTP_X_MOTHER_PASSWORD']))
      throw new APIError(APIError::PASSWORD_HEADER_REQUIRED);

    $db = $this->database();

    $username = $this->escape($_SERVER['HTTP_X_MOTHER_USERNAME']);
    $password = $this->escape($_SERVER['HTTP_X_MOTHER_PASSWORD']);

    $user_count_result = $db->query("SELECT COUNT(*) FROM `bbcs_v3`.`mother` WHERE MID = '$username'");
    $user_count = $user_count_result->fetch_row();
    $user_count_result->close();
    if ($user_count[0] == 0)
      throw new APIError(APIError::UNKNOWN_USER_ACCOUNT);

    $user_result = $db->query("SELECT m.MID AS MID FROM `bbcs_v3`.`mother` m INNER JOIN `bbcs_v3`.`mother_details` md ON m.MID = md.MID WHERE m.MID = '$username' AND md.password = MD5('$password')");
    if ($user_result->num_rows == 0)
      throw new APIError(APIError::INCORRECT_USER_PASSWORD);

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

class APIJSONHandler extends APIHandler
{
  public function pre_execute()
  {
    $result = parent::pre_execute();
    if (isset($result))
      return $result;

    if (stristr($_SERVER['CONTENT_TYPE'], 'json') === false)
      throw new APIError(APIError::CONTENT_TYPE_JSON_REQUIRED);

    return null;
  }

  protected function data()
  {
      $raw_data = file_get_contents('php://input');
      $data = json_decode($raw_data);
      return $data;
  }
}

?>