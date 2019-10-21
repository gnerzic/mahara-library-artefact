<?php

/**
 * The page to enter meta data about publications.
 * @package    mahara
 * @subpackage artefact-library
 * @author     Guillaume Nerzic
 * @copyright  Guillaume Nerzic, 2019
 *
 */

/* boilerplate code */
define('INTERNAL', 1);
define('MENUITEM_SUBPAGE', 'library');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'library');
define('SECTION_PAGE', 'library');

// load mahara core and required plugins
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'library');
safe_require('artefact', 'file');

//Check to ensure the plugin is enable.
if (!PluginArtefactLibrary::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', 
                                    get_string('library','artefact.library')));
}//endif

/* end of boilerpate code */

//get publication object based on ID
$publication = new ArtefactTypePublication(param_integer('id'));

//set the title of this page.
define('TITLE', get_string('Library','artefact.library'));
define('GROUP', $publication->get('group'));
define('SUBSECTIONHEADING', get_string('managethispublication', 'artefact.library', $publication->get('title')));



//get the user's role withint the group and throw an exception if this is a 'member'
$role = group_user_access($publication->get('group'), $USER->get('id'));
if ($role == 'member') {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}//endif 

// get HTML widget for this form.
$publicationsform = ArtefactTypePublication::get_publication_management_form($publication);
// obtain file id for the publication file (publications only have one attachment)
$fileid = $publication->attachment_id_list()[0];
// get file object from ID
$file = new ArtefactTypeFile($fileid);
// get name of the file.
$filename = $file->get('title');

$smarty = smarty();
$smarty->assign('filename', $filename);
$smarty->assign('fileid', $fileid);
$smarty->assign('publicationform', $publicationsform);
$smarty->display('artefact:library:managepublication.tpl');//

//endof PHP page publication.php