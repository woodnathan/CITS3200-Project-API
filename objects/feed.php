<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/helpers/result.php');
  
// Suppress the PHP warning
date_default_timezone_set('UTC');

abstract class APIFeedType
{
  const Breastfeed = 1;
  const Expression = 2;
  const Supplementary = 3;
}
abstract class APIFeedSubtype
{
  const Expression = 1;
  const Formula = 2;
}
abstract class APIFeedSide
{
  const Left = 1;
  const Right = 2;
}

class APIFeed
{
  protected $before_SID = null;
  protected $after_SID = null;

  protected $type; // APIFeedType
  protected $subtype; // APIFeedSubtype
  protected $side; // APIFeedSide
  protected $comment;

  protected $before_datetime;
  protected $before_weight;
  protected $after_datetime;
  protected $after_weight;

  private static $type_mapping = array(
    'B' => APIFeedType::Breastfeed,
    'E' => APIFeedType::Expression,
    'S' => APIFeedType::Supplementary
  );
  private static $subtype_mapping = array(
    'E' => APIFeedSubtype::Expression,
    'F' => APIFeedSubtype::Formula
  );
  private static $side_mapping = array(
    'L' => APIFeedSide::Left,
    'R' => APIFeedSide::Right
  );

  private static function enum_from_string($mapping, $value)
  {
    if (!empty($value))
    {
      $value = strtoupper($value[0]);
      if (array_key_exists($value, $mapping))
        return $mapping[$value];
    }
    return -1;
  }

  protected static function type_from_string($value)
  {
    return APIFeed::enum_from_string(APIFeed::$type_mapping, $value);
  }

  protected static function subtype_from_string($value)
  {
    return APIFeed::enum_from_string(APIFeed::$subtype_mapping, $value);
  }

  protected static function side_from_string($value)
  {
    return APIFeed::enum_from_string(APIFeed::$side_mapping, $value);
  }

  protected static function string_from_type($value)
  {
    switch ($value)
    {
      case APIFeedType::Breastfeed:
        return 'B';
      case APIFeedType::Expression:
        return 'E';
      case APIFeedType::Supplementary:
        return 'S';
      default:
        break;
    }
    throw new APIError(APIError::FEED_INVALID_TYPE);
  }

  protected static function string_from_subtype($value)
  {
    switch ($value)
    {
      case APIFeedSubtype::Expression:
        return 'E';
      case APIFeedSubtype::Formula:
        return 'F';
      default:
        break;
    }
    return 'U';
  }

  protected static function string_from_side($value)
  {
    switch ($value)
    {
      case APIFeedSide::Left:
        return 'L';
      case APIFeedSide::Right:
        return 'R';
      default:
        break;
    }
    return 'U';
  }

  protected static function ignore_calculation_for_type($type)
  {
    // If the feed_type is not Breastfeed, then disable the row by default
    return ($type !== APIFeedType::Breastfeed);
  }

  public function ignore_calculation()
  {
    $ignore = APIFeed::ignore_calculation_for_type($this->type);
    return ($ignore ? 'Y' : 'N');
  }

  function validate()
  {
    if (!isset($this->type))
      throw new APIError(APIError::FEED_INVALID_TYPE);

    if ($this->type == APIFeedType::Breastfeed && !isset($this->side))
      throw new APIError(APIError::FEED_SIDE_REQUIRED);

    if ($this->type == APIFeedType::Expression && !isset($this->side))
      throw new APIError(APIError::FEED_EXPRESSION_SIDE_REQUIRED);

    if ($this->type == APIFeedType::Supplementary && !isset($this->subtype))
      throw new APIError(APIError::FEED_SUBTYPE_REQUIRED);

    if (!isset($this->before_datetime) || $this->before_datetime == 0)
      throw new APIError(APIError::FEED_INVALID_BEFORE_DATE);

    if (!isset($this->after_datetime) || $this->after_datetime == 0)
      throw new APIError(APIError::FEED_INVALID_AFTER_DATE);

    if ($this->after_datetime <= $this->before_datetime)
      throw new APIError(APIError::FEED_INVALID_DATES);
  }

  protected function fill_using_JSON($json, $db)
  {
    if (isset($json->type) && !empty($json->type))
    {
      $type = mysqli_real_escape_string($db, $json->type);
      $this->type = APIFeed::type_from_string($type);
    }
    if (isset($json->subtype) && !empty($json->subtype))
    {
      $subtype = mysqli_real_escape_string($db, $json->subtype);
      $this->subtype = APIFeed::subtype_from_string($subtype);
    }
    if (isset($json->side) && !empty($json->side))
    {
      $side = mysqli_real_escape_string($db, $json->side);
      $this->side = APIFeed::side_from_string($side);
    }

    if (isset($json->comment))
    {
      $this->comment = mysqli_real_escape_string($db, $json->comment);
    }

    if (isset($json->before))
    {
      $before = $json->before;

      if (isset($before->date))
        $this->before_datetime = @strtotime(mysqli_real_escape_string($db, $before->date));

      if (isset($before->weight))
        $this->before_weight = mysqli_real_escape_string($db, $before->weight);
    }

    if (isset($json->after))
    {
      $after = $json->after;

      if (isset($after->date))
        $this->after_datetime = @strtotime(mysqli_real_escape_string($db, $after->date));
      
      if (isset($after->weight))
        $this->after_weight = mysqli_real_escape_string($db, $after->weight);
    }
  }

  public function get_value($field)
  {
    switch ($field)
    {
      case 'type':
        return APIFeed::string_from_type($this->type);
      case 'subtype':
        return APIFeed::string_from_subtype($this->subtype);
      case 'side':
        return APIFeed::string_from_side($this->side);
      case 'comment':
        return $this->comment;
      case 'before_datetime':
        return date('Y-m-d H:i:s', $this->before_datetime);
      case 'before_weight':
        return number_format($this->before_weight, 2, '.', '');
      case 'after_datetime':
        return date('Y-m-d H:i:s', $this->after_datetime);
      case 'after_weight':
        return number_format($this->after_weight, 2, '.', '');
      default:
        break;
    }
    return null;
  }

  public function get_assoc()
  {
    $assoc = array(
      'before' => array(
        'date' => date('Y-m-d\TH:i:s\Z', $this->before_datetime),
        'weight' => floatval($this->before_weight)
      ),
      'after' => array(
        'date' => date('Y-m-d\TH:i:s\Z', $this->after_datetime),
        'weight' => floatval($this->after_weight)
      ),
      'comment' => $this->comment,
      'type' => APIFeed::string_from_type($this->type),
      'subtype' => APIFeed::string_from_subtype($this->subtype),
      'side' => APIFeed::string_from_side($this->side)
    );

    if (isset($this->before_SID))
      $assoc['before']['SID'] = $this->before_SID;
    if (isset($this->after_SID))
      $assoc['after']['SID'] = $this->after_SID;

    return $assoc;
  }
}

?>