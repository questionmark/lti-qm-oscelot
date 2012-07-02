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
 *    1.0.01   2-May-12  Corrected GET to POST requests
*/

require_once('lib.php');

// initialise database
  $db = init_db();

// TODO create an LTI PIP file rather than use the Moodle version
  $consumer_key = $_POST['moodle_activityid'];
  $context_id = $_POST['moodle_courseid'];
  $result_id = $_POST['moodle_userid'];
  $score = $_POST['Percentage_Score'];

// Initialise a tool consumer and context object
  $consumer = new LTI_Tool_Consumer($consumer_key, array(TABLE_PREFIX, $db, DATA_CONNECTOR));
  $context = new LTI_Context($consumer, $context_id);

// Save result
  $outcome = new LTI_Outcome($result_id);
  $outcome->setValue($score);
  $outcome->type = 'percentage';
  $ok = $context->doOutcomesService(LTI_Context::EXT_WRITE, $outcome);

?>
