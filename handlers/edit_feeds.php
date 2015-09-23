<?php

  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handler.php');
  
  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/objects/edit_feed.php');

  class APIBindParam
  { 
    private $values = array();
    private $references = array();
    private $types = ''; 
    
    public function add($type, $value)
    { 
      $this->values[] = $value;
      $this->references[] = &$this->values[count($this->values) - 1];
      $this->types .= $type; 
    } 
    
    public function get()
    {
      return array_merge(array($this->types), $this->references); 
    }
  } 

  class APIEditFeedsHandler extends APIJSONHandler
  {
    const SampleBefore = 'B';
    const SampleAfter = 'A';

    public function execute()
    {
      $data = $this->data();

      if (!isset($data->feeds) || !is_array($data->feeds))
        throw new APIError(APIError::REQUEST_FORMAT_INVALID);

      $db = $this->database();
      $motherID = $this->motherID();
      $data_feeds = $data->feeds;

      $feeds = array();
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

        // First we validate that the SIDs are paired together
        // and that the authenticated user owns them
        $this->validate_SIDs($before_SID, $after_SID);

        // Fill in the missing values on the object from the database
        // and fetch the fields that are different to the database
        $original_values = $this->fetch_original($before_SID, $after_SID);
        $feed->fill_using_original_values($original_values);

        // Now that the object's properties are fulfilled, validate it
        $feed->validate();

        array_push($feeds, $feed);
      }

      // Disable autocommit so we don't end up with half data
      $db->autocommit(false);

      $feed_assocs = array();

      foreach ($feeds as $feed)
      {
        $update_fields = $feed->fields_requiring_update();

        $before_SID = $feed->before_SID();
        $after_SID = $feed->after_SID();
        $before_update_stmt = $this->update_statement($update_fields, $feed, $before_SID, APIEditFeedsHandler::SampleBefore);
        $after_update_stmt = $this->update_statement($update_fields, $feed, $after_SID, APIEditFeedsHandler::SampleAfter);

        if ($before_update_stmt->execute() === false)
          throw new APIError(APIError::SAMPLE_SAVE_FAILED);
        if ($after_update_stmt->execute() === false)
          throw new APIError(APIError::SAMPLE_SAVE_FAILED);

        array_push($feed_assocs, $feed->get_assoc());
      }

      if ($db->commit() === false)
        throw new APIError(APIError::SAMPLE_SAVE_FAILED);

      return new APIJSONResult(array('feeds' => $feed_assocs));
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

    private function fetch_original($before_SID, $after_SID)
    {
      $query = <<<SQL
SELECT
  bsr.SID AS before_SID,
  asr.SID AS after_SID,
  bsr.weight AS before_weight,
  bsr.time AS before_datetime,
  asr.weight AS after_weight,
  asr.time AS after_datetime,
  bsr.feed_type AS type,
  bsr.complementary_type AS subtype,
  bsr.left_right AS side,
  bsr.comment AS comment,
  bsr.ignore_calc AS ignore_calc
FROM `bbcs_v3`.`sample_reading` bsr
  INNER JOIN `bbcs_v3`.`sample_reading` asr ON
  asr.fore_sid = bsr.SID
WHERE bsr.SID = ? AND  asr.SID = ?;
SQL;
      $db = $this->database();
      $stmt = $db->prepare($query);

      $stmt->bind_param('ii', $before_SID, $after_SID);
      if ($stmt->execute() === false)
        throw new APIError(APIError::SAMPLE_FETCH_FAILED);

      $stmt->bind_result(
        $before_SID,
        $after_SID,
        $before_weight,
        $before_datetime,
        $after_weight,
        $after_datetime,
        $type,
        $subtype,
        $side,
        $comment,
        $ignore_calc
      );

      $original = null;
      if ($stmt->fetch())
      {
        $original = array(
          'before_SID' => $before_SID,
          'after_SID' => $after_SID,
          'before_weight' => $before_weight,
          'before_datetime' => $before_datetime,
          'after_weight' => $after_weight,
          'after_datetime' => $after_datetime,
          'type' => $type,
          'subtype' => $subtype,
          'side' => $side,
          'comment' => $comment,
          'ignore_calc' => $ignore_calc
        );
      }

      $stmt->close();

      return $original;
    }

    private function update_statement($fields, $feed, $SID, $sample_type)
    {
      $db = $this->database();

      $params = new APIBindParam();

      $update_columns = array();
      foreach ($fields as $field)
      {
        if ($sample_type === APIEditFeedsHandler::SampleBefore && APIEditFeedsHandler::str_startswith($field, 'after_'))
          continue;
        if ($sample_type === APIEditFeedsHandler::SampleAfter && APIEditFeedsHandler::str_startswith($field, 'before_'))
          continue;

        $column_details = $this->field_to_column($field);
        $column_name = $column_details[0];
        $column_type = $column_details[1];

        $value = $feed->get_value($field);
        $params->add($column_type, $value);

        $update_column = "`$column_name` = ?";
        array_push($update_columns, $update_column);
      }

      array_push($update_columns, '`ignore_calc` = ?');
      $params->add('s', $feed->ignore_calculation());

      $update_clause = implode(', ', $update_columns);

      $sql = "UPDATE `bbcs_v3`.`sample_reading` SET $update_clause WHERE SID = ?";
      $params->add('i', $SID);

      $stmt = $db->prepare($sql);

      // We bind our dynamic params here
      // $params->get() returns an array with the first
      // element being the type string expected by $stmt->bind_param
      // and the remaining elements to be the actual values
      call_user_func_array(array($stmt, 'bind_param'), $params->get());

      return $stmt;
    }

    private static function field_to_column($field)
    {
      switch ($field)
      {
        case 'type':
          return array('feed_type', 's');
        case 'subtype':
          return array('complementary_type', 's');
        case 'side':
          return array('left_right', 's');
        case 'before_datetime':
        case 'after_datetime':
          return array('time', 's');
        case 'before_weight':
        case 'after_weight':
          return array('weight', 'd'); // d for double
        case 'comment':
          return array('comment', 's');
        default:
          break;
      }
      throw new Exception('Unimplemented field_to_column mapping');
    }

    private static function str_startswith($source, $prefix)
    {
       return strncmp($source, $prefix, strlen($prefix)) == 0;
    }
  }

?>