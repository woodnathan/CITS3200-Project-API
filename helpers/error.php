<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/milk/api/helpers/result.php');

/**
 * @brief Represents the JSON result of an error with a code and message
 */
class APIErrorResult extends APIJSONResult
{
  /**
   * @param  code    an integer value representing the unique error code
   * @param  message a simple message describing the error
   */
  function __construct($code, $message, $stack_trace = null)
  {
    $value = array(
      'error' => array(
        'code' => intval($code),
        'message' => strval($message)
      )
    );

    if (isset($stack_trace) && is_array($stack_trace) && isset($_SERVER['HTTP_X_MOTHER_DEBUG']))
    {
      $stack_lines = array();
      foreach ($stack_trace as $trace)
      {
        $file = strval($trace['file']);
        $line_number = intval($trace['line']);
        $function = strval($trace['function']);
        // $args = implode(', ', $trace['args']);

        $stack_line = $file . '(' . $line_number . '): ' . $function . '()';
        array_push($stack_lines, $stack_line);
      }

      $value['stack_trace'] = $stack_lines;
    }

    parent::__construct($value);
  }
}

/**
 * @brief An Exception subclass to centralise all error codes and messages
 *        as well as provide convenience for obtaining an APIErrorResult
 *        object
 */
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
  const SAMPLE_MOTHER_LAST_UPDATE_FAILED = 402;
  const SAMPLE_SAVE_CALC_FAILED = 403;

  const SAMPLE_INVALID_BEFORE_SID = 500;
  const SAMPLE_INVALID_AFTER_SID = 501;
  const SAMPLE_ONE_SID_REQUIRED = 502;
  const SAMPLE_SIDS_INVALID = 503;
  const SAMPLE_SID_PERMISSIONS = 504;

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
    self::SAMPLE_MOTHER_LAST_UPDATE_FAILED => 'failed to update the last updated field to now in mother database',
    self::SAMPLE_SAVE_CALC_FAILED => 'failed to insert sample calcuation',

    self::SAMPLE_INVALID_BEFORE_SID => 'the before sample SID is invalid',
    self::SAMPLE_INVALID_AFTER_SID => 'the after sample SID is invalid',
    self::SAMPLE_ONE_SID_REQUIRED => 'at least one sample SID must be provided',
    self::SAMPLE_SIDS_INVALID => 'provided sample SIDs did not match expected SIDs',
    self::SAMPLE_SID_PERMISSIONS => 'provided sample SIDs do not belong to user'
  );

  function __construct($code)
  {
    $message = APIError::$messages[$code];
    parent::__construct($message, $code, null);
  }

  /**
   * @brief Creates an APIErrorResult object representing the API Exception
   * @return an APIErrorResult object
   */
  public function result()
  {
    $code = $this->code;
    $message = $this->message;
    $stack_trace = @$this->getTrace();
    return new APIErrorResult($code, $message, $stack_trace);
  }
}

?>