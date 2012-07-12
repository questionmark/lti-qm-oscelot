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
 *    1.2.00  10-Jul-12
*/

// TODO is there a better way to configure these parameters
define('CONSUMER_KEY', '');
define('CONSUMER_SECRET', '');
define('DB_NAME', 'sqlite:qmp-lti.sqlitedb');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');
define('QMWISE_URL', 'https://ondemand.questionmark.com/qmwise/123456/qmwise.asmx');
define('SECURITY_CLIENT_ID', '123456');
define('SECURITY_CHECKSUM', '434de524caad1f0bd4983c4cbf0cd0e9');
define('DEBUG_MODE', true);
define('ADMINISTRATOR_ROLE', 'LTI_INSTRUCTOR');
define('QM_USERNAME_PREFIX', '');
define('WEB_PATH', '');  // enter the path starting with a "/" but without a trailing "/"; only required if the automated version does not work

?>