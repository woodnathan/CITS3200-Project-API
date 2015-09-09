<?php

  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handler.php');

  class APIAuthenticationHandler extends APIHandler
  {
    public function execute()
    {
      return $this->error('authentication is not implemented');
    }
  }

?>