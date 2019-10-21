<?php
/**
 * This file defines the literature review plugin and grouptype.
 * The literature grouptype does not introduce any new functionality, 
 * it is a market to determine if the library plugin (see artefact/library) 
 * is available to the group.
 * @package    mahara
 * @subpackage grouptype-literaturereview
 * @author     Guillaume Nerzic
 * @copyright  Guillaume Nerzic, 2019
 *
 */

defined('INTERNAL') || die();

/**
 * PluginGrouptypeLiteratureReview
 */
class PluginGrouptypeLiteratureReview extends PluginGrouptype {

    /**
     * post installation tells the parent class to install this new grouptype
     * @param type $prevversion
     */
    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            parent::installgrouptype('GroupTypeLiteratureReview');
        }//endif
    }//endof static function postinst(.)

}//endof class PluginGrouptypeLiteratureReview

/**
 * configuration of the literature review group, all methods are inherited from
 * GroupTypeCourse, since the only interesting feature of this grouptype is its type.
 */
class GroupTypeLiteratureReview extends GroupTypeCourse {


}//endof class GroupTypeLiteratureReview
