<?php

/**
 * This is a standard code file for all Mahara plugins, it declared core components of the plugin
 *  - the artefact plugin which provides Mahara meta information about the plugin
 *  - the plugin type(s) which are defined classes which will be instantiated and manipulated by the user.
 * @package    mahara
 * @subpackage artefact-library
 * @author     Guillaume Nerzic
 * @copyright  Guillaume Nerzic, 2019
 *
 */

defined('INTERNAL') || die();

/**
 * Will not need to implement this function for the project.
 * @todo implement thins function is the library artefact is release to the Mahara community.
 * @param type $oldversion
 * @return boolean
 */
function xmldb_artefact_library_upgrade($oldversion=0) {
    return true;
}//endof function xmldb_artefact_library_upgrade(.)
