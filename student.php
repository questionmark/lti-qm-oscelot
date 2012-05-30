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

require_once('lib.php');

  session_name(SESSION_NAME);
  session_start();

// Get data from session
  $consumer_key = $_SESSION['consumer_key'];
  $context_id = $_SESSION['context_id'];
  $assessment_id = $_SESSION['assessment_id'];
  $username = $_SESSION['username'];
  $firstname = $_SESSION['firstname'];
  $lastname = $_SESSION['lastname'];
  $email = $_SESSION['email'];
  $return_url = $_SESSION['lti_return_url'];
// TODO set a more appropriate redirect URL when an assessment is completed
  if (!$return_url) {
    $return_url = get_root_url() . 'error.php';
  }
  $isStudent = $_SESSION['isStudent'];
  $notify_url = get_root_url() . 'notify.php';
  $result_id = $_SESSION['result_id'];

  $err = FALSE;
// Ensure this is a student, an assessment has been defined and the LMS will accept an outcome
  if (!isStudent) {
    $err = 'Not a student';
  } else if (!$assessment_id) {
    $err = 'No assignment selected';
  } else if (!$result_id) {
    $err = 'No grade book column';
  }

// Activate SOAP Connection.
  if (!$err && !perception_soapconnect()) {
    $err = 'Unable to initialise SOAP connection';
  }

// Create participant
  if (!$err && (($participant_details = get_participant_by_name($username)) !== FALSE)) {
    $participant_id = $participant_details->Participant_ID;
  } else if (!$err && (($participant_id = create_participant($username, $firstname, $lastname, $email)) === FALSE)) {
    $err = 'Cannot create participant';
  }

// Get assessment URL
  if (!$err && (($url = get_access_assessment_notify($assessment_id, "${firstname} {$lastname}", $result_id, $consumer_key, $context_id,
     $notify_url, $return_url)) === FALSE)) {
    $err = 'Cannot get assessment URL';
  }

  if ($err) {
    $url = "error.php?msg={$err}";
  }

  header("Location: {$url}");

?>
