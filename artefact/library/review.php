<?php

/**
 * The page shows the review form.
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

//set the title of this page.
define('TITLE', get_string('Review','artefact.library'));
//retrieve the group's id depending on the URL's available parameters 
define('GROUP', ArtefactTypeReview::get_review_group());

//get form to configure a new feedback
$review = null;
$parent = null;
// given the available parameters, create review and publication objects.
if(param_exists('id')) {
    $review = new ArtefactTypeReview(param_integer('id'));
    $parent = new ArtefactTypePublication($review->get('parent'));
} else {
    $parent = new ArtefactTypePublication(param_integer('publication'));
}//endif
//get HTML for to populate review
$form = ArtefactTypeReview::get_review_form($review);

//set the page subheadings.
$publication_title = $parent->get('title');
define('SUBSECTIONHEADING', get_string('Rating', 'artefact.library').'\''.$publication_title.'\'');

// there is only one attachment per publication
$publication_file_id = $parent->attachment_id_list()[0];
$publication_file = new ArtefactTypeFile($publication_file_id);
$publication_file_name = $publication_file->get('title');

$smarty = smarty(array('paginator'));
$smarty->assign('form', $form);
$smarty->assign('publicationfileid', $publication_file_id);
$smarty->assign('publicationfilename', $publication_file_name);
$smarty->display('artefact:library:review.tpl');

//endof PHP page review.php