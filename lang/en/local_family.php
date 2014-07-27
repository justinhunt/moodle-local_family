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
 * Strings for component 'local_family', language 'en'
 *
 * @package   local_family
 * @copyright Daniel Neis <danielneis@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['family:managefamiliies'] = 'Manage Families';
$string['pluginname'] = 'Family';
$string['family'] = 'Family';
$string['listview'] = 'family Assignments';
$string['familylist'] = 'Family: {$a}';
$string['listfamilies'] = 'List families';
$string['addfamily'] = 'Add a family';
$string['familyactivity'] = 'family Activity';
$string['addedsuccessfully'] = 'The family was added successfully';
$string['failedtoaddfamily'] = 'Failed to add family.';
$string['failedtoaddfamilymember'] = 'Failed to add family member. Possibly already a member of another family.';
$string['failedtogetmemberinfo'] = 'Failed to get family member information.';
$string['doaddfamily_label'] = 'Insert';
$string['doeditfamily_label'] = 'Update';
$string['dodeletefamily_label'] = 'Delete';
$string['doaddrole_label'] = 'Insert';
$string['doeditrole_label'] = 'Update';
$string['dodeleterole_label'] = 'Delete';
$string['deletefamilylink'] = 'Delete';
$string['editfamilylink'] = 'Edit';
$string['addfamilyheading'] = 'Add family';
$string['deletefamilyheading'] = 'Delete family: {$a}';
$string['editfamilyheading'] = 'Edit Family: {$a}';
$string['addroleheading'] = 'Add family member to: {$a}';
$string['deleteroleheading'] = 'Delete family member from: {$a}';
$string['deletedsuccessfully'] = 'The family was deleted successfully';
$string['failedtodelete'] = 'Failed to delete family. Sorry.';
$string['updatedsuccessfully'] = 'The family was updated successfully';
$string['failedtoupdate'] = 'Failed to update family. Is the family key unique?';
$string['actions'] = 'Actions';
$string['activitytitle'] = 'Activity Title';
$string['nofamilies'] = 'No Families Found';
$string['invalidfamilyid'] = 'Invalid Member ID specified';
$string['managefamilies'] = 'Manage family Activities';
$string['inadequatepermissions'] = 'Insufficient Permissions to Access this Page';
$string['managefamilies'] = 'Manage Families';
$string['picture'] = 'Picture';
$string['fullname'] = 'Full Name';
$string['messagelink'] = 'message';
$string['editlink'] = 'edit';
$string['deletememberlink'] = 'delete';
$string['nochildren'] = 'Family has no children';
$string['noparents'] = 'Family has no parents';
$string['addparenttofamily'] = 'Add parent to family';
$string['addchildtofamily'] = 'Add child to family';
$string['invalidfamilyuserid'] = 'Invalid family or user id';
$string['familynotes'] = 'Family Notes';
$string['username'] = 'User Name';
$string['familykey'] = 'Family Key';
$string['potentialmembers'] = 'Potential Members';
$string['listall'] = 'List All Families';
$string['firstparentname'] = 'Parent Name';
$string['childrennames'] = 'Children Names';
$string['viewlink'] = 'view';
$string['addedmembersuccessfully'] = 'The family member was added successfully';
$string['deletedmembersuccessfully'] = 'The family member was deleted successfully';
$string['failedtodeletemember'] = 'Failed to delete family member. Sorry.';
$string['showsinglefamily'] = 'Showing Family: {$a}';
$string['canceledbyuser'] = 'Canceled by user';
$string['dosearch'] = 'Find the family of the selected user';
$string['dosearch_label'] = 'Find the family of the selected user';
$string['searchfamilies'] = 'Search families';
$string['undefined'] = '---';
$string['loginas'] = 'Login As';
$string['label_loginas'] = 'Login as Child';
$string['loginasheading'] = 'Login as user: {$a}';
$string['loginaserror'] = 'Unable to Login As Child';
$string['invalidparentid'] = 'Parent ID did not match family of Child';
$string['loginaswarning'] = 'You are about to login as another member of your family. <br />Please do not submit course activities on their behalf.';
$string['uploadfile'] = 'Upload a family defining a batch of family relationships';
$string['musthavefile'] = 'You must upload a file';
$string['uploadfileheading'] = 'Batch Upload Family Definitions';
$string['uploadfamilies'] = 'Batch Upload Families';
$string['backtouploadfamilies'] = 'Back to Batch Upload Families';

