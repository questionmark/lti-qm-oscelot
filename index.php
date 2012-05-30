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
*/

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require_once('lib.php');

// initialise database
  $db = init_db();

// ensure consumer record exists
  $consumer = new LTI_Tool_Consumer(CONSUMER_KEY, $db);
  if ($consumer->secret != CONSUMER_SECRET) {
    $consumer->secret = CONSUMER_SECRET;
    $consumer->enabled = TRUE;
    $consumer->save();
  }

// process launch request
  $tool = new LTI_Tool_Provider(array('connect' => 'doLaunch'), $db);
  $tool->execute();

// process validated connection
  function doLaunch($tool_provider) {

    global $db;

    $consumer_key = $tool_provider->consumer->getKey();
    $context_id = $tool_provider->context->getId();
    $username = $tool_provider->user->getId(LTI_Tool_Provider::ID_SCOPE_GLOBAL);
// remove invalid characters in username
    $username = str_replace(LTI_Tool_Provider::ID_SCOPE_SEPARATOR, '-', $username);
    $firstname = $tool_provider->user->firstname;
    $lastname = $tool_provider->user->lastname;
    $email = $tool_provider->user->email;
    $isStudent = $tool_provider->user->isLearner();
    $result_id = $tool_provider->user->lti_result_sourcedid;

    $assessment_id = $tool_provider->context->getSetting(ASSESSMENT_SETTING);

    $ok = ($context_id && $username && ($tool_provider->user->isLearner() || $tool_provider->user->isStaff()) &&
           $tool_provider->context->hasOutcomesService());

    if ($ok) {
// initialise session
      session_name(SESSION_NAME);
      session_start();
      session_unset();
      $_SESSION['username'] = $username;
      $_SESSION['firstname'] = $firstname;
      $_SESSION['lastname'] = $lastname;
      $_SESSION['email'] = $email;
      $_SESSION['isStudent'] = $isStudent;
      $_SESSION['consumer_key'] = $consumer_key;
      $_SESSION['context_id'] = $context_id;
      $_SESSION['assessment_id'] = $assessment_id;
      $_SESSION['lti_return_url'] = $tool_provider->return_url;
      $_SESSION['result_id'] = $result_id;
// set redirect URL
      if ($isStudent) {
        $page = 'student';
      } else {
        $page = 'staff';
      }
      $ok = get_root_url() . "{$page}.php";
    } else {
      $tool_provider->reason = 'Missing data';
    }

    return $ok;

  }

?>
