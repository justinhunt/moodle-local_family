<?php
// Allows a parent to login as their child (in stealth mode). []/loginaschild.php?id=courseid&childid=useridofchild

require_once('../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/local/family/lib.php');
global $USER;

$courseid       = optional_param('courseid', SITEID, PARAM_INT);   // course id
$redirect = optional_param('redirect', 0, PARAM_BOOL);
// Try log in as this user.
$childid = required_param('childid', PARAM_INT);

$url = new moodle_url('/local/family/loginaschild.php', array('courseid'=>$courseid, 'redirect'=>$redirect, 'childid'=>$childid));
$PAGE->set_url($url);

// Reset user back to their real self if needed, for security reasons you need to log out and log in again.
if (\core\session\manager::is_loggedinas()) {
    require_sesskey();
    require_logout();

    // We can not set wanted URL here because the session is closed.
    redirect(new moodle_url($url, array('redirect'=>1)));
}

require_sesskey();

if ($redirect) {
    if ($courseid and $courseid != SITEID) {
        $SESSION->wantsurl = "$CFG->wwwroot/course/view.php?id=".$courseid;
    } else {
        $SESSION->wantsurl = "$CFG->wwwroot/";
    }

 //   redirect(get_login_url());
}


require_login();
//require_login($course);

//**JUSTIN ** replace  core code, relative to child/parent
//require_capability('moodle/user:loginas', $coursecontext);
$children = local_family_fetch_child_users($USER->id);
$parents = local_family_fetch_parent_users($childid);
if(!$children){return;}
$trueparent=false;
foreach($children as $child){
	if($childid == $child->id){
		foreach($parents as $parent){
			if($parent->id ==$USER->id){
				$trueparent=true;
				break;
			}
		}
		if($trueparent){break;}
	}
}
if(!$trueparent){return;}

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);

// User must be logged in.
$coursecontext = context_course::instance($course->id);

// Login as this user and return to course home page.
\core\session\manager::loginas($childid, $coursecontext);
$newfullname = fullname($USER, true);

$strloginas    = get_string('loginas');
$strloggedinas = get_string('loggedinas', '', $newfullname);

$PAGE->set_title($strloggedinas);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strloggedinas);
notice($strloggedinas, "$CFG->wwwroot/course/view.php?id=$course->id");