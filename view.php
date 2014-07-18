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
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/family/locallib.php');
require_once($CFG->dirroot . '/local/family/forms.php');

admin_externalpage_setup('managefamilies');

require_login();

$action = required_param('action', PARAM_TEXT); //the user action to take
$familyid =  optional_param('familyid',0, PARAM_INT); //the id of the group
$memberid =  optional_param('memberid',0, PARAM_INT); //the id of the group
$userid =  optional_param('userid',0, PARAM_INT); //the id of the group
$role =  optional_param('role','', PARAM_TEXT); //the actual role name
$courseid =  optional_param('courseid',0, PARAM_INT); //the courseid


$context = context_system::instance();
$PAGE->set_url('/local/family/view.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('listview', 'local_family'));
$PAGE->navbar->add(get_string('listview', 'local_family'));
$renderer = $PAGE->get_renderer('local_family');

$bfm = new local_family_manager($familyid);

// OUTPUT
echo $renderer->header();
$message=false;

//only admins and editing teachers should get here really
//if(!has_capability('local/family:managefamilies', $context) ){
// just while debugging disable this J: 20140708
if(false){
	echo $renderer->heading(get_string('inadequatepermissions', 'local_family'), 3, 'main');
	echo $renderer->footer();
	return;
 }


//try and get a family key
$familykey="";	
if($familyid){
	$family = $bfm->get_family($familyid);
	if(!$family){
		$message = get_string('invalidfamilyid','local_family');
	}else{
		$familykey = $family->familykey;
	}
}


switch($action){

	
	case 'addrole':
		$addform = new local_family_add_role_form();
		$data=new stdClass;
		$data->familyid = $familyid;
		$data->role = $role;
		$addform->set_data($data);
		$renderer->show_form($addform,get_string('addroleheading', 'local_family',$familykey));
		return;
	
	case 'deleterole':

		if(!$familyid || !$userid){
			$fmember = $bfm->get_member($memberid);
			if($fmember){
				$userid = $fmember->userid;
				$familyid = $fmember->familyid;
			}else{
				$message = get_string('invalidmemberid', 'local_family');
				local_family_show_error($renderer,$message);
				return;
			}
		}
		
		$deleteform = new local_family_delete_role_form();
		$fdata = new stdClass();
		$fdata->id=$memberid;	
		$fdata->familyid=$familyid;	
		$fdata->familykey=$familykey;		
		$member = $DB->get_record('user',array('id'=>$userid));
		if($member){
			$fdata->fullname = fullname($member);
			$deleteform->set_data($fdata);
			$renderer->show_form($deleteform,get_string('deleteroleheading', 'local_family',$familykey));
			return;
		}else{
			$message=  get_string('failedtogetmemberinfo', 'local_family');
			local_family_show_error($renderer,$message);
			return;	
		}

		

	case 'addfamily':
		$addform = new local_family_add_family_form();
		$renderer->show_form($addform,get_string('addfamilyheading', 'local_family'));
		return;
	
	case 'editfamily':
		if($familyid > 0){
			$editform = new local_family_edit_family_form();
			$bfm = new local_family_manager();
			$fdata = $bfm->get_family($familyid);
			//$fdata->id=$familyid;
			$editform->set_data($fdata);
			$renderer->show_form($editform,get_string('editfamilyheading', 'local_family',$familykey));
			return;
		}else{
			local_family_show_error($renderer,get_string('invalidfamilyid', 'local_family'));
			return;
		}
		
	
	case 'deletefamily':
		if($familyid > 0){
			$deleteform = new local_family_delete_family_form();
			$bfm = new local_family_manager();
			$fdata = $bfm->get_family($familyid);
			$fdata->id=$familyid;
			$deleteform->set_data($fdata);
			$renderer->show_form($deleteform,get_string('deletefamilyheading', 'local_family',$familykey));
			return;
		}else{
			$message=  get_string('invalidfamilyid', 'local_family');
			local_family_show_error($renderer,$message);
			return;
		}
		
	case 'searchfamily':
		$searchform = new local_family_search_family_form();
		$data=new stdClass;
		$searchform->set_data($data);
		$renderer->show_form($searchform,get_string('dosearch', 'local_family'));
		return;
		
	case 'doaddfamily':
		//get add form
		$add_form = new local_family_add_family_form();
		$data = $add_form->get_data();
		if($data){
			$ret = $bfm->add_family($data->familykey, $data->familynotes);
			$familykey = $data->familykey;
			if($ret){
				$message = get_string('updatedsuccessfully','local_family');
				$familyid = $ret;
				$children= local_family_fetch_children_by_family($familyid);
				$parents= local_family_fetch_parents_by_family($familyid);
				$renderer->show_single_family($familyid,$familykey,$children,$parents, $message);
				return;
			}else{
				$message = get_string('failedtoupdate','local_family');
			}
		}else{
			$message = get_string('canceledbyuser','local_family');
		}
		local_family_show_all_families($renderer,$message);
		return;
		
	case 'doeditfamily':
		$edit_form = new local_family_edit_family_form();
		$data = $edit_form->get_data();
		if($data){
			$ret = $bfm->edit_family($data->id, $data->familykey, $data->familynotes);
			if($ret){
				$message = get_string('updatedsuccessfully','local_family');
			}else{
				$message = get_string('failedtoupdate','local_family');
			}
		}else{
			$message = get_string('canceledbyuser','local_family');
		}
		local_family_show_all_families($renderer,$message);
		return;
			
	case 'dodeletefamily':
		//Delete an entire family
		$delete_form = new local_family_delete_family_form();
		$data = $delete_form->get_data();
		if($data){
			$ret = $bfm->delete_family($data->id);
			if($ret){
				$message = get_string('deletedsuccessfully','local_family');
			}else{
				$message = get_string('failedtodelete','local_family');
			}
		}else{
			$message = get_string('canceledbyuser','local_family');
		}
		local_family_show_all_families($renderer,$message);
		return;
		
	case 'dosearchfamily':
		$search_form = new local_family_search_family_form();
		$data = $search_form->get_data();
		if($data){
			$conditions = array('id'=>$data->userid);
			local_family_show_some_families($renderer,$conditions);
			return;
		}else{
			$message = get_string('canceledbyuser','local_family');
		}
		local_family_show_all_families($renderer,$message);
		return;

	
	
	case 'doaddrole':
		//get add form
		$add_form = new local_family_add_role_form();
		$data = $add_form->get_data();
		if($data){
			$ret = $bfm->add_role($data->familyid,$data->userid,$data->role);
			if($ret){
				$message = get_string('addedmembersuccessfully','local_family');
			}else{
				$message = get_string('failedtoaddfamilymember','local_family');
			}
		}else{
			$message = get_string('canceledbyuser','local_family');
		}
		$children= local_family_fetch_children_by_family($familyid);
		$parents= local_family_fetch_parents_by_family($familyid);
		$renderer->show_single_family($familyid,$familykey,$children,$parents, $message);
		return;

		

		
	case 'dodeleterole':
		//To do. here collect the data from the form and update in the db using. maybe
		//get add form
		$delete_form = new local_family_delete_role_form();
		$data = $delete_form->get_data();
		if($data){
			$ret = $bfm->delete_role($data->id);
			if($ret){
				$message = get_string('deletedmembersuccessfully','local_family');
			}else{
				$message = get_string('failedtodeletemember','local_family');
			}
		}else{
			$message = get_string('canceledbyuser','local_family');
		}
		$children= local_family_fetch_children_by_family($familyid);
		$parents= local_family_fetch_parents_by_family($familyid);
		$renderer->show_single_family($familyid,$familykey,$children,$parents, $message);
		return;


	
	case 'listall':
		local_family_show_all_families($renderer,$message);
		return;

		
	case 'listsingle':
		$children= local_family_fetch_children_by_family($familyid);
		$parents= local_family_fetch_parents_by_family($familyid);
		$renderer->show_single_family($familyid,$familykey,$children,$parents, $message);
		return;
	default:

}


//The default action is to list all the families and any messages that get here.
local_family_show_all_families($renderer,$message);

	/**
	 * Show an error, and return to top of family list page
	 * @param string $message
	 */
	function local_family_show_error($renderer,$message){
		$this->show_all_families($renderer, $message);
	}

	/**
	 * Show *all* families
	 * @param string $message any status messages can be displayed
	 */
	function local_family_show_all_families($renderer, $message = ''){
		$bfm = new local_family_manager();
		$conditions = array();
		$familydata =  $bfm->fetch_families($conditions);
		$renderer->show_families_list($familydata, $message);
	}

	/**
	 * Show families depending on condition families
	 * @param array $conditions user table fields to match
	 * @param string $message any status messages can be displayed
	 */
	function local_family_show_some_families($renderer,$conditions,$message = ''){
		$bfm = new local_family_manager();
		$familydata =  $bfm->fetch_families($conditions);
		$renderer->show_families_list($familydata, $message);
	}

