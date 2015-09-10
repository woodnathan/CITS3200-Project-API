<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/result.php');

class APIContext
{
  private $_handlers = array();

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
    $result->pre_execute();
    $result->execute();
  }

  public function register_handler($action_name, APIHandler $handler)
  {
    $this->_handlers[$action_name] = $handler;
  }

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