<?php
/*
 *  LTI-Connector - Connect to Perception via IMS LTI
 *  Copyright (C) 2012  Questionmark
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 *  Contact: info@questionmark.com
 *
 *  Version history:
 *    1.0.00   1-May-12  Initial prototype
 *    1.1.00   3-May-12  Added test harness
*/

require_once('config.php');
require_once('lti/LTI_Tool_Provider.php');

  $cfg_timezone = 'Europe/London';
  date_default_timezone_set($cfg_timezone);

  define('SESSION_NAME', 'QMP-LTI');
  define('INVALID_USERNAME_CHARS', '\'"&\\/£,:><');
  define('MAX_NAME_LENGTH', 50);
  define('MAX_EMAIL_LENGTH', 255);
  define('ASSESSMENT_SETTING', 'qmp_assessment_id');
  $LTI_ROLES = array('a' => 'Administrator',
                     'd' => 'ContentDeveloper',
                     'i' => 'Instructor',
                     't' => 'TeachingAssistant',
                     'l' => 'Learner',
                     'm' => 'Mentor');
  define('DATA_CONNECTOR', 'QMP');
  define('TABLE_PREFIX', '');


  function init_db() {

    $db = FALSE;

    try {

      $db = new PDO(DB_NAME, DB_USERNAME, DB_PASSWORD);

      $sql = 'CREATE TABLE IF NOT EXISTS ' . TABLE_PREFIX . LTI_Data_Connector::CONTEXT_TABLE_NAME . ' ' .
             '(consumer_key VARCHAR(255), context_id VARCHAR(255), settings TEXT, created DATETIME, updated DATETIME, ' .
             'PRIMARY KEY (consumer_key, context_id))';
      $res = $db->exec($sql);

      $sql = 'CREATE TABLE IF NOT EXISTS ' . TABLE_PREFIX . 'LTI_Outcome ' .
             '(result_sourcedid VARCHAR(255), score VARCHAR(255), created DATETIME, PRIMARY KEY (result_sourcedid))';
      $res = $db->exec($sql);

    } catch(PDOException $e) {
      log_error($e);
      $_SESSION['error'] = 'Unable to connect to database';
      $db = FALSE;
    }

    return $db;

  }

/*
// For reference

  function reset_db() {

    $db = new PDO(DB_NAME, DB_USERNAME, DB_PASSWORD);

    $res = $db->exec('DROP TABLE ' . TABLE_PREFIX . LTI_Data_Connector::CONTEXT_TABLE_NAME);
    $res = $db->exec('DROP TABLE ' . TABLE_PREFIX . 'LTI_Outcome');

  }
*/

