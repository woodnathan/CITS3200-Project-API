<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/handler.php');

class APIGetFeedsHandler extends APIHandler
{
  public function execute()
  {
    $query = <<<SQL
SELECT
    rcfs.SNO AS SNO,
    sr.SID AS before_SID,
    srA.SID AS after_SID,
    sr.time AS before_time,
    sr.weight AS before_weight,
    srA.time AS after_time,
    srA.weight AS after_weight,
    sr.comment AS comment,
    sr.feed_type AS type,
    sr.complementary_type AS subtype,
    sr.left_right AS side
FROM `bbcs_v3`.`sample_reading` sr
    INNER JOIN `bbcs_v3`.`r_calc_feed_and_sample` rcfs
        ON rcfs.SID = sr.SID AND rcfs.MID = ?
    INNER JOIN `bbcs_v3`.`sample_reading` srA
        ON srA.fore_sid = sr.SID AND srA.fore_hind = 'A'
WHERE sr.`fore_hind` = 'B'
ORDER BY before_time, rcfs.SNO;
SQL;
    $db = $this->database();
    $mid = $this->motherID();
    $stmt = $db->prepare($query);

    $stmt->bind_param('s', $mid);
    if ($stmt->execute() === false)
      throw new APIError(APIError::SAMPLE_FETCH_FAILED);

    $stmt->bind_result(
      $sno,
      $before_SID,
      $after_SID,
      $before_time,
      $before_weight,
      $after_time,
      $after_weight,
      $comment,
      $type,
      $subtype,
      $side
    );

    $feeds = array();
    while ($stmt->fetch())
    {
      if (preg_match('/^delete/i', $comment))
        continue;
      
      array_push($feeds, array(
        "before" => array(
          "SID" => $before_SID,
          "date" => date('Y-m-d\TH:i:s\Z', @strtotime($before_time)),
          "weight" => floatval($before_weight)
        ),
        "after" => array(
          "SID" => $after_SID,
          "date" => date('Y-m-d\TH:i:s\Z', @strtotime($after_time)),
          "weight" => floatval($after_weight)
        ),
        "comment" => $comment,
        "type" => $type,
        "subtype" => $subtype,
        "side" => $side
      ));
    }


    return new APIJSONResult(array('feeds' => $feeds));
  }
}

?>