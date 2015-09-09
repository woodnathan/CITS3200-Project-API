<?php

  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/context.php');
  
  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handlers/authenticate.php');
  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handlers/add_feeds.php');

  $context = new APIContext();

  $context->register_handler('authenticate', new APIAuthenticationHandler());
  $context->register_handler('add_feeds', new APIAddFeedsHandler());

  $context->execute();

?>