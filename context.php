<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/result.php');

/**
 * @brief The APIContext class maintains the list of handlers
 *        and routes the request to the right handler
 * @details The context will also catch and handle any APIError
 *          exceptions thrown in the handler lifecycle
 */
class APIContext
{
  private $_handlers = array();

  /**
   * @brief Selects the appropriate handler and executes it
   * @return nothing
   */
  public function execute()
  {
    $result = null;
    try
    {
      if (!array_key_exists('_action', $_GET))
        throw new APIError(APIError::API_ACTION_REQUIRED);

      $action = $_GET['_action'];
      $result = $this->execute_handler($action);
    }
    catch (APIError $e)
    {
      $result = $e->result();
    }
    $result->pre_execute(); // setting headers if needed
    $result->execute(); // outputting actual content
  }

  /**
   * @brief Registers the provided handler instance against the action name
   * 
   * @param  action_name represents the _action GET request parameter
   *                     to route to the handler
   * @param  handler     an APIHandler instance to execute against
   * @return nothing
   */
  public function register_handler($action_name, APIHandler $handler)
  {
    $this->_handlers[$action_name] = $handler;
  }

  /**
   * @brief Executes the handler given the action name
   * 
   * @param  action_name the action name of the handler to execute
   * @return an APIResult instance
   */
  private function execute_handler($action_name)
  {
    if (!array_key_exists($action_name, $this->_handlers))
      throw new APIError(APIError::API_ACTION_INVALID);
    
    $handler = $this->_handlers[$action_name];

    $pre_result = $handler->pre_execute();
    $result = $pre_result;
    if (!isset($pre_result))
    {
      $result = $handler->execute();
      $handler->post_execute();
    }

    return $result;
  }
}

?>