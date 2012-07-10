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

###
###  Class to represent a QMP LTI Data Connector
###

class LTI_Data_Connector_QMP extends LTI_Data_Connector {

  private $dbTableNamePrefix = '';
  private $db = NULL;

###
#    Class constructor
###
  function __construct($db, $dbTableNamePrefix = '') {

    $this->db = $db;
    $this->dbTableNamePrefix = $dbTableNamePrefix;

  }


###
###  LTI_Tool_Consumer methods
###

###
#    Load the tool consumer from the database
###
  public function Tool_Consumer_load($consumer) {

    $consumer->secret = CONSUMER_SECRET;
    $consumer->enabled = TRUE;
    $now = time();
    $consumer->created = $now;
    $consumer->updated = $now;

    return TRUE;

  }

###
#    Save the tool consumer to the database
###
  public function Tool_Consumer_save($consumer) {

    $consumer->updated = time();

    return TRUE;

  }

###
#    Delete the tool consumer from the database
###
  public function Tool_Consumer_delete($consumer) {

    return TRUE;

  }

###
#    Load all tool consumers from the database
###
  public function Tool_Consumer_list() {

    $consumers = array();

    return $consumers;

  }

###
###  LTI_Context methods
###

###
#    Load the context from the database
###
  public function Context_load($context) {

    $key = $context->getKey();
    $id = $context->getId();
    $sql = 'SELECT consumer_key, context_id, settings, created, updated ' .
           'FROM ' .$this->dbTableNamePrefix . LTI_Data_Connector::CONTEXT_TABLE_NAME . ' ' .
           'WHERE (consumer_key = :key) AND (context_id = :id)';
    $query = $this->db->prepare($sql);
    $query->bindValue('key', $key, PDO::PARAM_STR);
    $query->bindValue('id', $id, PDO::PARAM_STR);
    $query->execute();

    $row = $query->fetch();

    $ok = ($row !== FALSE);

    if ($ok) {
      $settingsValue = $row['settings'];
      if (!empty($settingsValue)) {
        $context->settings = unserialize($settingsValue);
        if (!is_array($context->settings)) {
          $context->settings = array();
        }
      } else {
        $context->settings = array();
      }
      $context->created = strtotime($row['created']);
      $context->updated = strtotime($row['updated']);
    }

    return $ok;

  }

###
#    Save the context to the database
###
  public function Context_save($context) {

    $time = time();
    $now = date("Y-m-d H:i:s", $time);
    $settingsValue = serialize($context->settings);
    if (is_null($context->created)) {
      $sql = 'INSERT INTO ' . $this->dbTableNamePrefix . LTI_Data_Connector::CONTEXT_TABLE_NAME . ' ' .
             '(consumer_key, context_id, settings, created, updated) VALUES (:key, :id, :settings, :created, :updated)';
      $query = $this->db->prepare($sql);
      $query->bindValue('key', $context->getKey(), PDO::PARAM_STR);
      $query->bindValue('id', $context->getId(), PDO::PARAM_STR);
      $query->bindValue('settings', $settingsValue, PDO::PARAM_INT);
      $query->bindValue('created', $now, PDO::PARAM_STR);
      $query->bindValue('updated', $now, PDO::PARAM_STR);
    } else {
      $sql = 'UPDATE ' . $this->dbTableNamePrefix . LTI_Data_Connector::CONTEXT_TABLE_NAME . ' ' .
             'SET settings = :settings, updated = :updated ' .
             'WHERE (consumer_key = :key) AND (context_id = :id)';
      $query = $this->db->prepare($sql);
      $query->bindValue('key', $context->getKey(), PDO::PARAM_STR);
      $query->bindValue('id', $context->getId(), PDO::PARAM_STR);
      $query->bindValue('settings', $settingsValue, PDO::PARAM_STR);
      $query->bindValue('updated', $now, PDO::PARAM_STR);
    }
    $ok = $query->execute();

    return $ok;

  }

###
#    Delete the context from the database
###
  public function Context_delete($context) {

    $key = $context->getKey();
    $id = $context->getId();

// Delete context
    $sql = 'DELETE FROM ' . $this->dbTableNamePrefix . LTI_Data_Connector::CONTEXT_TABLE_NAME . ' ' .
           'WHERE consumer_key = :key AND context_id = :id';
    $query = $this->db->prepare($sql);
    $query->bindValue('key', $key, PDO::PARAM_STR);
    $query->bindValue('id', $id, PDO::PARAM_STR);
    $ok = $query->execute();

    if ($ok) {
      $context->initialise();
    }

    return $ok;

  }

###
#    Obtain an array of LTI_User objects for users with a result sourcedId.  The array may include users from other
#    contexts which are sharing this context.  It may also be optionally indexed by the user ID of a specified scope.
###
  public function Context_getUserResultSourcedIDs($context, $context_only, $id_scope) {

    $users = array();

    return $users;

  }

###
#    Get an array of LTI_Context_Share objects for each context which is sharing this context
###
  public function Context_getShares($context) {

    $shares = array();

    return $shares;

  }


###
###  LTI_Consumer_Nonce methods
###

###
#    Load the consumer nonce from the database
###
  public function Consumer_Nonce_load($nonce) {

    return FALSE;

  }

###
#    Save the consumer nonce in the database
###
  public function Consumer_Nonce_save($nonce) {

    return TRUE;

  }


###
###  LTI_Context_Share_Key methods
###

###
#    Load the context share key from the database
###
  public function Context_Share_Key_load($share_key) {

    return TRUE;

  }

###
#    Save the context share key to the database
###
  public function Context_Share_Key_save($share_key) {

    return TRUE;

  }

###
#    Delete the context share key from the database
###
  public function Context_Share_Key_delete($share_key) {

    return TRUE;

  }


###
###  LTI_User methods
###


###
#    Load the user from the database
###
  public function User_load($user) {

    return TRUE;

  }

###
#    Save the user to the database
###
  public function User_save($user) {

    return TRUE;

  }

###
#    Delete the user from the database
###
  public function User_delete($user) {

    return TRUE;

  }

}

?>
