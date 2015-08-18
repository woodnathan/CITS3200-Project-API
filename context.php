<?php

class APIContext
{
  private $_handlers = array();

  public function execute()
  {
    $action = $_GET['_action'];
    $handler = $this->get_handler($action);

    $result = $handler->execute();
    $result->execute();
  }

  public function register_handler(string $action_name, APIHandler $handler)
  {
    $_handlers[$action_name] = $handler;
  }

  private function get_handler(string $action_name)
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