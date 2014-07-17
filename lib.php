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
 * The family block helper functions and callbacks
 *
 * @package   local_family
 * @copyright 2014 Justin Hunt <poodllsupport@google.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();



	/**
     * Fetch family by member's user id
     * @param integer $userid
     * @return object family
     */
	function local_family_fetch_family_by_key($familykey) {
		global $DB;
		 $sql = "SELECT * from {local_family} " . 
		 "WHERE familykey = '" . $familykey . "'";
		 
		 $result = $DB->get_record('local_family', array('familykey'=>$familykey));
		return $result;
	}

	/**
     * Fetch family by member's user id
     * @param integer $userid
     * @return object family
     */
	function local_family_fetch_family_by_member($userid) {
		 global $DB;
		 $sql = "SELECT * from {local_family} f " . 
		 "INNER JOIN {local_family_members} fm " .
		 "ON f.id = fm.familyid " . 
		 "WHERE fm.userid = " . $userid;
		
		$result = $DB->get_records_sql($sql);
		if($result && count($result) > 0){
			//a member can only be in ONE family
			//this should never be an error
			return array_shift($result);
		}else{
			return false;
		}
		//core_collator::asort_objects_by_property($familys,'role',core_collator::SORT_STRING);
	}

	/**
     * Fetch children by familyid
     * @param integer $familyid
     * @return array array of users (children)
     */
	function local_family_fetch_children_by_family($familyid) {
		global $DB;
		$ret = $DB->get_records('local_family_members',array('familyid'=>$familyid,'role'=>'child'));
		return $ret;
	}
	
	/**
     * Fetch children by parent user id
     * @param integer $userid
     * @return array array of users (children)
     */
	function local_family_fetch_child_users_by_parent($userid) {
		global $DB;
		$familyid = $DB->get_field('local_family_members','familyid',array('userid'=>$userid));
		if(!$familyid){return false;}
		
		$sql = "SELECT *
			FROM {local_family_members} lfm 
			INNER JOIN {user} u ON u.id=lfm.userid 
			WHERE lfm.role='child' 
			AND lfm.familyid = " . $familyid;
		$childusers = $DB->get_records_sql($sql);
		return $childusers;
	}
	
	/**
     * Fetch parent by children's user id
     * @param integer $userid
     * @return array array of users (parents)
     */
	function local_family_fetch_parents_by_child($userid) {
		global $DB;
		
	}
	
	/**
     * Fetch parent by family id
     * @param integer $familyid
     * @return array array of users (parents)
     */
	function local_family_fetch_parents_by_family($familyid) {
		global $DB;
		return $DB->get_records('local_family_members',array('familyid'=>$familyid,'role'=>'parent'));
	}
	
	/**
     * Fetch parent by family id
     * @param integer $familyid
     * @return array array of users (family members)
     */
	function local_family_fetch_members_by_family($familyid) {
		global $DB;
		return $DB->get_records('local_family_members',array('familyid'=>$familyid));
	}
	
	  
	

	
	/**
	 * user_deleted event handler
	 *
	 * @param \core\event\course_content_deleted $event The event.
	 * @return void
	 */
	function local_family_handle_user_deletion(\core\event\user_deleted $event) {
		global $DB;
		$DB->delete_records('local_family_members', array('userid' => $event->userid));
	}