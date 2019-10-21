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

// this files should not be requested directly by a browser, so should only be accessed 'INTERNAL' or die.
defined('INTERNAL') || die();

/**
 * the plugin artefact class defines the types of artefacts being used in this plugin
 */
class PluginArtefactLibrary extends PluginArtefact {
    /**
     * method returns this plugin's artefact types
     * @return array of strings 'publication' and 'review'.
     */
    public static function get_artefact_types(): array {
        return array(
            'publication',
            'review',
        );
    }// endof static method get_artefact_types()

    /**
     * This method returns this artefact's associated blocktype plugins
     * @return empty array since this artefact doesn't have an associated blocktype
     */
    public static function get_block_types(): array {
        return array();     // no blocktypes returned
    }//endof static method get_block_types()
   
    /**
     * This method returns this artefact's plugin name
     * @return string name of the plugin: 'library'
     */
    public static function get_plugin_name(): string {
        return 'library';
    }//endof static method get_plugin_name()

    /**
     * This method is overridden so that this artefact is available for groups. 
     * It checks that the group is a literature review. 
     * @param type $groupid
     * @param type $role
     */
    public static function group_tabs($groupid, $role){
        $group = get_group_by_id($groupid);
        // only make the artefact available is the for literature review groups.
        if($role != false && $group->grouptype == 'literaturereview') {
            return array(
                'library' => array(
                    'path' => 'groups/library',
                    'url' => 'artefact/library/index.php?group='.$groupid,
                    'title' => get_string('Library', 'artefact.library'),
                    'weight' => 99,
                ),
            );
        } else {
            return array();
        } //endif
    }//endof static method group_tabs(.,.)

    /**
     * 
     * @return boolean true is plugin is active
     */
    public static function is_active() {
        return get_field('artefact_installed', 'active', 'name', 'library');
    }//endof static method is_active()
    
}// endof class PluginArtefactLibrary


/**
 * The artefact type publication encapsulates a item in the library.
 */
class ArtefactTypePublication extends ArtefactType {
    
   /**
     * Returns this artefact's icon (abstract in super class)
     * @param type $options
     * @return string
     */
    public static function get_icon($options = null): string {
       return false; 
    }//endof static method get_icon(.)

    /** 
     * returns the default link for this artefact, can be local sensitive
     * (hence the array
     * @param type $id
     */
    public static function get_links($id) {
        return array (
            '_default' => get_config('wwwroot') . 'artefact/library/publication.php?id='.$id,
        );
    }//endof static method get_links(.)

    /**
     * @return boolean
     */
    public static function is_singular() {
        return false;
    }//endof static method is_singular()
    
    /**
     * Overridden to show this artefact can have attachments
     * @return boolean
     */
     public function can_have_attachments() {
        return true;
    }//endof method can_have_attachments()
    
    /**
     * Overridden so that this public type can be configured.
     * @return boolean
     */
    public static function has_config() {
        return true;
    }//endof method has_config()

    public static function get_config_options() {
        require_once("rs/lib.php");
        
        $rs_class = get_config_plugin('artefact', 'library', 'recommendersystemclass');
        $rs_class = empty($rs_class) ? 'EclideanDistanceRecommenderSystem' : $rs_class;
        
        $rs_available_classes = array();
        foreach(get_declared_classes() as $class_name) {
            if(in_array("IRecommenderSystem", class_implements($class_name))) {
                $refl = new ReflectionClass($class_name);
                if(!$refl->isAbstract()) {
                    $rs_available_classes[$class_name] = $class_name;
                }
            }
        }
        
        $elements =  array(
            'recommendersystemclass' => array(
                'type' => 'select',
                'title' => get_string('recommendersystemtype', 'artefact.library'),
                'defaultvalue' => $rs_class,
                'options' => $rs_available_classes,
            ),
        );
        return array(
            'elements' => $elements,
        );
    }    
    
    /**
     * Call back function from config screen
     * @param Pieform $form
     * @param type $values
     */
    public static function save_config_options(Pieform $form, $values) {
        $valid = array('recommendersystemclass');
        foreach ($valid as $settingname) {
            set_config_plugin('artefact', 'library', $settingname, $values[$settingname]);
        }
    }
    
    
    
