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
 * family block caps.
 *
 * @package    local_family
 * @copyright  Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/family/locallib.php');

class local_family extends block_list {

    function init() {
        $this->title = get_string('pluginname', 'local_family');
    }

    function get_content() {
        global $CFG, $OUTPUT, $COURSE,$USER;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        // user/index.php expect course context, so get one if page has module context.
        $currentcontext = $this->page->context->get_course_context(false);
		$course = $this->page->course;

		//If they don't have permission don't show it
		if(has_capability('block/family:managefamilies', $currentcontext) ){
			$url = new moodle_url('/blocks/family/view.php', array('courseid'=>$course->id,'action'=>'list','familyid'=>1));
			$this->content->items[] = html_writer::link($url, get_string('managefamilies','local_family'));
			$url = new moodle_url('/blocks/family/view.php', array('courseid'=>$course->id,'action'=>'addfamily'));
			$this->content->items[] = html_writer::link($url, get_string('addfamily','local_family'));
		 }
		

		$this->content->footer = '';
		return $this->content;
		
    }

    // my moodle can only have SITEID and it's redundant here, so take it away
    public function applicable_formats() {
        return array('all' => false,
                     'site' => true,
                     'site-index' => true,
                     'course-view' => true, 
                     'course-view-social' => false,
                     'mod' => true, 
                     'mod-quiz' => false);
    }

    public function instance_allow_multiple() {
          return true;
    }

    function has_config() {return true;}

    public function cron() {
            mtrace( "Hey, my cron script is running" );
             
                 // do something
                  
                      return true;
    }
}
