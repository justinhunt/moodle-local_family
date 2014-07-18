<?php

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////


/**
 * Local family renderer.
 * @package   local_family
 * @copyright 2014 Justin Hunt (poodllsupport@gmail.com)
 * @author    Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_family_renderer extends plugin_renderer_base {

	/**
	 * Show the "add" button beneath the parent/child section of the single family
	 * @param string $role (parent / child)
	 * @param integer $familyid
	 * @return string $familykey
	 */
	function show_add_role_button($role,$familyid, $familykey){
		global $OUTPUT;
		$addurl = new moodle_url('/local/family/view.php', array('action'=>'addrole','familyid'=>$familyid, 'role'=>$role));
		echo $this->output->single_button($addurl,get_string('add'. $role . 'tofamily','local_family',$familykey));
		
	}

	/**
	 * Show the "add" button on the family list page
	 *
	 */
	function show_add_family_button(){
		global $OUTPUT;
		$addurl = new moodle_url('/local/family/view.php', array('action'=>'addfamily'));
		echo $this->output->single_button($addurl,get_string('addfamily','local_family'));
	}

	/**
	 * Return the html table of members of a family
	 * @param array family objects
	 * @param string $role (parent or child)
	 * @return string html of table
	 */
	function show_member_list($familymembers,$role, $familyid){

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
		
			$picturecell = new html_table_cell($this->output->user_picture($memberuser));
			$fullname=fullname($memberuser);
			$fullname  = html_writer::tag('div', $fullname, array('class' => 'fullname'));
			$fullnamecell  = new html_table_cell($fullname);
		
			$actionurl = '/local/family/view.php';
			$messageurl = new moodle_url($actionurl, array());
			
			$messageurl = local_family_fetch_loginas_url($member->userid,2);
			
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
	 * Show  families
	 * @param array $family data objects 
	 * @param string $message any status messages can be displayed
	 */
	function show_families_list($familydata, $message=""){
		//if we have a status message, display it.
		if($message){
			echo $this->output->heading($message,5,'main');
		}
		echo $this->output->heading(get_string('listfamilies', 'local_family'), 3, 'main');
		$this->show_add_family_button();
		echo $this->get_families_table($familydata);
		echo $this->output->footer();
	}

	/**
	 * Return the html table of all families (in search)
	 * @param array family objects
	 * @return string html of table
	 */
	function get_families_table($familydatas){

		global $CFG, $DB;
	
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
					$parentnames .=  $this->output->user_picture($parent) . fullname($parent); 
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
					$childrennames .=  $this->output->user_picture($child) . fullname($child); 
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
	
	function show_loginas_error($message=""){

		echo $this->output->heading(get_string('loginaserror', 'local_family'), 3, 'main');

		//if we have a status message, display it.
		if($message){
			echo $this->output->heading($message,5,'main');
		}
	
		
	
		echo $this->output->footer();


	}

	function show_single_family($familyid,$familykey, $children, $parents, $message=""){

		//if we have a status message, display it.
		if($message){
			echo $this->output->heading($message,5,'main');
		}
	
		echo $this->output->heading(get_string('showsinglefamily', 'local_family', $familykey), 3, 'main');
	
		echo $this->output->box_start();
		if($parents){
			echo $this->show_member_list($parents, 'parent', $familyid);
		}else{
			echo $this->output->heading( get_string('noparents','local_family',$familykey),4,'main');
		}
		echo $this->output->box_end();
		$this->show_add_role_button('parent',$familyid,$familykey);
	
		echo $this->output->box_start();
		if($children){
			echo $this->show_member_list($children, 'child', $familyid);
		}else{
			echo $this->output->heading( get_string('nochildren','local_family',$familykey),4,'main');
		}
		echo $this->output->box_end();
		$this->show_add_role_button('child',$familyid,$familykey);
		echo $this->output->footer();


	}

	/**
	 * Show a form
	 * @param mform $showform the form to display
	 * @param string $heading the title of the form
	 * @param string $message any status messages from previous actions
	 */
	function show_form($showform,$heading, $message=''){
		global $OUTPUT;
	
		//if we have a status message, display it.
		if($message){
			echo $this->output->heading($message,5,'main');
		}
		echo $this->output->heading($heading, 3, 'main');
		$showform->display();
		echo $this->output->footer();
	}



	
	
}
