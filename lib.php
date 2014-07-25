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
     * Fetch family by member's user id *used internally*
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
     * Fetch family by member's user id *used internally*
     * @param integer $userid
     * @return object family
     */
	function local_family_fetch_family_by_member($userid) {
		 global $DB;
		 $sql = "SELECT f.* from {local_family} f " . 
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
	}

	
	/**
     * Fetch family by member's user id *used internally*
     * @param integer $userid
     * @return object family
     */
	function local_family_fetch_family_by_username($username) {
		 global $DB;
		 $sql = "SELECT f.* from {local_family} f " . 
		 "INNER JOIN {local_family_members} fm " .
		 "ON f.id = fm.familyid " . 
		 "INNER JOIN {user} u " .
		  "ON fm.userid = u.id " . 
		 "WHERE u.username = '" . $username ."'";
		
		$result = $DB->get_records_sql($sql);
		if($result && count($result) > 0){
			//a member can only be in ONE family
			//this should never be an error
			return array_shift($result);
		}else{
			return false;
		}
	}
	
	/**
     * Fetch children by familyid *used internally*
     * @param integer $familyid
     * @return array array of users (children)
     */
	function local_family_fetch_children_by_family($familyid) {
		global $DB;
		$ret = $DB->get_records('local_family_members',array('familyid'=>$familyid,'role'=>'child'));
		return $ret;
	}
	
	/**
     * Fetch parent by family id *used internally*
     * @param integer $familyid
     * @return array array of users (parents)
     */
	function local_family_fetch_parents_by_family($familyid) {
		global $DB;
		return $DB->get_records('local_family_members',array('familyid'=>$familyid,'role'=>'parent'));
	}
	
	/**
     * Fetch parent by family id *used internally*
     * @param integer $familyid
     * @return array array of users (family members)
     */
	function local_family_fetch_members_by_family($familyid) {
		global $DB;
		return $DB->get_records('local_family_members',array('familyid'=>$familyid));
	}
	
	
	/**
     *  Fetch child as USER by family members user id  *not used internally*
     * @param integer $userid
     * @return array array of user objects (children)
     */
	function local_family_fetch_child_users($userid) {
		global $DB;
		$familyid = $DB->get_field('local_family_members','familyid',array('userid'=>$userid));
		if(!$familyid){return false;}
		
			
		$sql = "SELECT *
			FROM {user} u 
			WHERE u.id in (SELECT lfm.userid FROM {local_family_members} lfm 
			WHERE lfm.role='child' AND lfm.familyid = " . $familyid . ')';
	
			
		$childusers = $DB->get_records_sql($sql);
		return $childusers;
	}
	
	/**
     *  Fetch USER/family members by family id  *used internally*
     * @param integer $userid
     * @return array array of user objects (children)
     */
	function local_family_fetch_users_by_family($familyid) {
		global $DB;

		if(!$familyid){return false;}			
		$sql = "SELECT *
			FROM {user} u 
			INNER JOIN {local_family_members} lfm 
			ON lfm.userid=u.id
			WHERE lfm.familyid = " . $familyid . " " .
			"ORDER BY lfm.role DESC";

		$users = $DB->get_records_sql($sql);
		return $users;
	}
	
	/**
     * Fetch parent as USER by family members user id *not used internally*
     * @param integer $userid
     * @return array array of users (parents)
     */
	function local_family_fetch_parent_users($userid) {
		global $DB;
		$familyid = $DB->get_field('local_family_members','familyid',array('userid'=>$userid));
		if(!$familyid){return false;}
		
			
		$sql = "SELECT *
			FROM {user} u 
			WHERE u.id in (SELECT lfm.userid FROM {local_family_members} lfm 
			WHERE lfm.role='parent' AND lfm.familyid = " . $familyid . ')';
	
			
		$parentusers = $DB->get_records_sql($sql);
		return $parentusers;
	}
	
	/**
     * Fetch url to use to log in as student. *not used internally*
     * loginas.php should make it impossble to login if not a parent of the child
     * @param integer $userid
     * @return moodleurl url to use for parent to loginas chile
     */
	function local_family_fetch_loginas_url($userid, $courseid) {
		global $CFG,$USER;
		$urlbase = '/local/family/loginas.php';
		return new moodle_url($urlbase, array('userid'=>$userid,'courseid'=>$courseid, 'action' => 'loginas'));
	}
	
	/**
     * Fetch url to use to get an outline report *not used internally*
     * default outline report is "outline", could also pass "complete"
     * @param integer $userid
     * @param integer $courseid
     * @param string $mode either "outline" or "complete"
     * @return moodleurl url of the user/course report
     */
	function local_family_fetch_outlinereport_url($userid, $courseid, $mode='outline') {
		global $CFG,$USER;
		$urlbase = '/report/outline/user.php';
		return new moodle_url($urlbase, array('id'=>$userid,'course'=>$courseid, 'mode' => $mode));
	}
	
	/**
     * Fetch url to use to get students grade report *not used internally*
     * @param integer $userid
     * @param integer $courseid
     * @param string $mode either "outline" or "complete"
     * @return moodleurl url of the user's grade report for this course
     */
	function local_family_fetch_gradereport_url($userid, $courseid) {
		global $CFG,$USER;
		$urlbase = '/course/user.php';
		return new moodle_url($urlbase, array('user'=>$userid,'id'=>$courseid, 'mode' => 'grade'));
	}
	
	/**
     * Is this a child *not used internally*
     * @param integer $userid
     * @return boolean  true =this is a child, false = not chile
     */
	function local_family_is_child($userid) {
		global $DB;
		$role = $DB->get_field('local_family_members','role',array('userid'=>$userid));
		if(!$role){return false;}

		return $role == 'child';
	}
	
	/**
     * Is this a parent *not used internally*
     * @param integer $userid
     * @return boolean  true =this is a parent, false = not parent
     */
	function local_family_is_parent($userid) {
		global $DB;
		$role = $DB->get_field('local_family_members','role',array('userid'=>$userid));
		if(!$role){return false;}
		return $role == 'parent';
	}
		
	
/**
 * Returns list of courses passedin user is enrolled in and can access
 *
 *
 * @param string $userid
 * @param int $limit max number of courses
 * @return array
 */
function local_family_fetch_user_courses($userid, $limit=1) {
		global $DB;

	$sort = 'visible DESC,sortorder ASC';
	$user = $DB->get_record('user', array('id'=>$userid));

    // Guest account does not have any courses
    if (isguestuser() or !isloggedin()) {
        return(array());
    }

    $basefields = array('id', 'category', 'sortorder',
                        'shortname', 'fullname', 'idnumber',
                        'startdate', 'visible',
                        'groupmode', 'groupmodeforce', 'cacherev');

   
    $fields = $basefields;
    if (in_array('*', $fields)) {
        $fields = array('*');
    }

    $orderby = "";
    $sort    = trim($sort);
    if (!empty($sort)) {
        $rawsorts = explode(',', $sort);
        $sorts = array();
        foreach ($rawsorts as $rawsort) {
            $rawsort = trim($rawsort);
            if (strpos($rawsort, 'c.') === 0) {
                $rawsort = substr($rawsort, 2);
            }
            $sorts[] = trim($rawsort);
        }
        $sort = 'c.'.implode(',c.', $sorts);
        $orderby = "ORDER BY $sort";
    }

    $wheres = array("c.id <> :siteid");
    $params = array('siteid'=>SITEID);

    if (isset($user->loginascontext) and $user->loginascontext->contextlevel == CONTEXT_COURSE) {
        // list _only_ this course - anything else is asking for trouble...
        $wheres[] = "courseid = :loginas";
        $params['loginas'] = $user->loginascontext->instanceid;
    }

    $coursefields = 'c.' .join(',c.', $fields);
    $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
    $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
    $params['contextlevel'] = CONTEXT_COURSE;
    $wheres = implode(" AND ", $wheres);

    //note: we can not use DISTINCT + text fields due to Oracle and MS limitations, that is why we have the subselect there
    $sql = "SELECT $coursefields $ccselect
              FROM {course} c
              JOIN (SELECT DISTINCT e.courseid
                      FROM {enrol} e
                      JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                     WHERE ue.status = :active AND e.status = :enabled AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)
                   ) en ON (en.courseid = c.id)
           $ccjoin
             WHERE $wheres
          $orderby";
    $params['userid']  = $user->id;
    $params['active']  = ENROL_USER_ACTIVE;
    $params['enabled'] = ENROL_INSTANCE_ENABLED;
    $params['now1']    = round(time(), -2); // improves db caching
    $params['now2']    = $params['now1'];

    $courses = $DB->get_records_sql($sql, $params, 0, $limit);

    // preload contexts and check visibility
    foreach ($courses as $id=>$course) {
        context_helper::preload_from_record($course);
        if (!$course->visible) {
            if (!$context = context_course::instance($id, IGNORE_MISSING)) {
                unset($courses[$id]);
                continue;
            }
            if (!has_capability('moodle/course:viewhiddencourses', $context)) {
                unset($courses[$id]);
                continue;
            }
        }
        $courses[$id] = $course;
    }


	//return the courses
    return $courses;


  }//end of function
 
	

	
	/**
	 * user_deleted event handler
	 *
	 * @param \core\event\course_content_deleted $event The event.
	 * @return void
	 */
	function local_family_handle_user_deletion(\core\event\user_deleted $event) {
		global $DB;
		$DB->delete_records('local_family_members', array('userid' => $event->relateduserid));
	}