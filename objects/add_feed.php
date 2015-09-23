<?php

  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/objects/feed.php');
  
  // Suppress the PHP warning
  date_default_timezone_set(@date_default_timezone_get());

  class APIAddFeed extends APIFeed
  {
    /**
     * Expects:
     *  {
     *    'type'    : 'breastfeed',
     *    'subtype' : 'formula',
     *    'side'    : 'left',
     *    'comment' : '',
     *    'before'  : { 'date' : '2015-01-01T05:00:00Z', 'weight' : 299.0 },
     *    'after'   : { 'date' : '2015-01-01T05:30:00Z', 'weight' : 300.0 }
     *  }
     */
    function __construct($json, $db)
    {
      $this->fill_using_JSON($json, $db);
    }

    /**
     * Database value accessors
     */

    function type()
    {
      return APIFeed::string_from_type($this->type);
    }

    function subtype()
    {
      return APIFeed::string_from_subtype($this->subtype);
    }

    function side()
    {
      return APIFeed::string_from_side($this->side);
    }

    function comment()
    {
      return $this->comment;
    }

    function before_datetime()
    {
      return date('Y-m-d H:i:s', $this->before_datetime);
    }

    function before_weight()
    {
      return number_format($this->before_weight, 2, '.', '');
    }

    function after_datetime()
    {
      return date('Y-m-d H:i:s', $this->after_datetime);
    }

    function after_weight()
    {
      return number_format($this->after_weight, 2, '.', '');
    }
  }

?>