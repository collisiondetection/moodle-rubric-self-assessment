<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file keeps track of upgrades to plugin gradingform_rubric
 *
 * @package    gradingform_rubric
 * @copyright  2011 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Keeps track or rubric plugin upgrade path
 *
 * @param int $oldversion the DB version of currently installed plugin
 * @return bool true
 */
function xmldb_gradingform_rubric_upgrade($oldversion) {
    global $CFG;
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    // Moodle v2.8.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v2.9.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v3.0.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v3.1.0 release upgrade line.
    // Put any upgrade step following this.

    // add self assessment table
    if ($oldversion < 2017020500) {

      // Define table gradingform_rubric_self to be created.
      $table = new xmldb_table('gradingform_rubric_self');

      // Adding fields to table gradingform_rubric_self.
      $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
      $table->add_field('instanceid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
      $table->add_field('criterionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
      $table->add_field('levelid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
      $table->add_field('remark', XMLDB_TYPE_TEXT, null, null, null, null, null);
      $table->add_field('remarkformat', XMLDB_TYPE_INTEGER, '2', null, null, null, null);

      // Adding keys to table gradingform_rubric_self.
      $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
      $table->add_key('fk_instanceid', XMLDB_KEY_FOREIGN, array('instanceid'), 'grading_instances', array('id'));
      $table->add_key('fk_criterionid', XMLDB_KEY_FOREIGN, array('criterionid'), 'gradingform_rubric_criteria', array('id'));
      $table->add_key('uq_instance_criterion', XMLDB_KEY_UNIQUE, array('instanceid', 'criterionid'));

      // Adding indexes to table gradingform_rubric_self.
      $table->add_index('ix_levelid', XMLDB_INDEX_NOTUNIQUE, array('levelid'));

      // Conditionally launch create table for gradingform_rubric_self.
      if (!$dbman->table_exists($table)) {
          $dbman->create_table($table);
      }

      // Rubric savepoint reached.
      upgrade_plugin_savepoint(true, 2017020500, 'gradingform', 'rubric');
    }

    if ($oldversion < 2017020502) {
    	$table = new xmldb_table('gradingform_rubric_self');
    	$field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null);

    	// Conditionally launch add field id.
      if (!$dbman->field_exists($table, $field)) {
          $dbman->add_field($table, $field);
      }
    	upgrade_plugin_savepoint(true, 2017020502, 'gradingform', 'rubric');
    }



    return true;
}
