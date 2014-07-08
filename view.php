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



$context = context_system::instance();
$PAGE->set_url('/local/family/view.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('listview', 'local_family'));
$PAGE->navbar->add(get_string('listview', 'local_family'));

$bfm = new local_family_manager($familyid);

// OUTPUT
echo $OUTPUT->header();
$message=false;

//only admins and editing teachers should get here really
//if(!has_capability('local/family:managefamilies', $context) ){
// just while debugging disable this J: 20140708
if(false){
	echo $OUTPUT->heading(get_string('inadequatepermissions', 'local_family'), 3, 'main');
	echo $OUTPUT->footer();
	return;
 }


//try and get a family key
$familykey="";	
if($familyid){
	$family = $bfm->local_family_get_family($familyid);
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
		local_family_show_form($addform,get_string('addroleheading', 'local_family',$familykey));
		return;
	
	case 'deleterole':

		if(!$familyid || !$userid){
			$fmember = $bfm->local_family_get_member($memberid);
			if($fmember){
				$userid = $fmember->userid;
				$familyid = $fmember->familyid;
			}else{
				$message = get_string('invalidmemberid', 'local_family');
				local_family_show_error($message);
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
			local_family_show_form($deleteform,get_string('deleteroleheading', 'local_family',$familykey));
			return;
		}else{
			$message=  get_string('failedtogetmemberinfo', 'local_family');
			local_family_show_error($message);
			return;	
		}

		

	case 'addfamily':
		$addform = new local_family_add_family_form();
		local_family_show_form($addform,get_string('addfamilyheading', 'local_family'));
		return;
	
	case 'editfamily':
		if($familyid > 0){
			$editform = new local_family_edit_family_form();
			$bfm = new local_family_manager();
			$fdata = $bfm->local_family_get_family($familyid);
			//$fdata->id=$familyid;
			$editform->set_data($fdata);
			local_family_show_form($editform,get_string('editfamilyheading', 'local_family',$familykey));
			return;
		}else{
			local_family_show_error(get_string('invalidfamilyid', 'local_family'));
			return;
		}
		
	
	case 'deletefamily':
		if($familyid > 0){
			$deleteform = new local_family_delete_family_form();
			$bfm = new local_family_manager();
			$fdata = $bfm->local_family_get_family($familyid);
			$fdata->id=$familyid;
			$deleteform->set_data($fdata);
			local_family_show_form($deleteform,get_string('deletefamilyheading', 'local_family',$familykey));
			return;
		}else{
			$message=  get_string('invalidfamilyid', 'local_family');
			local_family_show_error($message);
			return;
		}
		
	case 'searchfamily':
		$searchform = new local_family_search_family_form();
		$data=new stdClass;
		$searchform->set_data($data);
		local_family_show_form($searchform,get_string('dosearch', 'local_family'));
		return;
		
	case 'doaddfamily':
		//get add form
		$add_form = new local_family_add_family_form();
		$data = $add_form->get_data();
		if($data){
			$ret = $bfm->local_family_add_family($data->familykey, $data->familynotes);
			$familykey = $data->familykey;
			if($ret){
				$message = get_string('updatedsuccessfully','local_family');
				$familyid = $ret;
				local_family_show_single_family($familyid,$familykey, $message);
				return;
			}else{
				$message = get_string('failedtoupdate','local_family');
			}
		}else{
			$message = get_string('canceledbyuser','local_family');
		}
		local_family_show_all_families($message);
		return;
		
	case 'doeditfamily':
		$edit_form = new local_family_edit_family_form();
		$data = $edit_form->get_data();
		if($data){
			$ret = $bfm->local_family_edit_family($data->id, $data->familykey, $data->familynotes);
			if($ret){
				$message = get_string('updatedsuccessfully','local_family');
			}else{
				$message = get_string('failedtoupdate','local_family');
			}
		}else{
			$message = get_string('canceledbyuser','local_family');
		}
		local_family_show_all_families($message);
		return;
			
	case 'dodeletefamily':
		//Delete an entire family
		$delete_form = new local_family_delete_family_form();
		$data = $delete_form->get_data();
		if($data){
			$ret = $bfm->local_family_delete_family($data->id);
			if($ret){
				$message = get_string('deletedsuccessfully','local_family');
			}else{
				$message = get_string('failedtodelete','local_family');
			}
		}else{
			$message = get_string('canceledbyuser','local_family');
		}
		local_family_show_all_families($message);
		return;
		
	case 'dosearchfamily':
		$search_form = new local_family_search_family_form();
		$data = $search_form->get_data();
		if($data){
			$conditions = array('id'=>$data->userid);
			local_family_show_some_families($conditions);
			return;
		}else{
			$message = get_string('canceledbyuser','local_family');
		}
		local_family_show_all_families($message);
		return;

	
	
	case 'doaddrole':
		//get add form
		$add_form = new local_family_add_role_form();
		$data = $add_form->get_data();
		if($data){
			$ret = $bfm->local_family_add_role($data->familyid,$data->userid,$data->role);
			if($ret){
				$message = get_string('addedmembersuccessfully','local_family');
			}else{
				$message = get_string('failedtoaddfamilymember','local_family');
			}
		}else{
			$message = get_string('canceledbyuser','local_family');
		}
		local_family_show_single_family($familyid,$familykey, $message);
		return;

		

		
	case 'dodeleterole':
		//To do. here collect the data from the form and update in the db using. maybe
		//get add form
		$delete_form = new local_family_delete_role_form();
		$data = $delete_form->get_data();
		if($data){
			$ret = $bfm->local_family_delete_role($data->id);
			if($ret){
				$message = get_string('deletedmembersuccessfully','local_family');
			}else{
				$message = get_string('failedtodeletemember','local_family');
			}
		}else{
			$message = get_string('canceledbyuser','local_family');
		}
		local_family_show_single_family($familyid,$familykey, $message);
		return;


	
	case 'listall':
		local_family_show_all_families($message);
		return;

		
	case 'listsingle':
		local_family_show_single_family($familyid, $familykey, $message);
		return;
	default:

}

	

//The default action is to list all the families and any messages that get here.
local_family_show_all_families($message);



function local_family_show_single_family($familyid,$familykey, $message=""){
	global $OUTPUT;
	
	//if we have a status message, display it.
	if($message){
		echo $OUTPUT->heading($message,5,'main');
	}
	
	echo $OUTPUT->heading(get_string('showsinglefamily', 'local_family', $familykey), 3, 'main');
	
	$children= local_family_fetch_children_by_family($familyid);
	$parents= local_family_fetch_parents_by_family($familyid);
	echo $OUTPUT->box_start();
	if($parents){
		echo local_family_show_member_list($parents, 'parent', $familyid);
	}else{
		echo $OUTPUT->heading( get_string('noparents','local_family',$familykey),4,'main');
	}
	echo $OUTPUT->box_end();
	local_family_show_add_role_button('parent',$familyid,$familykey);
	
	echo $OUTPUT->box_start();
	if($children){
		echo local_family_show_member_list($children, 'child', $familyid);
	}else{
		echo $OUTPUT->heading( get_string('nochildren','local_family',$familykey),4,'main');
	}
	echo $OUTPUT->box_end();
	local_family_show_add_role_button('child',$familyid,$familykey);
		echo $OUTPUT->footer();


}

/**
 * Show a form
 * @param mform $showform the form to display
 * @param string $heading the title of the form
 * @param string $message any status messages from previous actions
 */
function local_family_show_form($showform,$heading, $message=''){
	global $OUTPUT;
	
	//if we have a status message, display it.
	if($message){
		echo $OUTPUT->heading($message,5,'main');
	}
	echo $OUTPUT->heading($heading, 3, 'main');
	$showform->display();
	echo $OUTPUT->footer();
}

/**
 * Show the "add" button beneath the parent/child section of the single family
 * @param string $role (parent / child)
 * @param integer $familyid
 * @return string $familykey
 */
function local_family_show_add_role_button($role,$familyid, $familykey){
	global $OUTPUT;
	$addurl = new moodle_url('/local/family/view.php', array('action'=>'addrole','familyid'=>$familyid, 'role'=>$role));
	echo $OUTPUT->single_button($addurl,get_string('add'. $role . 'tofamily','local_family',$familykey));
		
}

/**
 * Show the "add" button on the family list page
 *
 */
function local_family_show_add_family_button(){
	global $OUTPUT;
	$addurl = new moodle_url('/local/family/view.php', array('action'=>'addfamily'));
	echo $OUTPUT->single_button($addurl,get_string('addfamily','local_family'));
}

/**
 * Return the html table of members of a family
 * @param array family objects
 * @param string $role (parent or child)
 * @return string html of table
 */
function local_family_show_member_list($familymembers,$role, $familyid){

	global  $DB, $OUTPUT;
	
	$table = new html_table();
	$table->id = 'local_' . $role . '_panel';
	$table->head = array(
		get_string('picture', 'local_family'),
		get_string('fullname', 'local_family'),
		get_string('actions', 'local_family')
	);
	$table->headspan = array(1,1,2);
	$table->colclasses = array(
		'picture', 'fullname', 'message','delete'
	);


	//loop through the homoworks and add to table
	foreach ($familymembers as $member) {
		$row = new html_table_row();
		$memberuser = $DB->get_record('user',array('id'=>$member->userid));
		
		$picturecell = new html_table_cell($OUTPUT->user_picture($memberuser));
		$fullname=fullname($memberuser);
		$fullname  = html_writer::tag('div', $fullname, array('class' => 'fullname'));
		$fullnamecell  = new html_table_cell($fullname);
		
		$actionurl = '/local/family/view.php';
		$messageurl = new moodle_url($actionurl, array());
		$messagelink = html_writer::link($messageurl, get_string('messagelink', 'local_family'));
		$messagecell = new html_table_cell($messagelink);
		
		$deleteurl = new moodle_url($actionurl, array('familyid'=>$familyid,'action'=>'deleterole','userid'=>$member->userid,'memberid'=>$member->id,'role'=>$role));
		$deletelink = html_writer::link($deleteurl, get_string('deletememberlink', 'local_family'));
		$deletecell = new html_table_cell($deletelink);

		$row->cells = array(
			$picturecell, $fullnamecell, $messagecell, $deletecell
		);
		$table->data[] = $row;
	}

    return html_writer::table($table);

}

/**
 * Show an error, and return to top of family list page
 * @param string $message
 */
function local_family_show_error($message){
	local_family_show_all_families($message);
}

/**
 * Show *all* families
 * @param string $message any status messages can be displayed
 */
function local_family_show_all_families($message = ''){
	$bfm = new local_family_manager();
	$conditions = array();
	$familydata =  $bfm->local_family_fetch_families($conditions);
	local_family_show_families_list($familydata, $message);
}

/**
 * Show families depending on condition families
 * @param array $conditions user table fields to match
 * @param string $message any status messages can be displayed
 */
function local_family_show_some_families($conditions,$message = ''){
	$bfm = new local_family_manager();
	$familydata =  $bfm->local_family_fetch_families($conditions);
	local_family_show_families_list($familydata, $message);
}

/**
 * Show  families
 * @param array $family data objects 
 * @param string $message any status messages can be displayed
 */
function local_family_show_families_list($familydata, $message=""){
	global $OUTPUT;
	//if we have a status message, display it.
	if($message){
		echo $OUTPUT->heading($message,5,'main');
	}
	echo $OUTPUT->heading(get_string('listfamilies', 'local_family'), 3, 'main');
	local_family_show_add_family_button();
	echo local_family_get_families_table($familydata);
	echo $OUTPUT->footer();
}

/**
 * Return the html table of all families (in search)
 * @param array family objects
 * @return string html of table
 */
function local_family_get_families_table($familydatas){

	global  $DB, $OUTPUT;
	
	$table = new html_table();
	$table->id = 'local_family_familylist_panel';
	$table->head = array(
		get_string('familykey', 'local_family'),
		get_string('firstparentname', 'local_family'),
		get_string('childrennames', 'local_family'),
		get_string('actions', 'local_family')
	);
	$table->headspan = array(1,1,1,3);
	$table->colclasses = array(
		'familykey', 'firstparentname', 'childrennames','view','edit','delete'
	);


	//loop through the families and add to table
	foreach ($familydatas as $family) {
		$row = new html_table_row();
		//familykey
		$familykeycell = new html_table_cell($family->familykey);
		
		//parent
		$parentnames ="";
		if(count($family->parents)==0){
			$parentnames = get_string('undefined', 'local_family');
		}else{
			foreach($family->parents as $parent){
				if($parentnames!=''){$parentnames .= '<br />';}
				$parentnames .=  $OUTPUT->user_picture($parent) . fullname($parent); 
			}
		}
		$parentnamescell  = new html_table_cell($parentnames);
		
		//children
		$childrennames=""; 
		if(count($family->children)==0){
			$childrennames = get_string('undefined', 'local_family');
		}else{
			foreach($family->children as $child){
				if($childrennames!=''){$childrennames .= '<br />';}
				$childrennames .=  $OUTPUT->user_picture($child) . fullname($child); 
			}
		}
		$childrennamescell = new html_table_cell($childrennames);
		
		//actions
		$actionurl = '/local/family/view.php';
		$viewurl = new moodle_url($actionurl, array('familyid'=>$family->id,'action'=>'listsingle'));
		$viewlink = html_writer::link($viewurl, get_string('viewlink', 'local_family'));
		$viewcell = new html_table_cell($viewlink);

		$editurl = new moodle_url($actionurl, array('familyid'=>$family->id,'action'=>'editfamily'));
		$editlink = html_writer::link($editurl, get_string('editlink', 'local_family'));
		$editcell = new html_table_cell($editlink);
		
		$deleteurl = new moodle_url($actionurl, array('familyid'=>$family->id,'action'=>'deletefamily'));
		$deletelink = html_writer::link($deleteurl, get_string('deletememberlink', 'local_family'));
		$deletecell = new html_table_cell($deletelink);

		$row->cells = array(
			$familykeycell, $parentnamescell, $childrennamescell, $viewcell,$editcell, $deletecell
		);
		$table->data[] = $row;
	}

    return html_writer::table($table);

}