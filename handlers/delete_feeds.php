<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handlers/update_feeds_base.php');

class APIDeleteFeedsHandler extends APIUpdateFeedsBaseHandler
{
  public function execute()
  {
    $db = $this->database();
    
    // Disable autocommit so we don't end up with half data
    $db->autocommit(false);

    $feeds = $this->feeds();
    foreach ($feeds as $feed)
    {
      $before_SID = $feed->before_SID();
      $after_SID = $feed->after_SID();
      $before_update_stmt = $this->delete_statement($before_SID);
      $after_update_stmt = $this->delete_statement($after_SID);

      if ($before_update_stmt->execute() === false)
        throw new APIError(APIError::SAMPLE_SAVE_FAILED);
      if ($after_update_stmt->execute() === false)
        throw new APIError(APIError::SAMPLE_SAVE_FAILED);
    }

    if ($db->commit() === false)
      throw new APIError(APIError::SAMPLE_SAVE_FAILED);

    return new APIJSONResult(array('feeds' => array()));
  }

  private function delete_statement($SID)
  {
    $db = $this->database();

    $params = new APIBindParam();

    $sql = "UPDATE `bbcs_v3`.`sample_reading` SET `comment`='delete' WHERE SID = ?";
    $params->add('i', $SID);

    $stmt = $db->prepare($sql);

    // We bind our dynamic params here
    // $params->get() returns an array with the first
    // element being the type string expected by $stmt->bind_param
    // and the remaining elements to be the actual values
    call_user_func_array(array($stmt, 'bind_param'), $params->get());

    return $stmt;
  }
}

?>