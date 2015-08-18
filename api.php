<?php

  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/context.php');
  
  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handlers/authenticate.php');

  $context = new APIContext();

  $context->register_handler('authenticate', new APIAuthenticationHandler());

  $context->execute();

?>