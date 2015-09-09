<?php
  
  // Suppress the PHP warning
  date_default_timezone_set(@date_default_timezone_get());

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
    private $type; // APIFeedType
    private $subtype; // APIFeedSubtype
    private $side; // APIFeedSide
    private $comment;

    private $before_datetime;
    private $before_weight;
    private $after_datetime;
    private $after_weight;

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
      if (isset($json->type) && !empty($json->type))
      {
        $type = mysqli_real_escape_string($db, $json->type);
        $type = strtoupper($type[0]);
        $this->type = APIFeed::$type_mapping[$type];
      }
      if (isset($json->subtype) && !empty($json->subtype))
      {
        $subtype = mysqli_real_escape_string($db, $json->subtype);
        $subtype = strtoupper($subtype[0]);
        $this->subtype = APIFeed::$subtype_mapping[$subtype];
      }
      if (isset($json->side) && !empty($json->side))
      {
        $side = mysqli_real_escape_string($db, $json->side);
        $side = strtoupper($side[0]);
        $this->side = APIFeed::$side_mapping[$side];
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

    function validate()
    {
      if (!isset($this->type))
        throw new Exception('invalid type provided');

      if ($this->type == APIFeedType::Breastfeed && !isset($this->side))
        throw new Exception('side must be provided for type of Breastfeed');

      if ($this->type == APIFeedType::Expression && !isset($this->side))
        throw new Exception('side must be provided for type of Expression');

      if ($this->type == APIFeedType::Supplementary && !isset($this->subtype))
        throw new Exception('subtype must be provided for type of Supplementary');

      if (!isset($this->before_datetime) || $this->before_datetime == 0)
        throw new Exception('invalid before.date provided');

      if (!isset($this->after_datetime) || $this->after_datetime == 0)
        throw new Exception('invalid after.date provided');

      if ($this->after_datetime <= $this->before_datetime)
        throw new Exception('after.date must occur after before.date');
    }

    /**
     * Logic value accessors
     */
    function ignore_calculation()
    {
      // If the feed_type is not Breastfeed, then disable the row by default
      return ($this->type == APIFeedType::Breastfeed ? 'N' : 'Y');
    }

    /**
     * Database value accessors
     */

    function type()
    {
      switch ($this->type)
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
      throw new Exception('invalid type');
    }

    function subtype()
    {
      switch ($this->subtype)
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

    function side()
    {
      switch ($this->side)
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