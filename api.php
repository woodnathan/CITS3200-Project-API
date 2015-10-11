<?php

  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/context.php');
  
  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handlers/authenticate.php');
  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handlers/add_feeds.php');
  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handlers/get_feeds.php');
  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handlers/user_info.php');
  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handlers/edit_feeds.php');

  $context = new APIContext();

  /**
   * Register handlers
   */
  $context->register_handler('authenticate', new APIAuthenticationHandler()); // Deprecated
  $context->register_handler('user_info', new APIUserInfoHandler());
  $context->register_handler('add_feeds', new APIAddFeedsHandler());
  $context->register_handler('get_feeds', new APIGetFeedsHandler());
  $context->register_handler('edit_feeds', new APIEditFeedsHandler());

  // This simply handles the current request
  // and maps it to the correct handler
  $context->execute();

?>