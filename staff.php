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

require_once('lib.php');

// initialise database
  $db = init_db();

  session_name(SESSION_NAME);
  session_start();

  $consumer_key = $_SESSION['consumer_key'];
  $context_id = $_SESSION['context_id'];
  $username = $_SESSION['username'];
  $firstname = $_SESSION['firstname'];
  $lastname = $_SESSION['lastname'];
  $email = $_SESSION['email'];
  $isStudent = $_SESSION['isStudent'];

  if (isset($_POST['assessment'])) {
    $_SESSION['assessment_id'] = htmlentities($_POST['assessment']);

    $consumer = new LTI_Tool_Consumer($consumer_key, $db);
    $context = new LTI_Context($consumer, $context_id);
    $context->setSetting(ASSESSMENT_SETTING, $_SESSION['assessment_id']);
    $context->save();
  }
  $assessment_id = $_SESSION['assessment_id'];

  $ok = !$isStudent;


// Activate SOAP Connection.
  if ($ok) {
    $ok = perception_soapconnect();
  }

// Create administrator
  if ($ok && (($admin_details = get_administrator_by_name($username)) !== FALSE)) {
    $admin_id = $admin_details->Administrator_ID;
  } else if ($ok && (($admin_id = create_administrator_with_password($username, $firstname, $lastname, $email, ADMINISTRATOR_ROLE)) === FALSE)) {
    $ok = FALSE;
  }

// Get login URL
  if ($ok) {
    $em_url = get_access_administrator($username);
    $ok = !empty($em_url);
  }

// Get assessments
  if ($ok && (($assessments = get_assessment_tree_by_administrator($admin_id)) === FALSE)) {
    $assessments = array();
  }

  if (!$ok) {
    header('Location: error.php');
  }

  page_header($username);

?>
        <p><a href="<?php echo $em_url; ?>" target="_blank" />Log into Enterprise Manager</a></p>
        <h1>Assessments</h1>
<?php
  if ((count($assessments) > 0) && !is_null($assessments[0])) {
?>
        <form action="staff.php" method="POST">
        <table class="DataTable" cellpadding="0" cellspacing="0" width="95%">
        <tr class="GridHeader">
          <td class="AssessmentName">Assessment Name</td>
          <td class="AssessmentAuthor">Assessment Author</td>
          <td class="LastModified">Last Modified</td>
        </tr>
<?php
    foreach ($assessments as $assessment) {
      if ($assessment->Assessment_ID == $assessment_id) {
        $selected = ' checked="checked"';
      } else {
        $selected = '';
      }
?>
        <tr border="1" class="GridRow">
          <td>&nbsp;<input type="radio" name="assessment" value="<?php echo $assessment->Assessment_ID; ?>"<?php echo $selected; ?> />&nbsp;<?php echo $assessment->Session_Name; ?></td>
          <td>&nbsp;<?php echo $assessment->Author; ?></td><td>&nbsp;<?php echo $assessment->Modified_Date; ?></td>
        </tr>
<?php
    }
?>
        </table>
        <br />
        <input type="submit" value="Select" />
        </tr>
        </form>
<?php
  } else {
    echo "<p>\nNo assessments available.\n</p>\n";
  }

  page_footer();

?>