    /**
     * override delete method from superclass to ensure the detach function is called.
     * @return type
     */
    public function delete() {
        if (empty($this->id)) {
            return;
        }

        db_begin();
        $this->detach(); // Detach all file attachments
        parent::delete();
        db_commit();
    }//endof delete()
    
    
    public function get_publication_rating() {
        $children = $this->get_children_instances();
        $rating = -1;
        if($children) {
            foreach($children as $child) {
                if($rating == -1) {
                    $rating = (int)($child->get('rating'));
                } else {
                    $rating += (int)($child->get('rating'));
                }//endif
            }//endfor
            $total = count($children);
            $rating = ((double)$rating)/$total;
        }//endif
        return $rating;
    }//endof method get_publication_rating()
    
    /**
     * method returns all the publications for in this library, through an sql call.
     * @param type $group_id
     * @param type $offset
     * @param type $limit
     * @return array of publication
     */
    public static function get_publications($group_id, $offset=0, $limit=10) {
        global $USER;
        // retrieve database data 
        ($publications = get_records_sql_array("SELECT * FROM {artefact} a
                                            WHERE a.group = ? AND a.artefacttype = 'publication'
                                            ORDER BY a.title ASC", array($group_id), $offset, $limit))
        || //or empty array if nothing comes back
        ($publications = array());
        
        //get the user's role withint the group.
        $role = group_user_access($group_id, $USER->get('id'));
        
        // if tags are set, call static method on the superclass to get the tags
        // also edit the description to deal with formatting issues (note the foreach loop is done by reference)
        foreach ($publications as &$publication) {
            if (!isset($publication->tags)) {
                $publication->tags = ArtefactType::artefact_get_tags($publication->id);
            }//endif
            $publication->description = '<p>' . preg_replace('/\n\n/','</p><p>', $publication->description) . '</p>';
            
            //Add the attached file id to this object.
            $artefact = new ArtefactTypePublication($publication->id);
            $publication->fileid = $artefact->attachment_id_list()[0];
            // get any existing review for this publication from the current user.
            $publication->review = ArtefactTypeReview::user_review_from_publication_id($publication->id);
            // get publication rating
            $publication->rating = $artefact->get_publication_rating();
            $publication->rating_volume = count($artefact->get_children_instances());
        }//endfor
        // compile results dataset containing total count, data to display, and offsets
        $result = array(
            'count'  => count_records('artefact', 'group', $group_id, 'artefacttype', 'publication'),
            'data'   => $publications,
            'offset' => $offset,
            'limit'  => $limit,
            'canmanagelibrary' => ($role != 'member'),
            'group' => param_integer('group'),
        ); 
        return $result;
    }//endof static function get_publications(.,.,.)
    
    
    private static function get_ratings_from_db($group_id){
        // retrieve database rating data 
        ($ratings_db = get_records_sql_array("SELECT a2.owner AS usr_id, "
                                            . "a1.id as pub_id, r.rating as rating "
                                            . "FROM artefact a1 "
                                            . "  JOIN artefact a2 ON a1.id = a2.parent "
                                            . "  JOIN artefact_library_review r ON a2.id = r.artefact "
                                            . "WHERE a1.artefacttype = 'publication' "
                                            . "  AND a2.artefacttype = 'review' "
                                            . "  AND a1.group = ? "
                                            . "ORDER BY usr_id, pub_id", 
                                            array($group_id)))
        || //or empty array if nothing comes back
        ($ratings_db = array());
        
        // the database results set isn't in the datastructure expected by the recommendation system.
        $ratings = array();
        foreach($ratings_db as $rating_db) {
            // if this is the first user, then initialise the array
            if(!array_key_exists($rating_db->usr_id, $ratings)) {
                $ratings[$rating_db->usr_id] = array();
            }//endif
            $ratings[$rating_db->usr_id][$rating_db->pub_id] = $rating_db->rating;
        }//endfor
        return $ratings;
    }//endof static function get_ratings_from_db(.)
    
    public static function get_alternative_recommendation($group_id) {
        global $USER;
        require_once("rs/lib.php");
 
        //retrive ratings from the database.
        $ratings = ArtefactTypePublication::get_ratings_from_db($group_id);
         
        $rs =  RecommenderSystemFactory::get_recommender(get_config_plugin('artefact', 'library', 'recommendersystemclass'));
        
        $alt = $rs->make_alternative_suggestion($ratings, $USER->get('id'));
        return $alt ? new ArtefactTypePublication($alt) : null;
    }//endof static function get_alternative_recommendation(.)
    
    public static function get_recommendations($group_id) {
        global $USER;
        require_once("rs/lib.php");
 
        //retrive ratings from the database.
        $ratings = ArtefactTypePublication::get_ratings_from_db($group_id);
         
        $rs =  RecommenderSystemFactory::get_recommender(get_config_plugin('artefact', 'library', 'recommendersystemclass'));
        $recommendations = $rs->get_recommendations($ratings, $USER->get('id'));
        
        
        $user_recs = array();
        foreach($recommendations as $pub=>$ranking) {
            $obj = new stdClass();
            $pub_obj = new ArtefactTypePublication($pub);
            $obj->pub_name = $pub_obj->get('title');
            $obj->pub_id = $pub;
            // publications only have one attachement.
            $obj->file_id = $pub_obj->attachment_id_list()[0];
            $obj->ranking = round($ranking, 2);
            array_push($user_recs, $obj);
        }//endfor

        $user_recs = array_slice($user_recs, 0, 5);
        $result = array(
            'count'  => count($user_recs),
            'data'   => $user_recs,
        ); 
        return $result;
    }//endof static function get_recommendations(.)

    public static function get_similar_items($publication_id) {
        global $USER;
        require_once("rs/lib.php");
 
        //retrive ratings from the database.
        $ratings = ArtefactTypePublication::get_ratings_from_db(ArtefactTypeReview::get_review_group());
         
        $rs =  RecommenderSystemFactory::get_recommender(get_config_plugin('artefact', 'library', 'recommendersystemclass')); 
        $similar_items = $rs->get_similar_items($ratings, $publication_id);
        
        $item_recs = array();
        foreach($similar_items as $pub=>$ranking) {
            $obj = new stdClass();
            $pub_obj = new ArtefactTypePublication($pub);
            $obj->pub_name = $pub_obj->get('title');
            $obj->pub_id = $pub;
            // publications only have one attachement.
            $obj->file_id = $pub_obj->attachment_id_list()[0];
            $obj->similary = round($ranking, 2);
            $obj->user_rating = ArtefactTypeReview::user_review_from_publication_id($pub);
            array_push($item_recs, $obj);
        }//endfor

        $item_recs = array_slice($item_recs, 0, 5);
        $result = array(
            'count'  => count($item_recs),
            'data'   => $item_recs,
        ); 
        return $result;
    }//endof static function get_recommendations(.)    
    
    
    public static function get_ratings($publication_id, $offset=0, $limit=10, $rating_filter) {
        global $USER;

        $publication = new ArtefactTypePublication($publication_id);
        $publication_file_id = $publication->attachment_id_list()[0];
        $file = new ArtefactTypeFile($publication_file_id);
        $publication_file_name = $file->get('title');
        $reviews = $publication->get_children_instances();
        $pub_ratings = array();
        foreach($reviews as $review) {
            $obj = new stdClass();
            $obj->title = $review->get('title');
            $obj->description = $review->get('description');
            $obj->rating = $review->get('rating');
            $usr = get_user_for_display($review->get('owner'));
            $obj->author_id = $review->get('owner');
            $name = "";
            if($usr->preferredname == "") {
                $name = $usr->firstname . ' ' . $usr->lastname ;
            } else {
                $name = $usr->preferredname;
            }//endif
            $obj->author_name = $name;
            $obj->author_pic_url = profile_icon_url($usr, 25, 25);
            if($rating_filter == 7 || $obj->rating == $rating_filter) {
                array_push($pub_ratings, $obj);
            }
        }//endfor
        
        usort($pub_ratings, function($a, $b)
        {
            $comp_ratings = strcmp($b->rating, $a->rating);
            if(strcmp($b->rating, $a->rating) != 0) {
                return $comp_ratings;
            } else {
                return strcmp($a->title, $b->title);
            }
        });
        
        $result = array(
            'count'  => count($pub_ratings),
            'data'   => $pub_ratings,
            'offset' => $offset,
            'limit'  => $limit,
            'publication_title' => $publication->get('title'),
            'publication_id' => $publication_id,
            'publication_file_name' => $publication_file_name,
            'publication_file_id' => $publication_file_id,
        ); 
        return $result;
    }//endof static function get_ratings(.,.,.)    
    
    
    /**
     * Builds the library's publication list table
     * @param type $library (passed by reference)
     */
    public static function build_library_html(&$library) {
        // start new smarty template
        $smarty = smarty_core();
        // set publication data within the template
        $smarty->assign('library', $library);
        // set the group id within the template (used for URLs)
        $smarty->assign('group', $library['group']);
        // set the template structure from the theme.
        $library['tablerows'] = $smarty->fetch('artefact:library:publicationslist.tpl');
        $pagination = build_pagination(array(
            'id' => 'publicationslist_pagination', 
            'url' => get_config('wwwroot').'artefact/library/index.php', 
            'jsonscript' => 'artefact/library/publicationslist.json.php',
            'datatable' => 'publicationslist',
            'count' => $library['count'],
            'limit' => $library['limit'],
            'offset' => $library['offset'],
            'setlimit' => true,
            'jumplinks' => 6,
            'numbersincludeprevnext' => 2,
            'resultcounttextsingular' => get_string('publication', 'artefact.library'),
            'resultcounttextplural' => get_string('publications', 'artefact.library'),
        ));
        $library['pagination'] = $pagination['html'];
        $library['pagination_js'] = $pagination['javascript'];        
    }//endof static function build_library_html(.)
 
    /**
     * Builds the recommendations publication list table
     * @param type $recommendations (passed by reference)
     */
    public static function build_recommendations_html(&$recommendations) {
        // start new smarty template
        $smarty = smarty_core();
        // set recommendations data within the template
        $smarty->assign('recommendations', $recommendations);
        // set the template structure from the theme.
        $recommendations['tablerows'] = $smarty->fetch('artefact:library:recommendationslist.tpl');
    }//endof static function build_recommendations_html(.)

    /**
     * Builds the similar publication list table
     * @param type similar_items (passed by reference)
     */
    public static function build_similar_items_html(&$similar_items) {
        // start new smarty template
        $smarty = smarty_core();
        // set similar items data within the template
        $smarty->assign('similar_items', $similar_items);
        // set the template structure from the theme.
        $similar_items['tablerows'] = $smarty->fetch('artefact:library:similaritemslist.tpl');
    }//endof static function build_recommendations_html(.)
    
    
    public static function build_ratings_html(&$ratings) {
        // start new smarty template
        $smarty = smarty_core();
        // set publication data within the template
        $smarty->assign('ratings', $ratings);
        // set the template structure from the theme.
        $ratings['tablerows'] = $smarty->fetch('artefact:library:ratingslist.tpl');
        $pagination = build_pagination(array(
            'id' => 'ratingslist_pagination', 
            'url' => get_config('wwwroot').'artefact/library/rating.php?publication='.$ratings['publication_id'], 
            'jsonscript' => 'artefact/library/ratingslist.json.php',//todo
            'datatable' => 'ratingslist',
            'count' => $ratings['count'],
            'limit' => $ratings['limit'],
            'offset' => $ratings['offset'],
            'setlimit' => true,
            'jumplinks' => 6,
            'numbersincludeprevnext' => 2,
            'resultcounttextsingular' => get_string('rating', 'artefact.library'),
            'resultcounttextplural' => get_string('ratings', 'artefact.library'),
        ));
        $ratings['pagination'] = $pagination['html'];
        $ratings['pagination_js'] = $pagination['javascript'];        
    }//endof statuc function build_ratings_htlm(.)
    
    /**
     * Gets the form to manage the library's publication files.
     * @param type $group_id
     * @return pieform
     */
    public static function get_publications_form() {
        $elements = ArtefactTypePublication::get_publications_form_elements();
        // add the submit widgets for the form
        $elements['submit'] = array(
            'type' => 'submitcancel',
            'class' => 'btn-primary',
            'value' => array(get_string('updatelibrary','artefact.library'), get_string('cancel')),
            'goto' => get_config('wwwroot').'artefact/library/index.php?group='.param_integer('group'),
        );
        //generate the final form to be passed to the web template.
        $libraryform = array(
            'name' => 'updatelibrary',
            'plugintype' => 'artefact',
            'pluginname' => 'library',
            'validatecallback' => array('ArtefactTypePublication','validate_library_publications'),
            'successcallback' => array('ArtefactTypePublication','submit_library_publications'),
            'elements' => $elements,
        );
        return pieform($libraryform);
    }//endof static method get_publications_form()
    
    /**
    * coordinate the input widgets of the form. 
     * In this case only the file selector.
    */
    public static function get_publications_form_elements() {
        global $USER;
        // The form will use the 'filebrowser' pieform widget to will also need to
        // import the file artefact.
        safe_require('artefact', 'file');
        $group = param_integer('group');
        // the 'filebrowser's default start population is an array of file ids.
        $attachments = array();
        
        //Read the current publication within the database for this group
        $publications = ArtefactTypePublication::get_publications($group);
        //iterate over each puplication to get a the fileid of its attachment
        foreach($publications['data'] as $publication){
           // build up array of file ids
            array_push($attachments, $publication->fileid);
        }//endfor
        
        // get the folder id for the library folder. 
        // NOTE: ArtefactTypeFolder::get_folder_id very handily generates
        //       the folder is it doesn't already exist.
        $folderid = ArtefactTypeFolder::get_folder_id('library', 'library', $parentfolderid=null, $create=true, $userid=null, $groupid=$group);
        
        // the pieform element folder will only contain a single widget: the filebrowner. 
        $elements = array(
            'publicationfiles' => array(
                'type' => 'filebrowser',
                'title' => get_string('selectpublications', 'artefact.library'),
                'page' => get_config('wwwroot') . 'artefact/library/managelibrary.php?group='.$group,
                'folder' => $folderid,
                'highlight' => true,
                'group' => $group,
                'browse' => 2,
                'config' => array(
                    'upload' => true,
                    'edit' => false,
                    'select' => true,
                    'createfolder' => false,
                    'uploadagreement' => get_config_plugin('artefact', 'file', 'uploadagreement'),
                    'resizeonuploaduseroption' => get_config_plugin('artefact', 'file', 'resizeonuploaduseroption'),
                    'resizeonuploaduserdefault' => $USER->get_account_preference('resizeonuploaduserdefault'),
                ),
                'defaultvalue'       => $attachments,
                'selectlistcallback' => 'artefact_get_records_by_id',
            ),
        );
        return $elements;
    }//endof static method get_publications_form_elements()
    
    /**
     * This is a call back method from the pieform:
     * 'validatecallback' => array('ArtefactTypePublication','validate_library_publications')
     * @global type $USER
     * @param Pieform $form
     * @param type $values
     */
    public static function validate_library_publications(Pieform $form, $values) {
    }//endof static method validate_library_publications(,)   
    
    /**
     * The submit button will generate a publications artefact for each file selected by the pieform
     * This is call back method for the pieform:
     * 'successcallback' => array('ArtefactTypePublication','submit_library_publications')
     * @param Pieform $form
     * @param type $values
     */
    public static function submit_library_publications(Pieform $form, $values) {
        global $SESSION;
        // get the group id
        $group = param_integer('group');
        // retrieve publications data from database
        $publications = ArtefactTypePublication::get_publications($group);

        $current_publication_list = array();
        $selected_files = array();
        if($values['publicationfiles']) {
            $selected_files = $values['publicationfiles'];
        }//

        // delete publication is the attachment hasn't be selected in the filebrowser.
        foreach($publications['data'] as $publication){
            $artefact = new ArtefactTypePublication($publication->id);
            //there's only on attachment per publication, so only access the 0th element.
            $attachmentfileid = $artefact->attachment_id_list()[0];
            if(in_array($attachmentfileid, $selected_files)) {
                array_push($current_publication_list, $attachmentfileid);
            } else {
                $artefact->delete();
            }
        }//endfor
        
        // if files were selected then iterate through all the fileid to create publication artefacts
        $new_files = array_diff($selected_files, $current_publication_list);
        foreach($new_files as $publicationfileid) {
            $artefact = new ArtefactTypePublication();
            $artefact->set('group', $group);
            // create the file artefact, so we can access the name of the file.
            $file = new ArtefactTypeFile($publicationfileid);
            $artefact->set('title', $file->get('title'));
            //need to commit new artefact before adding the attachment.
            $artefact->commit();                
            // using the ArtefactType class' attachment functionity to associate
            // a file with the publication artefact.
            $artefact->attach($publicationfileid);
      }//endfor

        
        //Display the success message
        $SESSION->add_ok_msg(get_string('librarysavedsuccessfully', 'artefact.library'));

        //return to the library main page.
        $returnurl = get_config('wwwroot') . 'artefact/library/index.php?group='.$group;
        redirect($returnurl);    
    }//endof method submit_library_publications()
    
    
    /**
     * Gets the form to manage the publication's data.
     * @param type $group_id
     * @return pieform
     */
    public static function get_publication_management_form($publication) {
   
        $elements = ArtefactTypePublication::get_publication_management_form_elements($publication);
        // add the submit widgets for the form
        $elements['submit'] = array(
            'type' => 'submitcancel',
            'class' => 'btn-primary',
            'value' => array(get_string('updatepublication','artefact.library'), get_string('cancel')),
            'goto' => get_config('wwwroot').'artefact/library/index.php?group='.$publication->get('group'),
        );
        //generate the final form to be passed to the web template.
        $publicationform = array(
            'name' => 'updatelibrary',
            'plugintype' => 'artefact',
            'pluginname' => 'library',
            'validatecallback' => array('ArtefactTypePublication','validate_publication'),
            'successcallback' => array('ArtefactTypePublication','submit_publication'),
            'elements' => $elements,
        );
        return pieform($publicationform);
    }//endof static method get_publication_management_form(.)
    
    /**
     * returns the widget for the publication management form.
     * @param type $publication
     */
    public static function get_publication_management_form_elements($publication) {
        $elements = array (
        'title' => array(
                'type' => 'text',
                'defaultvalue' => null,
                'title' => get_string('title', 'artefact.library'),
                'size' => 50,
                'rules' => array(
                    'required' => true,
                ),
            ),
            'description' => array(
                'type'  => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'defaultvalue' => null,
                'title' => get_string('description', 'artefact.library'),
            ),
            'tags'        => array(
                'type'        => 'tags',
                'title'       => get_string('tags'),
                'description' => get_string('tagsdescprofile'),
                'help'        => true,
            ),
        );

        foreach ($elements as $k => $element) {
             if($k <> 'shelffiles'){
                 $elements[$k]['defaultvalue'] = $publication->get($k);
             }//endif
         }//endforeach
         $elements['publication'] = array(
             'type' => 'hidden',
             'value' => $publication->id,
         );

        return $elements;    
    }//endof static function get_publication_management_form_elements(.)
    
    /**
     * This is a call back method from the pieform:
     * 'validatecallback' => array('ArtefactTypePublication','validate_publication')
     * @global type $USER
     * @param Pieform $form
     * @param type $values
     */
    public static function validate_publication(Pieform $form, $values) {
    }//endof static method validate_publication(,)   
    
    

    /**
     * This is call back method for the pieform:
     * 'successcallback' => array(generate_artefact_class_name('shelf'),'submit')
     * @global type $SESSION
     * @param Pieform $form
     * @param type $values
     */
    public static function submit_publication(Pieform $form, $values) {
        global $SESSION;

        $id = (int) $values['publication'];
        $artefact = new ArtefactTypePublication($id);
        
        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);

        $artefact->set('tags', $values['tags']);
        
        $artefact->commit();
        
        $SESSION->add_ok_msg(get_string('publicationupdatedsuccessfully', 'artefact.library'));

        $returnurl = get_config('wwwroot') . 'artefact/library/index.php?group='.$artefact->get('group');

        redirect($returnurl);    
        
    }//endof static method submit_publication(.,.)    
}//endof ArtefacttypePublication


/**
 * The artefact type review encapsulates a student's review of a publication.
 * It will be associated with a student (owner) and a publication (parent). 
 * Moreover review will need an extension to the artefact db table so the
 * review's rating can be stored.
 */
class ArtefactTypeReview extends ArtefactType {
    protected $rating = 0; // the user's rating of the publication (between 0 and 6)

