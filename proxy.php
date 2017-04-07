<?php
/**
 * proxy script for social media
 *
 * Requires
 * php-curl-class/php-curl-class - this module requires PHP CURL for remote API http requests
 * j7mbo/twitter-api-php - this modules allows an easier connection for the twitter API
 *
 * Sample Usage:
 * http://example.com/proxy.php?u=simple&token=2c0ae9c1f976695a1f9a3f0218d0eda7&s=facebook&type=json&{additional parameters}
 * required parameters:
 * @param (string)$s - social media request type. Possible values:
 * facebook
 * instagram
 * twitter
 * @param (string)$u - username authentication of the proxy
 * @param (string)$token - token combination of the user
 * @param (string)$output_type - EXPERIMENTAL. output type of content. Not all response can be converted. Use proper response type for each social media response. possible outputs:
 * JSON
 * (NOT yet supported)JSONP - if JSONP - another Parameter is required
 * (NOT yet supported)XML
 * @param (string)$jsonp - name of the javascript function callback for the output response
 */

require __DIR__ . '/vendor/autoload.php';
/**
 * use CURL class
 */
use \Curl\Curl;

namespace SocialProxy;

class SocialProxy{
  /**
   * modify username or token at construct to allow only specific users in accessing this proxy page.
   */
  protected $proxy_username = 'simple';
  protected $proxy_token = '2c0ae9c1f976695a1f9a3f0218d0eda7';
  protected $social_media_request = '';
  protected $type = '';
  protected $allowed_social = array(
    'facebook',
    'twitter',
    'instagram',
    'youtube',
  );

  private $_curl;

  function __construct($username, $token, $social_media_request, $request_type = 'JSON'){
    $this->proxy_username = $username;
    $this->proxy_token = $token;

    $this->_curl = new Curl();

    try {
      // check required parameters
      $params = array('u', 'token', 's', 'output_type');
      if(!parameter_check($params)){
        return false;
      }
      $username = $_GET['u'];
      $token = $_GET['token'];
      if(!authenticate($username, $token)){
        return error_response('Access Forbidden.', 403);
      }

      $social_media_request = $_GET['s'];
      $this->_controller($social_media_request);
    } catch (Exception $e) {
      error_response($e->getMessage());
      // supress ALL warnings and errors and do not print.
    }
  }

  /**
   * determine which social media to run
   * @param (string)$social_media_request - determine the which social media is used. accepted value:
   * facebook
   * twitter
   * instagram
   * youtube
   */
  private function _controller(){
    $social = $this->allowed_social;
    // allowed social media
    if(!function_exists('social_request_'.$social_media_request)){
      error_response('Invalid Request.');
    }

    if(in_array($social_media_request, $social)){
      $function = 'social_request_'.$social_media_request;
      $output_type = $_GET['output_type'];

      try {
        switch ($social_media_request) {
          case 'facebook':
          case 'twitter':
          case 'instagram':
          case 'youtube':
            $is_raw = true;
            break;

          default:
            $is_raw = false;
            break;
        }
        if($output = $function($is_raw)){
          success_response($output, $output_type, $is_raw);
          return true;
        }
        error_response('Unknown Response.');
        return false;
      }
      catch (Exception $e) {
        error_response($e->getMessage());
      }
    }
    error_response('Invalid Request.');
    return false;
  }

  /**
   * intantiate a curl object
   */
  public function getCurl(){
    return $this->_curl;
  }


}
// define static username
define('PROXY_USERNAME', 'simple');
// define static token
define('PROXY_TOKEN', '2c0ae9c1f976695a1f9a3f0218d0eda7');

function main(){
  try {
    // check required parameters
    $params = array('u', 'token', 's', 'output_type');
    if(!parameter_check($params)){
      return false;
    }
    $username = $_GET['u'];
    $token = $_GET['token'];
    if(!authenticate($username, $token)){
      return error_response('Access Forbidden.', 403);
    }

    $social_media_request = $_GET['s'];
    controller($social_media_request);
  } catch (Exception $e) {
    error_response($e->getMessage());
    // supress ALL warnings and errors and do not print.
  }
}

