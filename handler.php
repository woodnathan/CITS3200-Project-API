<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/milk/admin/scripts/db_connect.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/helpers/result.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/helpers/error.php');

/**
 * @brief An abstract class for each API action
 * @details Also provides conveniences like database connection and
 *          authentication
 */
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

  /**
   * @brief The pre-execute function for the handler allows the handler
   *        to bail out of execution before it begins
   * @details The default implementation ensures HTTP POST is used, and also
   *          handles user authentication. It also stores the user ID from the
   *          database for the handler to use as a part of the execution.
   * @return null, or an APIResult instance to prevent execution
   * @throws APIError
   */
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

  /**
   * @brief Function stub for execution
   * @return An APIResult instance
   */
  public function execute()
  {

  }

  /**
   * @brief A function to be called after the execution of the handler
   * @return nothing
   */
  public function post_execute()
  {
    if ($this->_database != null)
      $this->_database->close();
  }

  /**
   * @brief A convenience method to create APIResult for an error (DEPRECATED)
   * @return An APIResult instance
   */
  protected function error($message)
  {
    return APIResult::Error($message);
  }

  /**
   * @brief Convenience method to escape a string to prevent SQL injection
   * @param  value the string to escape
   * @return an escaped string
   */
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

/**
 * @brief A subclass of APIHandler that expects a JSON HTTP body
 */
class APIJSONHandler extends APIHandler
{
  /**
   * @brief Overrides the default pre-execute function to ensure that
   *        the request's Content-Type is json
   * @return null, or an APIResult object
   */
  public function pre_execute()
  {
    $result = parent::pre_execute();
    if (isset($result))
      return $result;

    if (stristr($_SERVER['CONTENT_TYPE'], 'json') === false)
      throw new APIError(APIError::CONTENT_TYPE_JSON_REQUIRED);

    return null;
  }

  /**
   * @brief Decodes the HTTP body as JSON
   * @return An stdClass instance of the JSON decoded HTTP body
   */
  protected function data()
  {
      $raw_data = file_get_contents('php://input');
      $data = json_decode($raw_data);
      return $data;
  }
}

?>