/*
 * perception_soapconnect
 * Connect to the Perception server
 */
  function perception_soapconnect() {

    require_once 'PerceptionSoap.php';

    $ok = TRUE;

    if (!isset($GLOBALS['perceptionsoap'])) {
      try {
        $GLOBALS['perceptionsoap'] = new PerceptionSoap(QMWISE_URL, array(
          'security_client_id'           => SECURITY_CLIENT_ID,
          'security_checksum'            => SECURITY_CHECKSUM,
          'debug'                        => DEBUG_MODE
        ));
      } catch(Exception $e) {
        log_error($e);
        $ok = FALSE;
      }
    }

    return $ok;

  }

  function get_administrator_by_name($username) {

    $admin_details = FALSE;
    try {
      $admin_details = $GLOBALS['perceptionsoap']->get_administrator_by_name($username);
    } catch (Exception $e) {
    }

    return $admin_details;

  }

  function create_administrator_with_password($username, $firstname, $lastname, $email, $profile) {

    $admin_id = FALSE;
    try {
      $admin_details = $GLOBALS['perceptionsoap']->create_administrator_with_password($username, $firstname, $lastname, $email, $profile);
      $admin_id = $admin_details->Administrator_ID;
    } catch (Exception $e) {
      log_error($e);
    }

    return $admin_id;

  }

  function get_access_administrator($username) {

    $url = FALSE;
    try {
      $access = $GLOBALS['perceptionsoap']->get_access_administrator($username);
      $url = $access->URL;
    } catch (Exception $e) {
      log_error($e);
    }

    return $url;

  }

  function get_assessment_tree_by_administrator($id) {

    try {
      $assessments = $GLOBALS['perceptionsoap']->get_assessment_tree_by_administrator($id, 0, 1);
    } catch (Exception $e) {
      log_error($e);
      $assessments = FALSE;
    }

    return $assessments;

  }

  function get_access_assessment_notify($assessment_id, $participant_name, $user_id, $activity_id, $course_id, $notify_url, $home_url) {

    try {
      $access = $GLOBALS['perceptionsoap']->get_access_assessment_notify($assessment_id, $participant_name, $user_id, $activity_id, $course_id, $notify_url, $home_url);
      $url = $access->URL;
    } catch (Exception $e) {
      log_error($e);
      $url = FALSE;
    }

    return $url;

  }

  function get_participant_by_name($username) {

    $participant_details = FALSE;
    try {
      $participant_details = $GLOBALS['perceptionsoap']->get_participant_by_name($username);
    } catch (Exception $e) {
    }

    return $participant_details;

  }

  function create_participant($username, $firstname, $lastname, $email) {

    $participant_id = FALSE;
    try {
      $participant_details = $GLOBALS['perceptionsoap']->create_participant($username, $firstname, $lastname, $email);
      $participant_id = $participant_details->Participant_ID;
    } catch (Exception $e) {
      log_error($e);
    }

    return $participant_id;

  }

  function log_error($e) {

    $error = "Error {$e->getCode()}: {$e->getMessage()}";
    error_log($error);
    $_SESSION['error'] = $error;

  }

  function page_header($username = '') {

    header('Cache-control: no-cache');
    header('Pragma: no-cache');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');

    $html = <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>QMP: LTI</title>
<link href="qmp-lti.css" type="text/css" rel="stylesheet" />
</head>
<body>
<div id="Wrapper">
  <div id="HeaderWrapper">
    <img id="logoImage" src="logo.gif" alt="Questionmark" style="width: 175px; height: 32px; margin-left: 10px" />
  </div>
  <div id="MainContentWrapper">
    <div id="ContentWrapper">
      <div id="PageContent">
EOD;

    echo $html;
    if (isset($_SESSION['lti_return_url']) && (strlen($_SESSION['lti_return_url']) > 0)) {
      echo '<p><button type="button" onclick="location.href=\'' . $_SESSION['lti_return_url'] . '\';">Return to course environment</button></p>' . "\n";
    }

  }

  function page_footer() {

    $html = <<<EOD
      </div>
    </div>
  </div>
  <div id="FooterWrapper">
    <span id="Copyright">
      <a id="lnkCopyright" href="http://www.questionmark.com" target="_blank">Copyright &copy;2012 Questionmark Computing Ltd.</a>
    </span>
  </div>
</div>
</body>
</html>
EOD;

    echo $html;

  }

  function get_root_url() {

    if (!defined('WEB_PATH') || (strlen(WEB_PATH) <= 0)) {
      $path = str_replace('\\', '/', dirname(__FILE__));
      $path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
    } else {
      $path = WEB_PATH;
    }
    $scheme = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")
              ? 'http'
              : 'https';
    $url = $scheme . '://' . $_SERVER['HTTP_HOST'] . $path . '/';

    return $url;

  }

  function set_session($name, $value = '') {

    if (isset($_POST[$name])) {
      $value = $_POST[$name];
    }

    $_SESSION[$name] = $value;

  }

  function init_session($name, $value) {

    if (!isset($_SESSION[$name])) {
      $_SESSION[$name] = $value;
    }

  }

  function init_data() {

    init_session('url', get_root_url() . 'index.php');
    init_session('key', CONSUMER_KEY);
    init_session('secret', CONSUMER_SECRET);
    init_session('cid', '');
    init_session('rid', 'linkABC');
    init_session('uid', 'jt001');
    init_session('name', 'Jane Teacher');
    init_session('fname', 'Jane');
    init_session('lname', 'Teacher');
    init_session('email', 'jt@inst.edu');
    init_session('result', 'WLdfkdkjl213ljsOOS');
    init_session('roles', array('i'));
    init_session('outcome', '1');
    init_session('outcomes', '1');

  }

  function signRequest($url, $params) {

// Check for query parameters which need to be included in the signature
    $query_params = array();
    $query_string = parse_url($url, PHP_URL_QUERY);
    if (!is_null($query_string)) {
      $query_items = explode('&', $query_string);
      foreach ($query_items as $item) {
        if (strpos($item, '=') !== FALSE) {
          list($name, $value) = explode('=', $item);
          $query_params[$name] = $value;
        } else {
          $query_params[$name] = '';
        }
      }
    }
    $params = $params + $query_params;
    $params['oauth_callback'] = 'about:blank';
    $params['oauth_consumer_key'] = $_SESSION['key'];
// Add OAuth signature
    $hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
    $consumer = new OAuthConsumer($_SESSION['key'], $_SESSION['secret'], NULL);
    $req = OAuthRequest::from_consumer_and_token($consumer, NULL, 'POST', $url, $params);
    $req->sign_request($hmac_method, $consumer, NULL);
    $params = $req->get_parameters();
// Remove parameters being passed on the query string
    foreach (array_keys($query_params) as $name) {
      unset($params[$name]);
    }

    return $params;

  }

?>
