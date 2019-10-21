<?php

/**
 * This page shows a publication's reviews and ratings.
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
define('TITLE', get_string('Ratings','artefact.library'));
//retrieve the group's id depending on the URL's available parameters 
$group = ArtefactTypeReview::get_review_group();
// get group id from available parameters
define('GROUP', ArtefactTypeReview::get_review_group());

// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

//set rating file to 7 == all ratings if not set.
// the reason for the +1: ratings in the system start from 1, where 1 == no rating.
$rating_filter = param_integer('rating', 6) + 1;

//create publication, so that title can be used in page subheading.
$publication_id = param_integer('publication');
$publication = new ArtefactTypePublication($publication_id);
define('SUBSECTIONHEADING', get_string('Review', 'artefact.library').'\''.$publication->get('title').'\'');

// get the publications in this publication
$ratings = ArtefactTypePublication::get_ratings($publication_id, $offset, $limit, $rating_filter);
//format the ratings into an HTLM template friendly format (note: $ratings parameter is passed by reference)
ArtefactTypePublication::build_ratings_html($ratings);

// get similar items and html presentation elements
$similar_items = ArtefactTypePublication::get_similar_items($publication_id);
// (note: $similar_items parameter is passed by reference)
ArtefactTypePublication::build_similar_items_html($similar_items);

/* boilerplate code */
$js = <<< EOF
jQuery(function () {
    {$ratings['pagination_js']}
});
EOF;
/* end of boilerplate code */
    
//Start smarty formatting for presentation
$smarty = smarty(array('paginator'));
// set the library data into the web form.
$smarty->assign('ratings', $ratings);
// set the recommendations into the web form.
$smarty->assign('similar_items', $similar_items);
// set the publication id.
$smarty->assign('publication', $publication_id);
// the reason for the -1: ratings in the system start from 1, where 1 == no rating.
$smarty->assign('rating', $rating_filter-1);
//$smarty->assign('PAGEHEADING', $publication->get('title'));
$smarty->assign('INLINEJAVASCRIPT', $js);
//display through a web template (so that header and footer are automatically included.
$smarty->display('artefact:library:rating.tpl');

//endof PHP page rating.php