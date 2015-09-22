<?php

  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handler.php');
  
  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/objects/edit_feed.php');

  class APIEditFeedsHandler extends APIJSONHandler
  {
    public function execute()
    {
      $data = $this->data();

      if (!isset($data->feeds) || !is_array($data->feeds))
        throw new APIError(APIError::REQUEST_FORMAT_INVALID);

      $db = $this->database();
      $motherID = $this->motherID();
      $data_feeds = $data->feeds;

      $ids = array();
      foreach ($data_feeds as $data_feed)
      {
        $feed = new APIEditFeed($data_feed, $db);
        
        $before_SID = $feed->before_SID();
        $after_SID = $feed->after_SID();
        if (!isset($before_SID) && !isset($after_SID))
        {
          throw new APIError(APIError::SAMPLE_ONE_SID_REQUIRED);
        }
        else if (!isset($before_SID))
        {
          $before_SID = $this->before_SID_for_after_SID($after_SID);
          $feed->update_before_SID($before_SID);
        }
        else if (!isset($after_SID))
        {
          $after_SID = $this->after_SID_for_before_SID($before_SID);
          $feed->update_after_SID($after_SID);
        }

        $this->validate_SIDs($before_SID, $after_SID);

        array_push($ids, $feed->before_SID());
        array_push($ids, $feed->after_SID());
      }

      return new APIJSONResult(array('feeds' => array(), 'ids' => $ids));
    }

    private function after_SID_for_before_SID($SID)
    {
      $db = $this->database();

      $sql = "SELECT SID AS after_SID FROM `bbcs_v3`.`sample_reading` WHERE fore_sid = '$SID' AND fore_hind = 'A'";
      $result = $db->query($sql);

      if ($result->num_rows < 1) // Trying to be semi-relaxed here
        throw new APIError(APIError::SAMPLE_INVALID_BEFORE_SID);

      $row = $result->fetch_assoc();
      $after_SID = $row['after_SID'];
      $result->close();

      return $after_SID;
    }

    private function before_SID_for_after_SID($SID)
    {
      $db = $this->database();

      $sql = "SELECT fore_sid AS before_SID FROM `bbcs_v3`.`sample_reading` WHERE SID='$SID' AND fore_hind = 'A'";
      $result = $db->query($sql);

      if ($result->num_rows < 1) // Trying to be semi-relaxed here
        throw new APIError(APIError::SAMPLE_INVALID_AFTER_SID);

      $row = $result->fetch_assoc();
      $after_SID = $row['before_SID'];
      $result->close();

      return $after_SID;
    }

    private function validate_SIDs($before_SID, $after_SID)
    {
      $db = $this->database();
      $motherID = $this->motherID();

      // First verify that the SIDs are valid for each other - refer to same sample
      $sibling_verify_sql = "SELECT bes.SID AS before_SID, afs.SID AS after_SID FROM `bbcs_v3`.`sample_reading` bes INNER JOIN `bbcs_v3`.`sample_reading` afs ON afs.fore_sid = bes.SID WHERE bes.SID = '$before_SID' AND afs.SID = '$after_SID'";
      $result = $db->query($sibling_verify_sql);
      if ($result->num_rows < 1) // Trying to be semi-relaxed here
        throw new APIError(APIError::SAMPLE_SIDS_INVALID);
      $row = $result->fetch_assoc();
      if ($row['before_SID'] != $before_SID || $row['after_SID'] != $after_SID)
        throw new APIError(APIError::SAMPLE_SIDS_INVALID);
      $result->close();

      $user_verify_sql = "SELECT COUNT(*) AS count FROM `bbcs_v3`.`r_calc_feed_and_sample` WHERE MID='$motherID' AND (SID='$before_SID' OR SID='$after_SID')";
      $result = $db->query($user_verify_sql);
      if ($result->num_rows != 1)
        throw new APIError(APIError::SAMPLE_SID_PERMISSIONS);
      $row = $result->fetch_assoc();
      if ($row['count'] != 2) // There should be two rows for this
        throw new APIError(APIError::SAMPLE_SID_PERMISSIONS);
      $result->close();
    }
  }

?>