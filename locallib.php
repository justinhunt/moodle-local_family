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
 
 
		const LF_UNKNOWN = -1;
		const LF_STARTFINISH = 0;
		const LF_ADD = 1;
		const LF_REMOVE = 2;
		const LF_COMMENT = 3;
		const LF_FAMILYKEY = 4;
		const LF_SEARCHFAMILYKEY = 5;
 
 
require_once($CFG->dirroot . '/local/family/lib.php');

class local_family_manager {

	private $courseid=0;
	private $course=null;
	private $starttime=0;
	private $newkeycount=0;
	
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
		$this->starttime=time();
    }


  /**
     * Add a family role
     * @param integer group id
     * @param integer course module id
     * @return id of family or false if already added
     */
    public function add_role($familyid,$userid, $role ) {
        global $DB,$USER;

		 //The user id should not be zero here, its difficult to see how that might happen
		 //but it does for some reason when user import messes up in unknown circumstances
		 //so we check for that and other strangeness here
		 if(!$userid || $userid==0 || empty($role)){
		 	return false;
		 }
		 
		 //fetch the users family, we expect they are not in the family, so this is just a check
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

		//We shouldn't arrive here unchecked, but we double check anyway
		 if(!$userid || $userid==0 || empty($role)){
		 	return false;
		 }

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
   
   
   public function get_new_familykey($user){
		$this->newkeycount++;
		$newkey = $user->lastname . '_' . $this->starttime . '_' . $this->newkeycount;
		return $newkey;
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


/**
 * Defines upload file classes for use in the local_family block
 *
 * @package    local_family
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @copyright   2014 poodll.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Validates and processes files for the tutorlink block
 */
class local_family_upload_handler {


    /**
     * The ID of the file uploaded through the form
     *
     * @var string
     */
    private $filename;

    /**
     * local_family configuration
     *
     * @var object
     */
    private $cfg;

    /**
     * Constructor, sets the filename
     *
     * @param string $filename
     */
    public function __construct($filename) {
        $this->filename = $filename;
        $this->cfg = get_config('local_family');
    }

    /**
     * Attempts to open the file
     *
     *  open file using the File API.
     * Return the file handler.
     *
     * @throws local_family_exception if the file can't be opened for reading
     * @global object $USER
     * @return object File handler
     */
    public function open_file() {
        global $USER;
    
		$fs = get_file_storage();
		$context = context_user::instance($USER->id);
		$files = $fs->get_area_files($context->id,
									 'user',
									 'draft',
									 $this->filename,
									 'id DESC',
									 false);
		if (!$files) {
			throw new local_family_exception('cantreadcsv', '', 500);
		}
		$file = reset($files);
		if (!$file = $file->get_content_file_handle()) {
			throw new local_family_exception('cantreadcsv', '', 500);
		}

        return $file;
    }

    /**
     * Checks that the file is valid CSV in the expected format
     *
     * Opens the file, then checks each row contains 3 comma-separated values
     *
     * @see open_file()
     * @throws local_family_exception if there are the wrong number of columns
     * @return Object a set of data with no. of new familes, no. of members added or removed, and error details
     */
    public function doprocess_exportformat($preview=true,$stoponerror=true) {
		global $DB;
	
		$bfm = new local_family_manager();
        $line = 0;
		$ret= new stdClass();
		$ret->createdfamilies = 0;
		$ret->addedusers=0;
		$ret->removedusers=0;
		$ret->previewfamilies= array();
		$ret->messages= array();
		$ret->errors= array();
        $file = $this->open_file();
	
		
        while ($therow = fgets($file)) {
            $line++;
			if(!$therow || trim($therow) ==''){continue;}
			
			//determine what kind of line we are dealing with
			$linetype= LF_UNKNOWN ;
			if(strpos($therow,'=====')!==false){
				$linetype = LF_STARTFINISH;
			}elseif(strpos($therow,'+')===0){
				$linetype = LF_ADD;
			}elseif(strpos($therow,'-')===0){
				$linetype = LF_REMOVE;
			}elseif(strpos($therow,'//')===0){
				$linetype = LF_COMMENT;
			}elseif(strpos($therow,'FAMILYKEY=')===0){
				$linetype = LF_FAMILYKEY;
			}elseif(strpos($therow,'FAMILYPARENT=')===0){
				$linetype = LF_SEARCHFAMILYKEY;
			}
			
			//depending on the linetype, check for the simple errors
			switch($linetype){
				case LF_UNKNOWN:
					$ret->errors[] = get_string('strangerow','local_family', $line);
					break;
				case LF_ADD:
				case LF_REMOVE:
					$csvrow = explode(',',$therow);
					if(count($csvrow) < 3) {
						$ret->errors[] = get_string('toofewcols','local_family', $line);
						if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
					}
					if(count($csvrow) > 3) {
						$ret->errors[] = get_string('toomanycols','local_family', $line);
						if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
					}
					if(trim($csvrow[1])!='child' && trim($csvrow[1])!='parent'){
						$ret->errors[] = get_string('strangerelationship','local_family', $line);
						if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
					}

			}
			
			//process a family key line
			if($linetype==LF_FAMILYKEY){
				$familykey=str_replace('FAMILYKEY=','',trim($therow));
				$family = local_family_fetch_family_by_key($familykey);
				if($family){
					$currentfamily=$family;
					continue;
				}else{
					$ret->errors[] = get_string('nosuchfamily','local_family', $line);
					if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
				}
			}
			
			//Process a search family key line
			if($linetype==LF_SEARCHFAMILYKEY){
				$familyparent=str_replace('FAMILYPARENT=','',trim($therow));
				$family =  local_family_fetch_family_by_username($familyparent);
				if($family){
					$currentfamily=$family;
					continue;
				}else{
					$ret->errors[] = get_string('nosuchfamily','local_family', $line);				
					if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
				}	
			}
			
			//process a new family line
			if($linetype==LF_STARTFINISH){
				$currentfamily=false;
				continue;
			}
			
			//process an add remove line
			if($linetype==LF_ADD || $linetype==LF_REMOVE ){

				$user = $DB->get_record('user',array('username'=>trim($csvrow[2])));
				if(!$user){
					$ret->errors[] = get_string('nosuchuser','local_family', $line);
					if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
				}
				$family = local_family_fetch_family_by_username($user->username);
				$member = $DB->get_record('local_family_members',array('userid'=>$user->id));
				switch($linetype){
				
					case LF_ADD:
						if($family && $currentfamily && $family->id != $currentfamily->id){
							$ret->errors[] = get_string('alreadyindifferentfamily','local_family', $line);
							if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
						}elseif($family && $currentfamily && $family->id == $currentfamily->id){
							$ret->messages[] = get_string('alreadyinfamily','local_family', $line);
							continue;
						}else{
							if(!$preview){
							   if(!$currentfamily){
									$familykey = $bfm->get_new_familykey($user);
									$familynotes ="";
									$familyid =  $bfm->add_family($familykey, $familynotes);
									$currentfamily = local_family_fetch_family_by_key($familykey);
									$ret->createdfamilies++;
							   }else{
									$familyid=$currentfamily->id;
							   }
							  
							  if( $bfm->add_role($familyid,$user->id,trim($csvrow[1]))){
								$ret->addedusers++;
								continue;
							  }else{
								$ret->errors[] = get_string('unabletoassignrole','local_family', $line);
								if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
							 }
							}else{
								 if(!$currentfamily){
									if(array_search($user->id,$ret->previewfamilies)===false){
										$ret->previewfamilies[] = $user->id;
										$ret->createdfamilies++;
									}
								 }
								$ret->addedusers++;
							}
						}
						break;
					case LF_REMOVE:
						if(!$family){
							$ret->errors[] = get_string('notinfamily','local_family', $line);
							if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
						}elseif($family->familykey != $currentfamily->familykey){
							$ret->errors[] = get_string('wrongfamily','local_family', $line);
							if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
						}else{
							if(!$preview){
								if($bfm->delete_role($member->id)){	
									$ret->removedusers++;
									continue;
								}else{
									$ret->errors[] = get_string('unabletoremovemember','local_family', $line);
									if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
								}
							}else{
								$ret->removedusers++;
							}
						}
				}//end of switch
			}//end of if linetype
        }//end of while
        fclose($file);
        return $ret;
    }//end of do process function
	
	
	/**
     * Checks that the file is valid CSV in the expected format
     *
     * Opens the file, then checks each row contains 3 comma-separated values
     *
     * @see open_file()
     * @throws local_family_exception if there are the wrong number of columns
     * @return Object a set of data with no. of new familes, no. of members added or removed, and error details
     */
    public function doprocess_moodleformat($preview=true,$stoponerror=true) {
		global $DB;
	
		$bfm = new local_family_manager();
        $line = 0;
		$ret= new stdClass();
		$ret->createdfamilies = 0;
		$ret->addedusers=0;
		$ret->removedusers=0;
		$ret->previewfamilies= array();
		$ret->errors= array();
		$ret->messages= array();
        $file = $this->open_file();
	
		$username_i=false;
		$role_i =false;
		$parent_i=false;
		$familykey_i=false;
		$colcount = false;

        while ($therow = fgets($file)) {
            //inc our line counter
			$line++;
			//init our family variable
			$currentfamily=false;
			
			//if the line is empty, continue
			if(!$therow || trim($therow) ==''){continue;}
			
			//split our row
			$csvrow = explode(',',trim($therow));
			
			//if this is the first row
			//get the column definitions
			if(!$colcount){
			
				$username_i=array_search('username',$csvrow);
				$role_i=array_search('familyrole',$csvrow);
				$parent_i=array_search('familyparent',$csvrow);
				$familykey_i=array_search('familykey',$csvrow);
				if($username_i===false){$ret->errors[]= get_string('nocol_username','local_family', $line);}
				if($role_i===false){$ret->errors[]= get_string('nocol_familyrole','local_family', $line);}
				if($parent_i===false){$ret->errors[]= get_string('nocol_familyparent','local_family', $line);}
				if($familykey_i===false){$ret->errors[]= get_string('nocol_familykey','local_family', $line);}
				
				//if we have errors, do not proceed any further. It would be pointless
				if(count($ret->errors)){
					$ret->errors[]= get_string('import_cancelled','local_family');
					return $ret;
				}
				
				$colcount = max($username_i,$role_i,$parent_i,$familykey_i)+1;
				continue;
			}

			//make sure the current row has the correct no of columns
			if(count($csvrow) < $colcount) {
				$ret->errors[] = get_string('toofewcols','local_family', $line);
				if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
			}
			if(count($csvrow) > $colcount) {
				$ret->errors[] = get_string('toomanycols','local_family', $line);
				if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
			}
			if(trim($csvrow[$role_i])!='child' && trim($csvrow[$role_i])!='parent'){
				$ret->errors[] = get_string('strangerelationship','local_family', $line);
				if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
			}
			
			//first make sure our user is valid
			$user = $DB->get_record('user',array('username'=>trim($csvrow[$username_i])));
			if(!$user){
				$ret->errors[] = get_string('nosuchuser','local_family', $line);
				if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
			}

			//process the family key columns and search columns
			//first check we have the correct data
			$familykey=trim($csvrow[$familykey_i]);
			$searchkey=trim($csvrow[$parent_i]);
			if(empty($familykey) && empty($searchkey)){
				$ret->errors[] = get_string('nofamilykeyorsearchkey','local_family', $line);
				if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
			}
			if(!empty($familykey) && !empty($searchkey)){
				$ret->errors[] = get_string('bothfamilykeyandsearchkey','local_family', $line);
				if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
			}
			
			//fetch family by specified family key
			if(!empty($familykey)){
				$currentfamily = local_family_fetch_family_by_key($familykey);
				if(!$currentfamily){
					$ret->errors[] = get_string('nosuchfamily','local_family', $line);
					if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
				}
			//or fetch family by username of the parent key(search key)	
			}else{
				$currentfamily =  local_family_fetch_family_by_username($searchkey);
				if($preview && !$currentfamily){
					if(array_search($searchkey,$ret->previewfamilies)!==false){
						//just to pass later logic the actual ID is meaningless
						$currentfamily= new stdClass();
						$currentfamily->id = -1;
					}					
				}
			}
			
			//if we still did not get a family, we will need to add it.
			if(!$currentfamily){
				//first get the parent user to form the family from
				$parentuser = $DB->get_record('user',array('username'=>trim($searchkey)));
				if(!$parentuser){
					$ret->errors[] = get_string('nosuchparentuser','local_family', $line);
					if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
				}
				
				if($preview){
					$ret->previewfamilies[] = $parentuser->username;
					$ret->createdfamilies++;
					//just to pass later logic the actual ID is meaningless
					$currentfamily= new stdClass();
					$currentfamily->id = -1;

				}else{
					$familykey = $bfm->get_new_familykey($parentuser);
					$familynotes ="";
					$currentfamilyid =  $bfm->add_family($familykey, $familynotes);
					$currentfamily = local_family_fetch_family_by_key($familykey);
					if(!$currentfamily){
						$ret->errors[] = get_string('familycreationfailed','local_family', $line);
						if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
					}else{
						$ret->createdfamilies++;
						//now we also need to add our parent to make it real
						if( $bfm->add_role($currentfamily->id,$parentuser->id,'parent')){
							//echo ($currentfamily->id . ":" . $user->username . ":" . trim($csvrow[$role_i]) . "<br />"); 
							$ret->addedusers++;
						}else{
							$ret->errors[] = get_string('unabletoassignrole','local_family', $line);
							if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
						}
					}
				}
			}
		
			//check we are not already in a family
			$existingfamily = local_family_fetch_family_by_username($user->username);	
			if($existingfamily && $existingfamily->id != $currentfamily->id){
				$ret->errors[] = get_string('alreadyindifferentfamily','local_family', $line);
				if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
			}elseif($existingfamily && $existingfamily->id == $currentfamily->id){
				//we probably don't need to report this 'error', in most cases 
				//the parent was simply added when the child was. : Justin 20150328
				//$ret->messages[] = get_string('alreadyinfamily','local_family', $line);
				continue;
			}
			
			//finally lets add our member
			if($preview){
				$ret->addedusers++;			  
			}else{
				if( $bfm->add_role($currentfamily->id,$user->id,trim($csvrow[$role_i]))){
					//echo ($currentfamily->id . ":" . $user->username . ":" . trim($csvrow[$role_i]) . "<br />"); 
					$ret->addedusers++;
					continue;
				  }else{
					$ret->errors[] = get_string('unabletoassignrole','local_family', $line);
					if($stoponerror) {$ret->messages[] = get_string('import_cancelled_line','local_family', $line);return $ret;}else{continue;}
				 }
			}
					
        }//end of while
        fclose($file);
        return $ret;
    }//end of do process moodleformat function
	
	
	
}//end of class

    
/**
 * An exception for reporting errors when processing local_family files
 *
 * Extends the moodle_exception with an http property, to store an HTTP error
 * code for responding to AJAX requests.
 */
class local_family_exception extends moodle_exception {

    /**
     * Stores an HTTP error code
     *
     * @var int
     */
    public $http;

    /**
     * Constructor, creates the exeption from a string identifier, string
     * parameter and HTTP error code.
     *
     * @param string $errorcode
     * @param string $a
     * @param int $http
     */
    public function __construct($errorcode, $a, $http) {
        parent::__construct($errorcode, 'local_family', '', $a);
        $this->http = $http;
    }
}