/**
 * determine which function to run
 * @param (string)$social_media_request - determine the which social media is used. accepted value:
 * facebook
 * twitter
 * instagram
 */
function controller($social_media_request){
  // allowed social media
  $social = array(
    'facebook',
    'twitter',
    'instagram',
    'youtube',
  );
  if(!function_exists('social_request_'.$social_media_request)){
    error_response('Invalid Request.');
  }

  if(in_array($social_media_request, $social)){
    $function = 'social_request_'.$social_media_request;
    $output_type = $_GET['output_type'];

    try {
      switch ($social_media_request) {
        case 'facebook':
        case 'twitter':
        case 'instagram':
        case 'youtube':
          $is_raw = true;
          break;

        default:
          $is_raw = false;
          break;
      }
      if($output = $function($is_raw)){
        success_response($output, $output_type, $is_raw);
        return true;
      }
      error_response('Unknown Response.');
      return false;
    }
    catch (Exception $e) {
      error_response($e->getMessage());
    }
  }
  error_response('Invalid Request.');
  return false;
}

/**
 * simple authentication if the request will continue or not
 */
function authenticate($username, $token){
  if($username == PROXY_USERNAME && $token == PROXY_TOKEN){
    return true;
  }
  return false;
}

/**
 * social_request_hook()
 *
 * if request type is facebook following parameters are required
 * @param (string)$fb_username
 * @param (string)$fb_app_id
 * @param (string)$fb_app_token
 * @param (string)$fb_syntax
 */
function social_request_facebook($is_raw = true){
  // $fb_username = _variable_get('fb_username', 'UWCChangshu');
  // $app_id = _variable_get('fb_app_id', '188832764858676');
  // $app_token = _variable_get('fb_app_token', '0WSjJnlV79oWRXoK8riI5fJLozM');
  // $app_syntax = _variable_get('fb_graph_syntax', '?fields=posts.limit(1){story,message,created_time},id,username,name,picture');
  // simple check if any of the parameter is missing
  $params = array('fb_username', 'fb_app_id', 'fb_app_token', 'fb_syntax');
  if(!parameter_check($params)){
    return false;
  }
  $fb_username = $_GET['fb_username'];
  $app_id = $_GET['fb_app_id'];
  $app_token = $_GET['fb_app_token'];
  $app_syntax = $_GET['fb_syntax'];

  // need to use inline because curl auto formats field posts
  $url = 'https://graph.facebook.com/v2.7/' . $fb_username . $app_syntax . '&access_token=' . $app_id . '|' . $app_token;
  // echo $url;
  try {
    // use curl class instead
    $curl = new Curl();
    $curl->get($url);

    if ($curl->error) {
      error_response('Failed to fetch data from the Facebook Graph');
      // set page as invalid request
      return false;
    }
    if($is_raw){
      return $curl->rawResponse;
    }
    return $curl->response;
  }
  catch (Exception $e) {
    error_response($e->getMessage());
  }

  return $posts;
}

/**
 * social_request_hook()
 *
 * if request type is twitter following parameters are required
 * @param (string)$twitter_token
 * @param (string)$twitter_token_secret
 * @param (string)$twitter_key
 * @param (string)$twitter_secret
 * @param (string)$twitter_username
 */
function social_request_twitter($is_raw = true){
  // requires twitter API
  // https://github.com/J7mbo/twitter-api-php
  $params = array('twitter_token', 'twitter_token_secret', 'twitter_key', 'twitter_secret', 'twitter_username');
  if(!parameter_check($params)){
    return false;
  }
  // require_once('vendor/twitter-api-php/TwitterAPIExchange.php');

  $twitter_token = $_GET['twitter_token'];
  $twitter_token_secret = $_GET['twitter_token_secret'];
  $twitter_key = $_GET['twitter_key'];
  $twitter_secret = $_GET['twitter_secret'];
  $twitter_username = $_GET['twitter_username'];

  $settings = array(
    'oauth_access_token' => $twitter_token,
    'oauth_access_token_secret' => $twitter_token_secret,
    'consumer_key' => $twitter_key,
    'consumer_secret' => $twitter_secret,
  );

  $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
  $getfield = '?screen_name=' . $twitter_username . '&count=1';
  $requestMethod = 'GET';

  $twitter = new TwitterAPIExchange($settings);
  try {
    $response = $twitter->setGetfield($getfield)
      ->buildOauth($url, $requestMethod)
      ->performRequest();

    if($is_raw){
      return $response;
    }
  }
  catch (Exception $e) {
    error_response($e->getMessage());
  }
  return;
}

