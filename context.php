<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/result.php');

class APIContext
{
  private $_handlers = array();

  public function execute()
  {
    $result = null;
    if (array_key_exists('_action', $_GET))
    {
      $action = $_GET['_action'];
      $result = $this->execute_handler($action);
    }
    else
    {
      $result = APIResult::Error('no action provided');
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
    if (array_key_exists($action_name, $this->_handlers))
    {
      $handler = $this->_handlers[$action_name];

      $result = $handler->execute();
      $handler->post_execute();

      return $result;
    }
    else
    {
      return APIResult::Error('invalid action provided');
    }
  }
}

?>