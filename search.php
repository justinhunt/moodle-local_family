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
 * Controller for various actions of the block.
 *
 * This page display the community course search form.
 * It also handles adding a course to the community block.
 * It also handles downloading a course template.
 *
 * @package    block_community
 * @author     Jerome Mouneyrac <jerome@mouneyrac.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

require('../../config.php');
require_once($CFG->dirroot . '/blocks/family/locallib.php');
require_once($CFG->dirroot . '/blocks/family/forms.php');

require_login();
$courseid = required_param('courseid', PARAM_INT); //if no courseid is given
$action = required_param('action', PARAM_TEXT); //the user action to take
$groupid =  optional_param('groupid',0, PARAM_INT); //the id of the group
$familyid =  optional_param('familyid',0, PARAM_INT); //the id of the group

$parentcourse = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$context = context_course::instance($courseid);
$PAGE->set_course($parentcourse);
$PAGE->set_url('/blocks/family/view.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('listview', 'local_family'));
$PAGE->navbar->add(get_string('listview', 'local_family'));

$bmh = new local_family_manager($courseid);

// OUTPUT
echo $OUTPUT->header();
$message=false;

//only admins and editing teachers should get here really
if(!has_capability('block/family:managefamilies', $context) ){
	echo $OUTPUT->heading(get_string('inadequatepermissions', 'local_family'), 3, 'main');
	echo $OUTPUT->footer();
	return;
 }


//don't do anything without a groupid
if($groupid == 0){
	$action='group';
}else{		
	$groupname = groups_get_group_name($groupid);
	if(!$groupname){
		$message = get_string('invalidgroupid','local_family');
		$action='group';
	}
}


switch($action){
	
	case 'add':
		echo $OUTPUT->heading(get_string('addfamilyheading', 'local_family',$groupname), 3, 'main');
		$addform = new local_family_add_form(null,array('groupid'=>$groupid));
		$addform->display();
		echo $OUTPUT->footer();
		return;
	

	
	case 'edit':
		echo $OUTPUT->heading(get_string('editfamilyheading', 'local_family',$groupname), 3, 'main');
		$editform = new local_family_edit_form(null,array('groupid'=>$groupid));

		if($familyid > 0){
			$bmh = new local_family_manager();
			$hdata = $bmh->local_family_get_family($familyid);
			$hdata->familyid=$familyid;
			$editform->set_data($hdata);
			$editform->display();
		}else{
			echo get_string('invalidfamilyid', 'local_family');
		}
		
		
		echo $OUTPUT->footer();
		return;
		

	
	case 'delete':
		echo $OUTPUT->heading(get_string('deletefamilyheading', 'local_family',$groupname), 3, 'main');
		$deleteform = new local_family_delete_form(null,array('groupid'=>$groupid));
		
		if($familyid > 0){
			$bmh = new local_family_manager();
			$hdata = $bmh->local_family_get_family($familyid);
			$hdata->familyid=$familyid;		
			
			$modinfo = get_fast_modinfo($parentcourse);
			$cm = $modinfo->get_cm($hdata->cmid);
			$hdata->activityname =$cm->name;
			
			$hdata->startdate = userdate($hdata->startdate,'%d %B %Y');
			
			$deleteform->set_data($hdata);
			$deleteform->display();
		}else{
			echo get_string('invalidfamilyid', 'local_family');
		}

		echo $OUTPUT->footer();
		return;
		
	case 'group':
		//might have been possible to use moodle groups dropdown
		//http://docs.moodle.org/dev/Groups_API see groups_print_activity_menu
	
		//if we have a status message, display it.
		if($message){
			echo $OUTPUT->heading($message,5,'main');
		}
		echo $OUTPUT->heading(get_string('selectgroup', 'local_family'), 3, 'main');
		$gdata = new stdClass();
		$gdata->courseid=$courseid;
		$gdata->groupid=$groupid;
		$groupform = new local_family_group_form(null,array('courseid'=>$courseid));
		$groupform->set_data($gdata);
		$groupform->display();
		echo $OUTPUT->footer();
		return;

	
	case 'doadd':
		//get add form
		$add_form = new local_family_add_form();
		//print_r($add_form);
		$data = $add_form->get_data();
		$ret = $bmh->local_family_add_family($data->groupid,$data->courseid,$data->cmid,$data->startdate);
		if($ret){
			$message = get_string('addedsuccessfully','local_family');
		}else{
			$message = get_string('failedtoadd','local_family');
		}
		break;
		
	case 'doedit':
		//get add form
		$edit_form = new local_family_edit_form();
		//print_r($add_form);
		$data = $edit_form->get_data();
		$ret = $bmh->local_family_edit_family($data->familyid, $data->groupid,$data->courseid,$data->cmid,$data->startdate);
		if($ret){
			$message = get_string('updatedsuccessfully','local_family');
		}else{
			$message = get_string('failedtoupdate','local_family');
		}
		break;
		
	case 'dodelete':
		//To do. here collect the data from the form and update in the db using. maybe
		//get add form
		$delete_form = new local_family_delete_form();
		$data = $delete_form->get_data();
		$ret = $bmh->local_family_delete_family($data->familyid);
		if($ret){
			$message = get_string('deletedsuccessfully','local_family');
		}else{
			$message = get_string('failedtodelete','local_family');
		}
		break;
		
	case 'dogroup':
		//To do. here collect the data from the form and update in the db using. maybe
		//get add form
		$group_form = new local_family_group_form();
		$data = $group_form->get_data();
		$groupid = $data->groupid;
		$message = get_string('groupupdated','local_family');

		break;
	
	case 'list':
	default:

}

	//if we have a status message, display it.
	if($message){
		echo $OUTPUT->heading($message,5,'main');
	}

	echo $OUTPUT->heading(get_string('familylist', 'local_family', $groupname), 3, 'main');
	
	//group form
	//echo $OUTPUT->heading(get_string('selectgroup', 'local_family'), 3, 'main');
	$gdata = new stdClass();
	$gdata->courseid=$courseid;
	$gdata->groupid=$groupid;
	$groupform = new local_family_group_form();
	$groupform->set_data($gdata);
	$groupform->display();

	
	//list of familys for current group
	$familydata=$bmh->local_family_get_familys($groupid,$courseid);
	if($familydata){
		echo show_family_list($familydata,$courseid,$groupid);
	}else{
		echo $OUTPUT->heading( get_string('nofamilys','local_family',$groupname),4,'main');
	}
	echo show_buttons($groupid, $groupname);
	echo $OUTPUT->footer();
		

/**
 * Return the add list buttons at bottom of table (ugly
 * @param integer $groupid
 * @param integer $groupname
 * @return string html of buttons
 */
function show_buttons($groupid,$groupname){
	global $COURSE;
	
			$addurl = new moodle_url('/blocks/family/view.php', array('courseid'=>$COURSE->id,'action'=>'add','groupid'=>$groupid));
			echo '<br />' . html_writer::link($addurl,  get_string('addfamily','local_family',$groupname) );
			$listurl = new moodle_url('/blocks/family/view.php', array('courseid'=>$COURSE->id,'action'=>'list','groupid'=>$groupid));
			echo '<br />' . html_writer::link($listurl,  get_string('listfamilys','local_family',$groupname) );

}

/**
 * Return the html table of familys for a group  / course
 * @param array family objects
 * @param integer $courseid
 * @param integer $groupid
 * @return string html of table
 */
function show_family_list($familydatas,$courseid,$groupid){

	global $COURSE;
	
	$table = new html_table();
	$table->id = 'local_family_panel';
	$table->head = array(
		get_string('startdate', 'local_family'),
		get_string('activitytitle', 'local_family'),
		get_string('actions', 'local_family')
	);
	$table->headspan = array(1,1,2);
	$table->colclasses = array(
		'startdate', 'activitytitle', 'edit','delete'
	);
	
	$modinfo = get_fast_modinfo($COURSE);

	//sort by start date
    core_collator::asort_objects_by_property($familydatas,'startdate',core_collator::SORT_NUMERIC);

	//loop through the homoworks and add to table
	foreach ($familydatas as $hwork) {
		$row = new html_table_row();
		
		
		$startdatecell = new html_table_cell(userdate($hwork->startdate,'%d %B %Y'));
		
		$cm = $modinfo->get_cm($hwork->cmid);
		$displayname=$cm->name;
		$activityname  = html_writer::tag('div', $displayname, array('class' => 'displayname'));
		$activitycell  = new html_table_cell($activityname);
		
		$actionurl = '/blocks/family/view.php';
		$editurl = new moodle_url($actionurl, array('familyid'=>$hwork->id,'action'=>'edit','courseid'=>$courseid,'groupid'=>$groupid));
		$editlink = html_writer::link($editurl, get_string('editfamilylink', 'local_family'));
		$editcell = new html_table_cell($editlink);
		
		$deleteurl = new moodle_url($actionurl, array('familyid'=>$hwork->id,'action'=>'delete','courseid'=>$courseid,'groupid'=>$groupid));
		$deletelink = html_writer::link($deleteurl, get_string('deletefamilylink', 'local_family'));
		$deletecell = new html_table_cell($deletelink);

		$row->cells = array(
			$startdatecell, $activitycell, $editcell, $deletecell
		);
		$table->data[] = $row;
	}

    return html_writer::table($table);

}