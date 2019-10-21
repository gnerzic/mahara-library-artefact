<?php

/**
 * this json file mimic the index.php file.
 * @package    mahara
 * @subpackage artefact-library
 * @author     Guillaume Nerzic
 * @copyright  Guillaume Nerzic, 2019
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'library');

$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);

// get the publications in this library
$ratings = ArtefactTypePublication::get_ratings(param_integer('publication'), $offset, $limit);
//format the library into an HTLM template friendly format (note: $library parameter is passed by reference)
ArtefactTypePublication::build_ratings_html($ratings);


json_reply(false, (object) array('message' => false, 'data' => $ratings));

//endof ratingslist.json.php