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
 * Community library
 *
 * @package    local_family
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 onwards Justin Hunt
 *
 *
 */
 
require_once($CFG->dirroot . '/local/family/lib.php');

class local_family_manager {

	private $courseid=0;
	private $course=null;
	
	/**
     * constructor. make sure we have the right course
     * @param integer courseid id
	*/
	function local_family_manager($courseid=0) {
		global $COURSE;
		if($courseid){
			$this->courseid=$courseid;
		}else{
			$this->courseid = $COURSE->id; 
			$this->course = $COURSE;
		}
    }


  /**
     * Add a family role
     * @param integer group id
     * @param integer course module id
     * @return id of family or false if already added
     */
    public function add_role($familyid,$userid, $role ) {
        global $DB,$USER;

         $family =  local_family_fetch_family_by_member($userid);
         
       if (empty($family)) {
            $member = new stdClass();
            $member->familyid= $familyid;
			$member->userid= $userid;
			$member->role= $role;
           if ($DB->insert_record('local_family_members', $member, true)){
            	$this->sync_parentrole($familyid);
            	return true;
            }else{
            	return false;
            }
       } else {
            return false;
       }
    }
    
    public function edit_role($memberid, $familyid,$userid, $role ) {
        global $DB,$USER;

            $member = new stdClass();
            $member->id = $memberid;
			$member->familyid = $familyid;
            $member->userid= $userid;
			$member->role = $role;
        if( $DB->update_record('local_family_members', $member))
		{
			return true;
        } else {
            return false;
        }
    }
    
    /**
     * Delete a family
     * @param integer $familyid
     * @return bool true
     */
    public function delete_role($id) {
        global $DB;
        $familymember = $DB->get_record('local_family_members',array('id'=>$id));
        if(!$familymember){return false;}
        $this->unsync_parentrole($familymember);
        return $DB->delete_records('local_family_members', array('id' => $id));   	
    }
    
        /**
     * Unassign the Moodle family role
     * @param integer $child_userid
     * @param integer $parent_userid
     * @param object $parent_role 
     * @return bool true
     */
    public function unsync_parentrole($familymember, $parentrole = null){
    	global $DB;
    	$ret =false;
    	if(!$parentrole){
    		$parentrole  = $DB->get_record('role', array('shortname'=>'parent'));
    	}
    	
    	switch ($familymember->role){
    		case 'parent':
    			$children = local_family_fetch_parents_by_family($familymember->familyid);
    			foreach($children as $child){
    				$childcontext = context_user::instance($child->userid);
    				if(user_has_role_assignment($familymember->userid, $parentrole->id, $childcontext->id)){
    					role_unassign($parentrole->id, $familymember->userid, $childcontext->id);
    				}
    			}
    			break;
    		case 'child':
    			 $parents = local_family_fetch_parents_by_family($familymember->familyid);
    			 $childcontext = context_user::instance($familymember->userid);
    			 foreach($parents as $parent){
    			 	if(user_has_role_assignment($parent->userid, $parentrole->id, $childcontext->id)){
    			 		role_unassign($parentrole->id, $parent->userid, $childcontext->id);
    			 	}
    			 }
    			break;
    	
    	}//end of switch
    
    }//end of function
    
    
    /**
     * Assign the Moodle family role
     * @param integer $child_userid
     * @param integer $parent_userid
     * @param object $parent_role 
     * @return bool true
     */
    public function sync_parentrole($familyid, $parentrole = null){
    	global $DB;
    	$ret =false;
    	if(!$parentrole){
    		$parentrole  = $DB->get_record('role', array('shortname'=>'parent'));
    	}
    	
    	//fetch family members
    	$children = local_family_fetch_children_by_family($familyid);
    	$parents = local_family_fetch_parents_by_family($familyid);
    	foreach($children as $child){
    		$childcontext = context_user::instance($child->userid);
    		foreach($parents as $parent){
    			if (!user_has_role_assignment($parent->userid, $parentrole->id, $childcontext->id)){
    				$this->assign_parentrole($child->userid, $parent->userid,$parentrole,$childcontext);
    			}//end of if has r assignment
    		}//end of parents loop
    	}//end of children loop
    	
    }
    
    /**
     * Assign the Moodle family role
     * @param integer $child_userid
     * @param integer $parent_userid
     * @param object $parent_role 
     * @return bool true
     */
    public function assign_parentrole($child_userid, $parent_userid, $parentrole = null,$childcontext = null){
    	global $DB;
    	$ret =false;
    	if(!$parentrole){
    		$parentrole  = $DB->get_record('role', array('shortname'=>'parent'));
    	}
    	if(!$childcontext){
    		$childcontext = context_user::instance($child_userid);
    	}
    	
    	if($parentrole && $childcontext){
    		$ret =role_assign($parentrole->id, $parent_userid, $childcontext->id);
    	}
    	if($ret){
    		return true;
    	}else{
    		return false;
    	}
    }
    
