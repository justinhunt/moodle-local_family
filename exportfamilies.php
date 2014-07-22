<?php   // $Id: exportfile.php,v 1.8 2007/08/17 12:49:31 skodak Exp $

	global $SESSION, $DB;	
	
	require_once("../../config.php");
	require_once($CFG->dirroot.'/local/family/lib.php');
    require_once("../../lib/filelib.php");


    require_login();
    
	//Set up page
	$context = context_user::instance($USER->id);


   // require_capability('block/quizletquiz:export', $context);

	
	//perform the export
	$filename = "familiesexport.txt";
	$lineend = "\n";
	$content="";
	$families = $DB->get_records('local_family');
	foreach($families as $family){
		$content .="==========================" . $lineend;
		$content .="FAMILYKEY=" . $family->familykey  . $lineend;
		$familymembers = local_family_fetch_users_by_family($family->id);
		if(!$familymembers){continue;}
		foreach($familymembers as $member){
			$content.="+,". $member->role ."," . $member->username  . $lineend;
		}
	}
	send_file($content, $filename, 0, 0, true, true); 
	return;