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
 * family 
 *
 * @package    local_family
 * @copyright  Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
$ADMIN->add('root', new admin_category('local_family', get_string('family', 'local_family')));

$ADMIN->add('local_family', new admin_externalpage('managefamilies', get_string('managefamilies', 'local_family'),
        $CFG->wwwroot."/local/family/view.php?action=listall",
        'moodle/site:config'));

$ADMIN->add('local_family', new admin_externalpage('searchfamilies', get_string('searchfamilies', 'local_family'),
        $CFG->wwwroot."/local/family/view.php?action=searchfamily",
        'moodle/site:config'));

$ADMIN->add('local_family', new admin_externalpage('addfamily', get_string('addfamily', 'local_family'),
        $CFG->wwwroot."/local/family/view.php?action=addfamily",
        'moodle/site:config'));

