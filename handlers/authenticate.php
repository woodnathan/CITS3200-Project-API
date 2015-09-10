<?php

  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handler.php');

  class APIAuthenticationHandler extends APIHandler
  {
    public function execute()
    {
      return APIResult::Error('authentication is not implemented');
    }
  }

?>