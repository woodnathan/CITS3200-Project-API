<?php

  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handler.php');
  
  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/objects/feed.php');

  class APIAddFeedsHandler extends APIHandler
  {
    public function pre_execute()
    {
      $result = parent::pre_execute();
      if (isset($result))
        return $result;

      if (stristr($_SERVER["CONTENT_TYPE"], 'json') === false)
        throw new APIError(APIError::CONTENT_TYPE_JSON_REQUIRED);

      return null;
    }

    public function execute()
    {
      $raw_data = file_get_contents('php://input');
      $data = json_decode($raw_data);

      if (!isset($data->feeds) || !is_array($data->feeds))
        throw new APIError(APIError::REQUEST_FORMAT_INVALID);

      $db = $this->database();

      $data_feeds = $data->feeds;

      $feeds = array();
      foreach ($data_feeds as $data_feed)
      {
        $feed = new APIFeed($data_feed, $db /* for string escaping */);

        // This will throw an APIError if it doesn't validate
        $feed->validate();

        array_push($feeds, $feed);
      }

      $errors = array();
      $motherID = $this->motherID();
      foreach ($feeds as $feed)
      {
        try {
          $this->insert_sample($feed, $motherID);
        } catch (Exception $e) {
          array_push($errors, $e->getMessage());
        }
      }

      return APIResult::Error($errors);
    }

    private function insert_sample(APIFeed $feed, $mid)
    {
      $db = $this->database();

      $sql = "SELECT MAX(SID) AS max FROM `bbcs_v3`.`sample_reading`";
      $result = $db->query($sql);
      while($row = $result->fetch_assoc()) {
        $before_sno = $row['max'] + 1;
      }
      $result->close();
      $after_sno = $before_sno + 1;

      $ignore_calc = $feed->ignore_calculation();
      $type = $feed->type();
      $complementary_type = $feed->subtype();
      $left_right = $feed->side();
      $comment = $feed->comment();
      $before_datetime = $feed->before_datetime();
      $before_weight = $feed->before_weight();
      $after_datetime = $feed->after_datetime();
      $after_weight = $feed->after_weight();

      $sql = "INSERT INTO `bbcs_v3`.`sample_reading` (SID,time,weight,fore_hind,left_right,comment,feed_type,complementary_type,ignore_calc) 
        VALUES ('$before_sno','$before_datetime','$before_weight','B','$left_right','$comment','$type','$complementary_type','$ignore_calc')";
      if ($db->query($sql) === false)
        throw new APIError(APIError::SAMPLE_SAVE_FAILED);
      
      $nextid = mysqli_insert_id($db); // Returns id generated with auto_increment with last query

      $sql1 = "INSERT INTO `bbcs_v3`.`sample_reading` (SID,time,weight,fore_hind,left_right,comment,feed_type,complementary_type,ignore_calc,fore_sid) 
          values ('$after_sno','$after_datetime','$after_weight','A','$left_right','$comment','$type','$complementary_type','$ignore_calc','$nextid')";

      if ($db->query($sql1) === false)
        throw new APIError(APIError::SAMPLE_SAVE_FAILED);

      $sql_get_sno = "SELECT MAX(SNO) AS SNO FROM `bbcs_v3`.`r_calc_feed_and_sample` WHERE MID = '$mid'";
      $result = $db->query($sql_get_sno);

      $row = $result->fetch_assoc();
      $sno = $row['SNO'];
      $result->close();

      if ($sno == '0') {
        $sno = '1';
      } else {
        $sno = $sno + 1;
      }

      if ($type == 'S')
      {
        $sno = '0';
      }

      // update the timestamp in the mother's table
      $sql2 = "UPDATE `bbcs_v3`.`mother` SET last_update_at = NOW() WHERE mid = '$mid'";

      $sql_update_r_before = "INSERT INTO `bbcs_v3`.`r_calc_feed_and_sample` (MID, SID, SNO) 
                    VALUES ('$mid', '$before_sno', '$sno')";

      $sql_update_r_after = "INSERT INTO `bbcs_v3`.`r_calc_feed_and_sample` (MID, SID, SNO) 
                  VALUES ('$mid', '$after_sno', '$sno')";

      if ($db->query($sql2) === false)
        throw new Exception('Error in updating now field in mother database');

      if ($db->query($sql_update_r_before) === false)
        throw new Exception('Error with updating before feed and sample.');

      if ($db->query($sql_update_r_after) === false)
        throw new Exception('Error with updating after feed and sample.');
    }
  }

?>