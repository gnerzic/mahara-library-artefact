<?php

/**
 * This page confirm that a review should be deleted.
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

//set the title of this page.
define('TITLE', get_string('Removereview','artefact.library'));
//retrieve the group's id depending on the URL's available parameters 
$group = ArtefactTypeReview::get_review_group();
define('GROUP', ArtefactTypeReview::get_review_group());
define('SUBSECTIONHEADING', get_string('Removereview', 'artefact.library'));

//this page should only be invoke if the id parameter is set (an execption will be thrown otherwise)
$todelete = new ArtefactTypeReview(param_integer('id'));
$associated_pub = new ArtefactTypePublication($todelete->get('parent'));

if (!$USER->can_edit_artefact($todelete)) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}//endif

$returnurl = get_config('wwwroot').'/artefact/library/index.php?group='.$group;

// set the delete form inline to this page (usually this is done through a
// static function of the plugintype class, but this is a very simple form.
$deleteform = array(
    'name' => 'removereviewform',
    'class' => 'form-delete',
    'plugintype' => 'artefact',
    'pluginname' => 'library',
    'renderer' => 'div',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'class' => 'btn-secondary',
            'value' => array(get_string('removereview','artefact.library'), get_string('cancel')),
            'goto' => $returnurl,
        ),
    )
);
$form = pieform($deleteform);

$smarty = smarty();
$smarty->assign('form', $form);
$smarty->assign('SUBSECTIONHEADING', get_string('removethisreview','artefact.library',$associated_pub->get('title')));
$smarty->assign('message', get_string('removereviewconfirm','artefact.library'));
$smarty->display('artefact:library:delete.tpl');

//endof PHP page removereview.php

// this is a call back function for the form specified within this form.
function removereviewform_submit(Pieform $form, $values) {
    global $SESSION, $todelete, $group;

    $todelete->delete();
    $SESSION->add_ok_msg(get_string('reviewremovedsuccessfully', 'artefact.library'));

    $returnurl = get_config('wwwroot').'/artefact/library/index.php?group='.$group;

    redirect($returnurl);
}//endof function removereviewform_submit(.,.)