$string['nopermission'] = 'You do not have permission to upload family relationships.';
$string['noroles'] = 'There are currently no roles assignable in User contexts. You must create such a role
before this block can be fully configured, otherwise you will not be able to use it!';
$string['reladded'] = '{$a->parent} sucessfully assigned to {$a->child}';
$string['relalreadyexists'] = '{$a->parent} already assigned to {$a->child}';
$string['reladderror'] = 'Error assigning {$a->parent} to {$a->child}';
$string['reldeleted'] = '{$a->parent} unassigned from {$a->child}';
$string['exportfamilies'] = 'Export families';
$string['toofewcols'] = 'Line {$a}: line has too few columns.';
$string['toomanycols'] = 'Line {$a}: line has too many columns.';
$string['parentnotfound'] = 'Line {$a}: Parent not found';
$string['childnotfound'] = 'Line {$a}: Child not found';
$string['wrongfamily'] = 'Line {$a}: Wrong family ';
$string['unabletoassignrole'] = 'Line {$a}: unable to Assign Role';
$string['alreadyindifferentfamily'] = 'Line {$a}: Already in another family';
$string['alreadyinfamily'] = 'Line {$a} Already in this family';
$string['nosuchuser'] = 'Line {$a}: No such user';
$string['nosuchfamily'] = 'Line {$a}: No such family';
$string['strangeuser'] = 'Line {$a}: Strange user';
$string['strangerow'] = 'Line {$a}: Strange row';
$string['strangerelationship'] = 'Line {$a}: Strange relationship ';
$string['notinfamily'] = 'Line {$a}: Not in family';
$string['wrongfamily'] = 'Line {$a}: Wrong family';
$string['unabletoremovemember'] = 'Line {$a}: unable to remove member from family';
$string['uploadfileresults'] = 'Results of File Upload';
$string['previewuploadfileresults'] = 'PREVIEW of File Upload';
$string['errorcount'] = 'Error Count: ';
$string['familiescreated'] = 'Families Created: ';
$string['familycreationfailed'] = 'Family Creation Failed: ';
$string['membersadded'] = 'Family Members Added: ';
$string['membersremoved'] = 'Family Members Removed: ';
$string['previewerrorcount'] = 'Preview Error Count: ';
$string['previewfamiliescreated'] = 'Families to be Created: ';
$string['previewmembersadded'] = 'Family Members to be Added: ';
$string['previewmembersremoved'] = 'Family Members to be Removed: ';
$string['importmode'] = 'Import Mode: ';
$string['previewmode'] = 'Preview Mode (no data is altered)';
$string['realmode'] = 'Execute Mode (no turning back)';
$string['stoponerror'] = 'Stop on error and return: ';
$string['importformat'] = 'Import Format';
$string['moodleformat'] = 'Moodle User Import Format(+ a few cols)';
$string['sameasexportformat'] = 'Same as Family Export Format';

$string['nocol_username'] = 'No USERNAME column on line: {$a}';
$string['nocol_familyrole'] = 'No FAMILYROLE column on line: {$a}';
$string['nocol_familyparent'] = 'No FAMILYPARENT column on line: {$a}';
$string['nocol_familykey'] = 'No FAMILYKEY column on line: {$a}';
$string['nofamilykeyorsearchkey'] = 'Neither FAMILYKEY column nor PARENTUSER specified on line: {$a}';
$string['bothfamilykeyandsearchkey'] = 'Both FAMILYKEY column and PARENTUSER specified on line: {$a}. Please just use either but not both.';
$string['nosuchparentuser'] = 'The parent user specified does not exist line: {$a}';
$string['import_cancelled'] = 'Family import cancelled due to incorrect/missing column definitions';
$string['import_cancelled_line'] = 'Family import cancelled at line {$a} due to errors';