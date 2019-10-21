<?php

/**
 * Page use to manage the publications within the library.
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

// load mahara core
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'library');

//Check to ensure the plugin is enable.
if (!PluginArtefactLibrary::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', 
                                    get_string('library','artefact.library')));
}//endif

/* end of boilerpate code */

//get group id
$group_id = param_integer('group');

//set the title of this page.
define('TITLE', get_string('managelibrary','artefact.library'));
define('GROUP', $group_id);
define('SUBSECTIONHEADING', get_string('Library', 'artefact.library'));

//get the user's role within the group and throw an exception if this is 'member'
$role = group_user_access($group_id, $USER->get('id'));
if ($role == 'member') {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}//endif 

// get the html form containing the widgets to manage the library.
$publicationsform = ArtefactTypePublication::get_publications_form();

$smarty = smarty();
$smarty->assign('publicationform', $publicationsform);
$smarty->display('artefact:library:manage.tpl');//

//endof PHP page managelibrary.php
