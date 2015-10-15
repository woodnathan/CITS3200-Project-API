<?php

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

/**
 * @brief An abstract class representing base functionality for the
 *        APIEditFeed and APIAddFeed object
 * @discussion These three classes could be refactored into a single class.
 */
abstract class APIFeed
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

  /**
   * @brief This function is used to map strings to "enums"
   * @param mapping An associative array with single uppercase letter strings
   *                for the keys
   * @param value   A string that will be used to find the matching value
   * @return A value from the mapping array, or -1 if a value cannot be found
   */
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

  /**
   * @brief This function will map a string to an APIFeedType enum
   * @param value A string value representing the type
   * @return An integer value from the APIFeedType enum, or -1 if a matching
   *         type cannot be found
   */
  protected static function type_from_string($value)
  {
    return APIFeed::enum_from_string(APIFeed::$type_mapping, $value);
  }

  /**
   * @brief This function will map a string to an APIFeedSubtype enum
   * @param value A string value representing the subtype
   * @return An integer value from the APIFeedSubtype enum, or -1 if a matching
   *         type cannot be found
   */
  protected static function subtype_from_string($value)
  {
    return APIFeed::enum_from_string(APIFeed::$subtype_mapping, $value);
  }

  /**
   * @brief This function will map a string to an APIFeedSide enum
   * @param value A string value representing the side
   * @return An integer value from the APIFeedSide enum, or -1 if a matching
   *         type cannot be found
   */
  protected static function side_from_string($value)
  {
    return APIFeed::enum_from_string(APIFeed::$side_mapping, $value);
  }

  /**
   * @brief This function will map an APIFeedType enum to a string
   * @param value An integer value from the APIFeedType enum
   * @return A single uppercase letter string representing the enum value
   * @throws APIError
   */
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

  /**
   * @brief This function will map an APIFeedSubtype enum to a string
   * @param value An integer value from the APIFeedSubtype enum
   * @return A single uppercase letter string representing the enum value
   * @throws APIError
   */
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

  /**
   * @brief This function will map an APIFeedSide enum to a string
   * @param value An integer value from the APIFeedSide enum
   * @return A single uppercase letter string representing the enum value
   * @throws APIError
   */
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

  /**
   * @brief This function determines whether the feed should be ignored for
   *        calculation based on the type
   * @param type An integer value from the APIFeedType enum
   * @return true if the feed should be ignored, false otherwise
   */
  protected static function ignore_calculation_for_type($type)
  {
    // If the feed_type is not Breastfeed, then disable the row by default
    return ($type !== APIFeedType::Breastfeed);
  }

  /**
   * @brief This function determines whether the feed should be ignored for
   *        calculation based on the type
   * @return A string value: 'Y' if the feed should be ignored for calculation,
   *         or 'N' if the feed should not be ignored for calculation
   */
  public function ignore_calculation()
  {
    $ignore = APIFeed::ignore_calculation_for_type($this->type);
    return ($ignore ? 'Y' : 'N');
  }

  /**
   * @brief Validates the values of the feed
   * @return nothing
   * @throws APIError
   */
  public function validate()
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

  /**
   * @brief Sets the feed's property using the given JSON object
   * @param json An stdClass instance representing the feed data
   * @param db An mysqli instance to be used for escaping strings
   * @return nothing
   * @discussion The string escaping is really only necessary for the comment
   *             as all of the other fields are parsed/corrected in some way
   *             before being inserted into the database
   */
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

  /**
   * @brief Formats and returns the value for the field
   * @param field a string value of the following values:
   *          type, subtype, side, comment,
   *          before_datetime, before_weight,
   *          after_datetime, after_weight
   * @return a string value for the specified field, or null if the field
   *         does not exist
   */
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

  /**
   * @brief Produces an associative array format representing the feed
   * @return A nested associative array
   */
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