    /**
     * The constructor fetches the extra data.
     *
     * @param integer
     * @param object
     */
    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);

        if ($this->id) {
            if ($pdata = get_record('artefact_library_review', 'artefact', $this->id, null, null, null, null, 'rating')) {
                // this would only retunr one single rating, so the foreach loop will only iterate once.
                foreach($pdata as $rating_data) {
                    $this->rating= $rating_data;
                }//endfor
            } else {
                // This should never happen unless the user is playing around with reference IDs in the location bar or similar
                throw new ArtefactNotFoundException(get_string('reviewdoesnotexist', 'artefact.library'));
            } //endif
        }//endif
    }//endof method __construct(.,.)

    /**
     * This method overrides ArtefactType::commit() by adding additional data
     * into the artefact_library_review table.
     *
     */
    public function commit() {
        if (empty($this->dirty)) {
            return;
        }
        // Return whether or not the commit worked
        $success = false;
        db_begin();
        $new = empty($this->id);
        parent::commit();

        $this->dirty = true;

        $data = (object)array(
            'artefact'  => $this->get('id'),
            'rating' => $this->get('rating'),
        );

        if ($new) {
            $success = insert_record('artefact_library_review', $data);
        }
        else {
            $success = update_record('artefact_library_review', $data, 'artefact');
        }

        db_commit();

        $this->dirty = $success ? false : true;

        return $success;
    }//endof commit      

    /**
     * This method overrides ArtefactType::delete() by deleting theadditional data
     * into the artefact_library_review table.     * @return type
     */
    public function delete() {
        if (empty($this->id)) {
            return;
        }

        db_begin();
        delete_records('artefact_library_review', 'artefact', $this->id);

        parent::delete();
        db_commit();
    }//endof method delete()

    public static function bulk_delete($artefactids, $log=false) {
        if (empty($artefactids)) {
            return;
        }

        $idstr = join(',', array_map('intval', $artefactids));

        db_begin();
        delete_records_select('artefact_library_review', 'artefact IN (' . $idstr . ')');
        parent::bulk_delete($artefactids);
        db_commit();
    }//endof static function bulk_delete(.,.)    
    
    /**
     * Returns this artefact's type
     * @todo understand how the icon framework operates
     * @param type $options
     * @return string
     */
    public static function get_icon($options = null): string {
       return false; 
    }//endof static method get_icon(.)

    /** 
     * returns the default link for this artefact, can be local sensitive
     * (hence the array
     * @param type $id
     */
    public static function get_links($id) {
        return array (
            '_default' => get_config('wwwroot') . 'artefact/library/review.php?id='.$id,
        );
    }//endof static method get_links(.)

    /**
     * @return boolean
     */
    public static function is_singular() {
        return false;
    }//endof statuc method is_singular()
    
    /**
     * This function returns the review's associated group, by accessing the
     * publication (either directly, or via the review's owner.
     * @return type
     * @throws AccessDeniedException
     */
    public static function get_review_group() {
        $groupid = 0;
        // if page was invoked from the publication list then the publication parameter would be set.
        // otherwise check is this review's id was set
        // finally throw an exception
        
        if (param_exists('publication') ) {
            $artefact = new ArtefactTypePublication(param_integer('publication'));
            $groupid = $artefact->get('group');
        } elseif (param_exists('id')) { //if the page was invoked with the review's id
            $artefact = new ArtefactTypeReview(param_integer('id'));
            $artefact = new ArtefactTypePublication($artefact->get('parent'));
            $groupid = $artefact->get('group');
        } else {
            throw new AccessDeniedException(get_string('plugindisableduser', 'mahara',
                            get_string('library', 'artefact.library')));
        }//endif
        return $groupid;
    }//endof static function get_review_group()
    
    
    // returns this user's review given the publication id. 
    // returns null if there isn't one
    public static function user_review_from_publication_id($publication){
        global $USER;
        $review = null;
        $artefact = new ArtefactTypePublication($publication);
        $pub_children_metadata = $artefact->get_children_metadata();
        if($pub_children_metadata) {
            foreach($pub_children_metadata as $child) {
                if($child->owner == $USER->id) {
                    $review = new ArtefactTypeReview($child->id);
                    break;
                }//endif
            }//endfor
        }//endif
        return $review;
    }//endof user_review_from_publication_id(.)
    
    /**
     * Gets the new or edit form for a review.
     * @param type $review
     * @return type
     */
    public static function get_review_form($review = null){
        
        $publication_id = null;
        //if there is not review, then check if there is already a review for this user
        if($review == null ) {
            $publication_id = param_integer("publication");
            $review = ArtefactTypeReview::user_review_from_publication_id(param_integer("publication"));
        } else {
            $publication_id = $review->get('parent');
        }//endif 
        //get the associated filename, file id, and title (can be different)
        $parent = new ArtefactTypePublication($publication_id);
        $publication_title = $parent->get('title');
        // there is only one attachment per publication
        $publication_file_id = $parent->attachment_id_list()[0];
        $publication_file = new ArtefactTypeFile($publication_file_id);
        $publication_file_name = $publication_file->get('title');
        //set return URL 
        $returnurl = get_config('wwwroot').'artefact/library/index.php?group='.ArtefactTypeReview::get_review_group();

        $elements = ArtefactTypeReview::get_review_form_elements($review);
        
        $elements['submit'] = array(
            'type' => 'submitcancel',
            'class' => 'btn-primary',
            'value' => array(get_string('savereview','artefact.library'), get_string('cancel')),
            'goto' => $returnurl,
        );
        $feedbackform = array(
            'name' => empty($review) ? 'addreview' : 'editreview',
            'plugintype' => 'artefact',
            'pluginname' => 'library',
            'validatecallback' => array('ArtefactTypeReview','validate_review'),
            'successcallback' => array('ArtefactTypeReview','submit_review'),
            'elements' => $elements,
        );
        return pieform($feedbackform);
    }//endof static function get_review_form(.)

    /**
     * 
     * @param type $review
     */
    public static function get_review_form_elements($review) {
        global $USER;
        $name = $USER->get('preferredname');
        
        $elements = array(
            'title' => array(
                'type' => 'text',
                //'defaultvalue' => $deftitle,
                'defaultvalue' => null,
                'title' => get_string('Title', 'artefact.library'),
                'size' => 30,
                'rules' => array(
                    'required' => true,
                ),
            ),
            'rating' => array(
                'type' => 'select',
                'defaultvalue' => 1,
                'options' => array(
                    1 => "0: -", 
                    2 => "1: *", 
                    3 => "2: **", 
                    4 => "3: ***", 
                    5 => "4: ****", 
                    6 => "5: *****",
                ),
                'title' => get_string('rating', 'artefact.library'),
                'description' => get_string('ratingdescription', 'artefact.library'),
            ),            
            'description' => array(
                'type'  => 'wysiwyg',
                'rows' => 20,
                'cols' => 70,
                'title' => get_string('reviewbody', 'artefact.library'),
                'description' => get_string('reviewbodydesc', 'artefact.library'),
                'rules' => array(
                    'maxlength' => 1000000,
                ),
            ),

            'tags' => array(
                'type'        => 'tags',
                'title'       => get_string('tags'),
                'description' => get_string('tagsdescprofile'),
                'help'        => true,
            ),
        );

        if (!empty($review)) {
            foreach ($elements as $k => $element) {
                $elements[$k]['defaultvalue'] = $review->get($k);
            }//$review
            $elements['review'] = array(
                'type' => 'hidden',
                'value' => $review->id,
            );
            
        }//endif


        return $elements;
        
    }//endof static function get_review_form_elements(.)
    
    /**
     * This is a call back method from the pieform:
     * 'validatecallback' => 'validatecallback' => array('ArtefactTypeReview','validate_review')
     * @global type $USER
     * @param Pieform $form
     * @param type $values
     */
    public static function validate_review(Pieform $form, $values) {
        global $USER;
        if (!empty($values['review'])) {
            $id = (int) $values['review'];
            $artefact = new ArtefactTypeReview($id);
            
            if (!$USER->can_edit_artefact($artefact)) {
                $form->set_error('submit', get_string('canteditdontownreview', 'artefact.library'));
            }//endif
        }//endif
    }//endof static method validate_review(,)      
    
 /**
     * This is call back method for the pieform:
     * 'successcallback' => array(generate_artefact_class_name('ArtefactTypeReview'),'submit_review')
     * @global type $SESSION
     * @param Pieform $form
     * @param type $values
     */
    public static function submit_review(Pieform $form, $values) {
        global $SESSION, $USER;

        $group = ArtefactTypeReview::get_review_group();

//
        if (!empty($values['review'])) {
            $id = (int) $values['review'];
            $artefact = new ArtefactTypeReview($id);
        }                    
        else {
           $artefact = new ArtefactTypeReview();
          // $artefact->set('group', $group );
           $artefact->set('owner', $USER->get('id'));
           $artefact->set('parent', param_integer('publication'));
        }//endif

        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);
        $artefact->set('rating', $values['rating']);
        $artefact->set('tags', $values['tags']);
        
        $artefact->commit();
        

        $SESSION->add_ok_msg(get_string('reviewsavedsuccessfully', 'artefact.library'));

        $returnurl = get_config('wwwroot') . 'artefact/library/index.php?group='.$group;
        redirect($returnurl);    
    }//endof static method submit_review(.,.)     
    
}//endof class ArtefactTypeReview