       /**
     * Assign the Moodle family role
     * @param integer $child_userid
     * @param integer $parent_userid
     * @param object $parent_role 
     * @return bool true
     */
    public function unassign_parentrole($child_userid, $parent_userid, $parentrole = null){
    	global $DB;
    	$ret =false;
    	if(!$parentrole){
    		$parentrole  = $DB->get_record('role', array('shortname'=>'parent'));
    	}
    	$childcontext = context_user::instance($child_userid);
    	if($parentrole && $childcontext){
    		$ret =role_unassign($parentrole->id, $parent_userid, $childcontext->id);
    	}
    	if($ret){
    		return true;
    	}else{
    		return false;
    	}
    }
    
    
    /**
     * Add a family activity
     * @param integer group id
     * @param integer course module id
     * @return id of family or false if already added
     */
    public function add_family($familykey, $familynotes) {
        global $DB,$USER;

       		$family = local_family_fetch_family_by_key($familykey);
       		if(!$family){
				$family = new stdClass();
				$family->familykey= $familykey;
				$family->familynotes = $familynotes;
				return $DB->insert_record('local_family', $family,true);
            }else{
            	return false;
            }

    }
	
	public function edit_family($familyid,$familykey, $familynotes) {
        global $DB,$USER;
		
			$family = local_family_fetch_family_by_key($familykey);
			if($family && $family->id != $familyid){
				return false;
			}else{
            	$family = new stdClass();
				$family->id = $familyid;
            	$family->familykey= $familykey;
				$family->familynotes = $familynotes;
			}
        if( $DB->update_record('local_family', $family))
		{
			return true;
        } else {
            return false;
        }
    }
   
   /**
     * Return array of families data, suitable for list
     * @param integer $familyid
     * @return array of course
     */ 
   public function fetch_families($conditions){
   		global $DB, $OUTPUT;
		$where ="";
		if($conditions && count($conditions > 0)){
			
			foreach($conditions as $name => $value){
				if($where !=""){
					$where = " AND ";
				}else{
					$where = " WHERE ";
				}
				$where .= 'u.' . $name . " = '" . $value ."' "; 
			}
			$sql = 'SELECT DISTINCT lf.id as id, lf.familykey as familykey 
			FROM {local_family} lf 
			INNER JOIN {local_family_members} lfm ON lf.id = lfm.familyid 
			INNER JOIN {user} u ON u.id=lfm.userid ';
   		}else{
   			$sql = 'SELECT DISTINCT lf.id as id, lf.familykey as familykey 
			FROM {local_family} lf';
   		}
   		$sql .= $where;
   		
   		$families = $DB->get_records_sql_menu($sql);
   		$ret = array();
   		foreach($families as $id=>$familykey){
   			$members = $this->get_members($id);
   			$children = array();
   			$parents = array();
   			$upic = array();
   			foreach($members as $member){

   				if($member->role == 'child'){
   					$children[] = $member;
   				}else{
   					$parents[]= $member;	
   				}
   			}
   			
   			$f = new stdClass();
   			$f->familykey = $familykey;
   			$f->upics = $upic;
   			$f->parents = $parents;
   			$f->children =$children;
   			$f->id = $id;
   			$ret[] = $f;
   		} 
   		
   		return $ret;
   		
   }
	

    /**
     * Return a single family
     * @param integer $familyid
     * @return array of course
     */
    public function get_family($familyid) {
        global $DB;
        return $DB->get_record('local_family',
                array('id' => $familyid));
    }
    
    /**
     * Return a single member of a family
     * @param integer $familyid
     * @return array of course
     */
    public function get_member($memberid) {
        global $DB;
        return $DB->get_record('local_family_members',
                array('id' => $memberid));
    }

	 /**
     * Return a single member of a family
     * @param integer $familyid
     * @return array of course
     */
    public function get_members($familyid) {
        global $DB;
        $sql = 'SELECT *
			FROM {local_family_members} lfm 
			INNER JOIN {user} u ON u.id=lfm.userid 
			WHERE lfm.familyid = ' . $familyid;
   		
   		$members = $DB->get_records_sql($sql);
		return $members;
    }
    
    /**
     * Delete a family
     * @param integer $familyid
     * @return bool true
     */
    public function delete_family($familyid) {
        global $DB;
        $DB->delete_records('local_family_members', array('familyid' => $familyid));
        return $DB->delete_records('local_family',
                array('id' => $familyid));
    }
	
	/*
     * Get all the groups
     * @param integer $familyid
     * @return array all the groups
     */
	function get_grouplist(){
		$groups = groups_get_all_groups($this->courseid);
		return $groups;
	}
	


}
