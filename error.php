<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/result.php');

class APIErrorResult extends APIJSONResult
{
  function __construct($code, $message)
  {
    $value = array(
      'error' => array(
        'code' => intval($code),
        'message' => strval($message)
      )
    );
    parent::__construct($value);
  }
}

class APIError extends Exception
{
  const POST_METHOD_REQUIRED = 100;
  const API_ACTION_REQUIRED = 101;
  const API_ACTION_INVALID = 103;
  const USERNAME_HEADER_REQUIRED = 104;
  const PASSWORD_HEADER_REQUIRED = 105;
  const CONTENT_TYPE_JSON_REQUIRED = 106;
  const REQUEST_FORMAT_INVALID = 107;

  const UNKNOWN_USER_ACCOUNT = 200;
  const INCORRECT_USER_PASSWORD = 201;

  const FEED_INVALID_TYPE = 300;
  const FEED_SIDE_REQUIRED = 301;
  const FEED_EXPRESSION_SIDE_REQUIRED = 302;
  const FEED_SUBTYPE_REQUIRED = 303;
  const FEED_INVALID_BEFORE_DATE = 304;
  const FEED_INVALID_AFTER_DATE = 305;
  const FEED_INVALID_DATES = 306;

  const SAMPLE_SAVE_FAILED = 400;
  const SAMPLE_FETCH_FAILED = 401;

  private static $messages = array(
    self::POST_METHOD_REQUIRED => 'POST method required',
    self::API_ACTION_REQUIRED => 'no action provided',
    self::API_ACTION_INVALID => 'invalid action provided',
    self::USERNAME_HEADER_REQUIRED => 'X-Mother-Username header required',
    self::PASSWORD_HEADER_REQUIRED => 'X-Mother-Password header required',
    self::CONTENT_TYPE_JSON_REQUIRED => 'Content-Type must be application/json',
    self::REQUEST_FORMAT_INVALID => 'request format is invalid',

    self::UNKNOWN_USER_ACCOUNT => 'unknown user account',
    self::INCORRECT_USER_PASSWORD => 'invalid password',

    self::FEED_INVALID_TYPE => 'invalid type provided',
    self::FEED_SIDE_REQUIRED => 'side must be provided for type of Breastfeed',
    self::FEED_EXPRESSION_SIDE_REQUIRED => 'side must be provided for type of Expression',
    self::FEED_SUBTYPE_REQUIRED => 'subtype must be provided for type of Supplementary',
    self::FEED_INVALID_BEFORE_DATE => 'invalid before.date provided',
    self::FEED_INVALID_AFTER_DATE => 'invalid after.date provided',
    self::FEED_INVALID_DATES => 'after.date must occur after before.date',

    self::SAMPLE_SAVE_FAILED => 'failed to insert sample into database',
    self::SAMPLE_FETCH_FAILED => 'failed to get feeds from database',
  );

  function __construct($code)
  {
    $message = APIError::$messages[$code];
    parent::__construct($message, $code, null);
  }

  public function result()
  {
    $code = $this->code;
    $message = $this->message;
    return new APIErrorResult($code, $message);
  }
}

?>