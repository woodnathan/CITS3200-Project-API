<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handler.php');

/**
 * @brief A deprecated handler for authentication
 */
class APIAuthenticationHandler extends APIHandler
{
  public function execute()
  {
    return new APIErrorResult(APIError::API_ACTION_INVALID, 'authenticate has been deprecated, use user_info instead');
  }
}

?>