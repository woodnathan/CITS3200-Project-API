<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/objects/feed.php');

/**
 * @brief A class representing a feed being edited
 * @details This class provides functionality to track which fields have been
 *          modified
 */
class APIEditFeed extends APIFeed
{
  /**
   * An array of strings representing the fields that have been updated
   */
  private $changed_fields = null;

  /**
   * Expects:
   *  {
   *    'type'    : 'breastfeed',
   *    'subtype' : 'formula',
   *    'side'    : 'left',
   *    'comment' : '',
   *    'before'  : { 'SID' : 1, 'date' : '2015-01-01T05:00:00Z', 'weight' : 299.0 },
   *    'after'   : { 'SID' : 2, 'date' : '2015-01-01T05:30:00Z', 'weight' : 300.0 }
   *  }
   */
  function __construct($json, $db)
  {
    $this->fill_using_JSON($json, $db);

    if (isset($json->before->SID) && !empty($json->before->SID))
      $this->before_SID = intval($json->before->SID);
    if (isset($json->after->SID) && !empty($json->after->SID))
      $this->after_SID = intval($json->after->SID);
  }

  /**
   * @brief This function updates the object's field named by key and keeps
   *        track of fields that were already set
   * @param values An associative array of values
   * @param unchanged_fields An array to track whether the field was unchanged
   * @param key The key representing field name, and the key for the value in
   *            values
   * @param user_func An optional array or string to call on the value before
   *                  updating the field
   */
  private function fill_field($values, &$unchanged_fields, $key, $user_func = null)
  {
    if (!isset($this->$key))
    {
      $value = $values[$key];
      if (isset($user_func))
        $value = call_user_func($user_func, $value);
      $this->$key = $value;
    }
    else
    {
      array_push($unchanged_fields, $key);
    }
  }

  /**
   * @brief Updates the object's fields with the provided values
   * @param values An associative array with the following keys:
   *                 type, subtype, side, comment,
   *                 before_datetime, before_weight,
   *                 after_datetime, after_weight,
   * @return An array of all the keys where the object's field was already set
   */
  public function fill_using_original_values($values)
  {
    $unchanged_fields = array();

    $this->fill_field($values, $unchanged_fields, 'type', array($this, 'type_from_string'));
    $this->fill_field($values, $unchanged_fields, 'subtype', array($this, 'subtype_from_string'));
    $this->fill_field($values, $unchanged_fields, 'side', array($this, 'side_from_string'));
    $this->fill_field($values, $unchanged_fields, 'comment');
    $this->fill_field($values, $unchanged_fields, 'before_datetime', 'strtotime');
    $this->fill_field($values, $unchanged_fields, 'before_weight', 'floatval');
    $this->fill_field($values, $unchanged_fields, 'after_datetime', 'strtotime');
    $this->fill_field($values, $unchanged_fields, 'after_weight', 'floatval');

    // $unchanged_fields represents fields unchanged by filling
    // in the original values; that is, those fields already had
    // values from the JSON in the constructor.
    // We want to store these fields in the $changed_fields for
    // updating only these in the database.
    $this->changed_fields = $unchanged_fields;

    return $unchanged_fields;
  }

  public function before_SID()
  {
    return $this->before_SID;
  }

  public function after_SID()
  {
    return $this->after_SID;
  }

  public function update_before_SID($value)
  {
    $this->before_SID = intval($value);
  }

  public function update_after_SID($value)
  {
    $this->after_SID = intval($value);
  }

  public function fields_requiring_update()
  {
    return $this->changed_fields;
  }

  public function get_value($field)
  {
    switch ($field)
    {
      case 'before_SID':
        return $this->before_SID;
      case 'after_SID':
        return $this->after_SID;
      default:
        break;
    }
    return parent::get_value($field);
  }
}

?>