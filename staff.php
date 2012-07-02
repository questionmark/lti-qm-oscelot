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

// Initialise database
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

    $consumer = new LTI_Tool_Consumer($consumer_key, array(TABLE_PREFIX, $db, DATA_CONNECTOR));
    $context = new LTI_Context($consumer, $context_id);
    $context->setSetting(ASSESSMENT_SETTING, $_SESSION['assessment_id']);
    $context->save();
  }
  $assessment_id = $_SESSION['assessment_id'];

  $ok = !$isStudent;
  if (!$ok) {
    $_SESSION['error'] = 'Invalid role';
  }

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
    exit;
  }

  page_header($username);

?>

<script type="text/javascript">
function doChange(id) {
  doReset();
  var el = document.getElementById(id);
  if (el) {
    el.className = 'show';
  }
  el = document.getElementById('id_save');
  el.disabled = false;
}

function doReset() {
  var el = document.getElementById('id_save');
  el.disabled = true;
  for (var i=1; i<=document.forms[0].assessment.length; i++) {
    el = document.getElementById('img' + i);
    if (el) {
      el.className = 'hide';
    }
  }
}
</script>

        <p><a href="<?php echo $em_url; ?>" target="_blank" />Log into Enterprise Manager</a></p>
        <h1>Assessments</h1>
<?php
  if ((count($assessments) > 0) && !is_null($assessments[0])) {
?>
        <form action="staff.php" method="POST">
        <table class="DataTable" cellpadding="0" cellspacing="0">
        <tr class="GridHeader">
          <td>&nbsp;</td>
          <td class="AssessmentName">Assessment Name</td>
          <td class="AssessmentAuthor">Assessment Author</td>
          <td class="LastModified">Last Modified</td>
        </tr>
<?php
    $i = 0;
    foreach ($assessments as $assessment) {
      $i++;
      if ($assessment->Assessment_ID == $assessment_id) {
        $selected = ' checked="checked" onclick="doReset();"';
      } else {
        $selected = ' onclick="doChange(\'img' . $i . '\');"';
      }
?>
        <tr class="GridRow">
          <td><img src="exclamation.png" alt="Unsaved changed" title="Unsaved changed" class="hide" id="img<?php echo $i; ?>" />&nbsp;<input type="radio" name="assessment" value="<?php echo $assessment->Assessment_ID; ?>"<?php echo $selected; ?> /></td>
          <td><?php echo $assessment->Session_Name; ?></td>
          <td><?php echo $assessment->Author; ?></td>
          <td><?php echo $assessment->Modified_Date; ?></td>
        </tr>
<?php
    }
?>
        </table>
        <p>
        <input type="submit" id="id_save" value="Save change" disabled="disabled" />
        </p>
        </form>
<?php
  } else {
    echo "<p>\nNo assessments available.\n</p>\n";
  }

  page_footer();

?>
