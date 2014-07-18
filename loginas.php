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
 * Controller for various actions of the mod.
 *
 * This page handles the display of the local mod family
 * 
 *
 * @package    local_family
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 onwards Justin Hunt  http://poodll.com
 */

require('../../config.php');

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/family/locallib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/local/family/forms.php');


require_login();

$action = required_param('action', PARAM_TEXT); //the user action to take
$userid =  optional_param('userid',0, PARAM_INT); //the id of the group
$courseid =  optional_param('courseid',0, PARAM_INT); //the courseid


$context = context_system::instance();
$PAGE->set_url('/local/family/loginas.php');
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('loginas', 'local_family'));
$PAGE->navbar->add(get_string('loginas', 'local_family'));
$renderer = $PAGE->get_renderer('local_family');


// OUTPUT
echo $renderer->header();


switch($action){

	
	case 'loginas':
		$loginasform = new local_family_loginas_form();
		$data=new stdClass;
		$data->childid = $userid;
		$data->redirect = 1;
		$data->courseid = $courseid;
		$loginasform->set_data($data);
		
		$user = $DB->get_record('user',array('id'=>$userid));
		if(!$user){return;}
		$username = fullname($user);
		$renderer->show_form($loginasform,get_string('loginasheading', 'local_family',$username));
		return;
	
		break;
	
	case 'dologinas':
	
		// Reset user back to their real self if needed, for security reasons you need to log out and log in again.
		if (\core\session\manager::is_loggedinas()) {
			require_sesskey();
			require_logout();
		}
		$loginas_form = new local_family_loginas_form();
		
		$data = $loginas_form->get_data();
		//print_r($data);
		if($data){
			if ($data->redirect) {
				if ($data->courseid and $data->courseid != SITEID) {
					$SESSION->wantsurl = "$CFG->wwwroot/course/view.php?id=".$courseid;
				} else {
					$SESSION->wantsurl = "$CFG->wwwroot/";
				}
			}
			

			$children = local_family_fetch_child_users($USER->id);
			$parents = local_family_fetch_parent_users($data->childid);
			if(!$children){
				$message = get_string('invalidparentid', 'local_family');
				$renderer->show_loginas_error($message);
				return;
			}
			
			$trueparent=false;
			foreach($children as $child){
				if($data->childid == $child->id){
					foreach($parents as $parent){
						if($parent->id ==$USER->id){
							$trueparent=true;
							break;
						}
					}
					if($trueparent){break;}
				}
			}
			if(!$trueparent){
				$message = get_string('invalidparentid', 'local_family');
				$renderer->show_loginas_error($message);
				return;
			}

			$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);

			// User must be logged in.
			$coursecontext = context_course::instance($course->id);

			// Login as this user and return to course home page.
			\core\session\manager::loginas($data->childid, $coursecontext);
			$newfullname = fullname($USER, true);

			$strloginas    = get_string('loginas');
			$strloggedinas = get_string('loggedinas', '', $newfullname);

			$PAGE->set_title($strloggedinas);
			$PAGE->set_heading($course->fullname);
			$PAGE->navbar->add($strloggedinas);
			notice($strloggedinas, "$CFG->wwwroot/course/view.php?id=$course->id");
			return;	
			
		}else{
			$message = get_string('canceledbyuser','local_family');
			$renderer->show_loginas_error($message);
			return;
		}
		
		break;
	
	
	default:
		$message = get_string('unknownaction','local_family');
		$renderer->show_loginas_error($message);
		return;


}

