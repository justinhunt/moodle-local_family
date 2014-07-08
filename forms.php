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
 * Forms for family Block
 *
 * @package    local_family
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Justin Hunt  http://poodll.com
 */

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/user/selector/lib.php');

class local_family_add_role_form extends moodleform {
	
	protected $action = 'addrole';
	
    public function definition() {
        global $CFG, $USER, $OUTPUT;
        $strrequired = get_string('required');
        $mform = & $this->_form;

	//	$familyid = $this->_customdata['familyid'];
	//	$role = $this->_customdata['role'];

        //add the course id (of the context)
       // $mform->addElement('text', 'userid', get_string('username','local_family'));
       // $mform->setType('userid', PARAM_INT);
       
       //if admin, display a selectors so we can update contributor, site and sitecourseid
		$selector = new local_family_user_selector('userid', array());
		$selectorhtml = get_string('username', 'local_family');
		$selectorhtml .= $selector->display(true);
		$mform->addElement('static','userselector','',$selectorhtml);
		
		$mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
        
		$mform->addElement('hidden', 'familyid');
        $mform->setType('familyid', PARAM_INT);
		
		$mform->addElement('hidden', 'role');
        $mform->setType('role', PARAM_TEXT);
		
		
		
		$mform->addElement('hidden', 'action', 'do' . $this->action);
        $mform->setType('action', PARAM_TEXT);
		
		 $this->add_action_buttons(true,get_string('do' . $this->action . '_label', 'local_family'));

    }
	
	/*

    function validation($data, $files) {
        global $CFG;

        $errors = array();

        if (empty($this->_form->_submitValues['startdate'])) {
            $errors['startdate'] = get_string('nostartdate', 'local_family');
        }
		if (empty($this->_form->_submitValues['cmid'])) {
            $errors['cmid'] = get_string('nocmid', 'local_family');
        }
		if (empty($this->_form->_submitValues['groupid'])) {
            $errors['groupid'] = get_string('nogroupid', 'local_family');
        }

        return $errors;
    }
*/
}
	
class local_family_edit_role_form extends local_family_add_role_form {

	protected $action = 'editrole';

}//end of class
	
class local_family_delete_role_form extends moodleform {

    public function definition() {
        global $CFG, $USER, $OUTPUT;
        $strrequired = get_string('required');
        $mform = & $this->_form;

		$mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        
		$mform->addElement('hidden', 'familyid', 0);
        $mform->setType('familyid', PARAM_INT);

		
		$mform->addElement('static', 'familykey', get_string('familykey','local_family'));
		$mform->addElement('static', 'fullname', get_string('fullname','local_family'));
		
		$mform->addElement('hidden', 'action', 'dodeleterole');
        $mform->setType('action', PARAM_TEXT);

		 $this->add_action_buttons(true,get_string('dodeleterole_label', 'local_family'));
	}

}

class local_family_add_family_form extends moodleform {

	protected $action = 'addfamily';

    public function definition() {
        global $CFG, $USER, $OUTPUT;
        $strrequired = get_string('required');
        $mform = & $this->_form;

        //add the course id (of the context)
        $mform->addElement('text', 'familykey', get_string('familykey','local_family'));
        $mform->setType('familykey', PARAM_TEXT);
		
        //add the course id (of the context)
        $mform->addElement('text', 'familynotes', get_string('familynotes','local_family'));
        $mform->setType('familynotes', PARAM_TEXT);
        
		$mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
		$mform->addElement('hidden', 'action', 'do' . $this->action);
        $mform->setType('action', PARAM_TEXT);
        $this->add_action_buttons(true,get_string('do' . $this->action . '_label', 'local_family'));
	}
	
}
	
class local_family_edit_family_form extends local_family_add_family_form {

		protected $action = 'editfamily';

}//end of class
	
class local_family_delete_family_form extends moodleform {

		public function definition() {
			global $CFG, $USER, $OUTPUT, $COURSE;
			$strrequired = get_string('required');
			$mform = & $this->_form;

		
			$mform->addElement('hidden', 'id');
			$mform->setType('id', PARAM_INT);
		
		
			$mform->addElement('static', 'familykey', get_string('familykey','local_family'));
			$mform->addElement('static', 'familynotes', get_string('familynotes','local_family'));
		
			$mform->addElement('hidden', 'action', 'dodeletefamily');
			$mform->setType('action', PARAM_TEXT);

			$this->add_action_buttons(true,get_string('dodeletefamily_label', 'local_family'));

		}

}

class local_family_search_family_form extends moodleform {
	
    public function definition() {
        global $CFG, $USER, $OUTPUT;
        $strrequired = get_string('required');
        $mform = & $this->_form;
       
       //if admin, display a selectors so we can update contributor, site and sitecourseid
		$selector = new local_family_user_selector('userid', array());
		$selectorhtml = get_string('username', 'local_family');
		$selectorhtml .= $selector->display(true);
		$mform->addElement('static','userselector','',$selectorhtml);
		
		$mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
		
		$mform->addElement('hidden', 'action', 'dosearchfamily');
        $mform->setType('action', PARAM_TEXT);
		
		 $this->add_action_buttons(true,get_string('dosearch_label', 'local_family'));

    }
}

/*
 * This class displays either all the Moodle users allowed to use a service,
 * either all the other Moodle users.
 */
class local_family_user_selector extends user_selector_base {

   /** @var boolean Whether the conrol should allow selection of many users, or just one. */
    protected $multiselect = false;
    /** @var int The height this control should have, in rows. */
    protected $rows = 5;

    public function __construct($name, $options) {
        parent::__construct($name, $options);
    }
    
      /**
     * Find allowed or not allowed users of a service (depend of $this->displayallowedusers)
     * @global object $DB
     * @param <type> $search
     * @return array
     */
    public function find_users($search) {
        global $DB;
        //by default wherecondition retrieves all users except the deleted, not
        //confirmed and guest
        list($wherecondition, $params) = $this->search_sql($search, 'u');


        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

            $sql = " FROM {user} u
                 WHERE $wherecondition
                       AND u.deleted = 0 AND NOT (u.auth='webservice') ";
 
       

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }


    
        $groupname = get_string('potentialmembers', 'local_family');
      

        return array($groupname => $availableusers);
    }
    
     /**
     * This options are automatically used by the AJAX search
     * @global object $CFG
     * @return object options pass to the constructor when AJAX search call a new selector
     */
    protected function get_options() {
        global $CFG;
        $options = parent::get_options();
        $options['file'] = '/local/family/forms.php'; //need to be set, otherwise
                                                        // the /user/selector/search.php
                                                        //will fail to find this user_selector class
        return $options;
    }
}