<?php

  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handler.php');

  class APIUserInfoHandler extends APIHandler
  {
    public function execute()
    {
      $db = $this->database();
      $mid = $this->motherID();


      $sql = "SELECT collecting_samples, consent_form FROM `bbcs_v3`.`mother_studies` WHERE MID = '$mid'";
      $result = $db->query($sql);
      $row = $result->fetch_assoc();
      $collecting_samples = $row['collecting_samples'];
      $consent_form = $row['consent_form'];
      $result->close();

      $collecting_samples = ($collecting_samples === 'Y') ? true : false;
      $consent_form = ($consent_form === 'Y') ? true : false;

      $user_details = array(
        'collecting_samples' => $collecting_samples,
        'accepted_consent_form' => $consent_form
      );

      return new APIJSONResult(array('user' => $user_details));
    }
  }

?>