<?php

class APIContext
{
  private $_handlers = array();

  public function execute()
  {
    $action = $_GET['_action'];
    $handler = $this->get_handler($action);

    $result = $handler->execute();
	$result->pre_execute();
    $result->execute();
  }

  public function register_handler($action_name, APIHandler $handler)
  {
    $this->_handlers[$action_name] = $handler;
  }

  private function get_handler($action_name)
  {
    if (array_key_exists($action_name, $this->_handlers))
    {
      return $this->_handlers[$action_name];
    }
    else
    {
      die("handle no handler in get_handler");
    }
  }
}

?>