<?php

  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handler.php');

  class APIAuthenticationHandler extends APIHandler
  {
    public function execute()
    {
      if (!array_key_exists('username', $_POST))
        return $this->error('missing username parameter');

      if (!array_key_exists('password', $_POST))
        return $this->error('missing password parameter');

      $db = $this->database();

      $username = $this->escape($_POST['username']);
      $password = $this->escape($_POST['password']);

      $user_count_result = $db->query("SELECT COUNT(*) FROM `bbcs_v3`.`mother` WHERE MID = '$username'");
      $user_count = $user_count_result->fetch_row();
      if ($user_count[0] == 0)
        return $this->error('unknown user account');

      return $this->error('authentication is not implemented');
    }
  }

?>