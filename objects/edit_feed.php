<?php

  require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/error.php');

  class APIEditFeed
  {
    private $before_SID = null;
    private $after_SID = null;

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
      if (isset($json->before->SID) && !empty($json->before->SID))
        $this->before_SID = intval($json->before->SID);
      if (isset($json->after->SID) && !empty($json->after->SID))
        $this->after_SID = intval($json->after->SID);
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
  }

?>