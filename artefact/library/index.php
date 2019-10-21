<?php

/**
 * The home page of the library.
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

// get group id
$group_id = param_integer('group');

//set the title of this page.
define('TITLE', get_string('Library','artefact.library'));
define('GROUP', $group_id);
define('SUBSECTIONHEADING', get_string('Library', 'artefact.library'));

// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

// get the publications in this library and html presentation elements.
$library = ArtefactTypePublication::get_publications($group_id, $offset, $limit);
// (note: $library parameter is passed by reference)
ArtefactTypePublication::build_library_html($library);

// get personalised recommendations and html presentation elements
$recommendations = ArtefactTypePublication::get_recommendations($group_id);
// (note: $recommendations parameter is passed by reference)
ArtefactTypePublication::build_recommendations_html($recommendations);

$alt_pub = ArtefactTypePublication::get_alternative_recommendation($group_id);

/* boilerplate code */
$js = <<< EOF
jQuery(function () {
    {$library['pagination_js']}
});
EOF;
/* end of boilerpate code */
    
//Start smarty formatting for presentation
$smarty = smarty(array('paginator'));
// set the library data into the web form.
$smarty->assign('library', $library);
// set the recommendations into the web form.
$smarty->assign('recommendations', $recommendations);
// set the alternative suggestion
$smarty->assign('alternative', $alt_pub);
$smarty->assign('alternative_fileid', $alt_pub?$alt_pub->attachment_id_list()[0]:null);
// if there is not data then set a holding message 
$smarty->assign('strnolibrary', get_string('nopublicationsyet', 'artefact.library'));
// if there is recommendation data set a holding message
$smarty->assign('strnorecommendations', get_string('norecommendations', 'artefact.library'));
$smarty->assign('strnoalternaterecommendations', get_string('noalternative', 'artefact.library'));
$smarty->assign('INLINEJAVASCRIPT', $js);
//display through a web template (so that header and footer are automatically included.
$smarty->display('artefact:library:library.tpl');

//endof PHP page index.php