/**
 * social_request_hook()
 *
 * if request type is instagram following parameters are required
 * @param (string)$instagram_username
 */
function social_request_instagram($is_raw = true){
  $params = array('instagram_username');
  if(!parameter_check($params)){
    return false;
  }
  $instagram_username = $_GET['instagram_username'];

  try {
    $url = 'https://www.instagram.com/' . $instagram_username . '/media/';
    // use curl class instead
    $curl = new Curl();
    $curl->get($url);

    if ($curl->error) {
      error_response('Failed to fetch data from the Instagram.');
      // set page as invalid request
      return false;
    }
    if($is_raw){
      return $curl->rawResponse;
    }
    return $curl->response;
  }
  catch (Exception $e) {
    error_response($e->getMessage());
  }
}

/**
 * social_request_hook()
 *
 * if request type is instagram following parameters are required
 * @param (string)$youtube_channel_id
 */
function social_request_youtube($is_raw = true){
  $params = array('youtube_channel_id');
  if(!parameter_check($params)){
    return false;
  }
  $youtube_channel_id = $_GET['youtube_channel_id'];

  try {
    $url = 'https://www.youtube.com/feeds/videos.xml?channel_id=' . $youtube_channel_id;
    // use curl class instead
    $curl = new Curl();
    $curl->get($url);

    if ($curl->error) {
      error_response('Failed to fetch data from the Youtube.');
      // set page as invalid request
      return false;
    }
    if($is_raw){
      return json_encode($curl->response);
    }
    return $curl->response;
  }
  catch (Exception $e) {
    error_response($e->getMessage());
  }
}

/**
 * parameter checker, checks if parameters is available and not empty
 */
function parameter_check($default_parameters = array()){
  if(empty($default_parameters)){
    return false;
  }

  foreach ($default_parameters as $key => $value) {
    // checks if any of the parameters does not exists
    if(!isset($_GET[$value])){
      return false;
    }
  }
  // if all parameters exists
  return true;
}

/**
 * set the header into 404 not found by default
 */
function error_response($error_message, $error_code = 400){
  $responses = array(
    100 => 'Continue',
    101 => 'Switching Protocols',
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-Authoritative Information',
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',
    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    305 => 'Use Proxy',
    307 => 'Temporary Redirect',
    400 => 'Bad Request',
    401 => 'Unauthorized',
    402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Time-out',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Request Entity Too Large',
    414 => 'Request-URI Too Large',
    415 => 'Unsupported Media Type',
    416 => 'Requested range not satisfiable',
    417 => 'Expectation Failed',
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Time-out',
    505 => 'HTTP Version not supported',
  );
  if (isset($responses[$error_code])) {
    header("HTTP/1.0 ".$error_code." ".$responses[$error_code]);
    echo $error_message;
    exit();
  }
}

/**
 * @param (mixed)$data - the response data returned by the CURL
 * @param (string)$type - the type of response output to be used
 * @param (bool)$is_raw - bool
 */
function success_response($data, $type, $is_raw = false){
  // check if type is a valid type
  $valid_types = array(
    'json' => 'application/json',
    'jsonp' => 'application/javascript',
    'xml' => 'text/xml',
  );

  // TODO: auto detect if the output is object attempt to auto convert
  if(isset($valid_types[$type])){
    header('Content-Type: '.$valid_types[$type]);
    if($is_raw){
      echo $data;
    }
    // TODO: use the curl data response if not raw then convert it to expected output
  }
  exit();
}

main();
