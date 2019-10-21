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
//defined('INTERNAL') || die();


interface IRecommenderSystem {
    /**
     * method will make recommendations
     * @param array $elements array of array of recommendation for all actors.
     * @param string $actor identifier of the actor (should be a key of the $elements array)
     * @return array of recommendations
     */
    public function get_recommendations($elements, $actor);
    
    /**
     * This method will return items which are similar to the item passed in as a paramter
     * based on user ratings.
     * @param array $elements array of array of recommendation for all actors.
     * @param string $item identifier of the items
     * @return array of similar items.
     */
    public function get_similar_items($elements, $item);
    
    /**
     * This method will return an alternative suggestion to suggest something 
     * not aligned to the user's profile.s
     * @param array $elements array of array of recommendation for all actors.
     * @param string $item identifier of the items
     * @stringr item identifier.
     */
    public function make_alternative_suggestion($elements, $actor);
}//endof interface

/**
 * this is an abstract factory class to generate recommendation system and
 * decouple the recommendation system used by the code.
 */
abstract class RecommenderSystemFactory {
    public static function get_recommender($rs_classname = null) {
        $rs = null;
        if($rs_classname != null) {
            // instantiate a recommender system implements IRecommenderSystem
            // based on the class name string.
            $rs = new $rs_classname;
            if(!($rs instanceof IRecommenderSystem) ) {
                throw new Exception("Error:".$rs_classname." does not implement IRecommenderSystem");
            }//endif
        } else {
            $rs = new EclideanDistanceRecommenderSystem();
        }//endif
        return $rs;
    }//endof static function getRS(.)
}//endof class RecommenderSystemFactory


/**
 * This class implements a very simplistic distance based recommendation approach
 * this is very resource intensive which would not be efficient for very large datasets. 
 */
abstract class SimpleRecommenderSystem implements IRecommenderSystem {
    /**
     * This function will return the similarity measure of two actors
     * @param type $elements
     * @param type $thisactor
     * @param type $actor
     */
    protected abstract function get_similarity_distance($elements, $thisactor, $actor);

    /**
     * method will make recommendations
     * @param array $elements array of array of recommendation for all actors.
     * @param string $actor identifier of the actor (should be a key of the $elements array)
     * @return array of recommendations
     */
    public function get_recommendations($elements, $thisactor): array {
        $total_ratings_sum = array();
        $similarity_ratins_sum = array();
        $recommendations = array();
        $similarity = 0;
        if(array_key_exists($thisactor, $elements)) {
            foreach($elements as $actor=>$ratings) {

                if($thisactor != $actor) {
                    $similarity = $this->get_similarity_distance($elements, $thisactor, $actor);
                }//endif

                if($similarity >0) {
                    foreach($elements[$actor] as $element=>$rating) {
                        if(!array_key_exists($element, $elements[$thisactor])) {
                            if(!array_key_exists($element, $total_ratings_sum)) {
                                $total_ratings_sum[$element] = 0;
                                $similarity_ratins_sum[$element] = 0;
                            }//endif
                            $total_ratings_sum[$element] += $elements[$actor][$element] * $similarity;
                            $similarity_ratins_sum[$element] += $similarity;
                        }//endif
                    }//endfor
                }//endif
            }//endfor

            foreach($total_ratings_sum as $element=>$total_rating) {
                $recommendations[$element] = strval($total_rating / $similarity_ratins_sum[$element]);
            }//endgfor
        }//endif 

        // array multisort will re-index if the keys are numerical which we don't want here.
        // the code below is a work around from here:
        // https://www.php.net/manual/en/function.array-multisort.php
        // c.f. post by rnacken at gmail dot com
        $array = array($recommendations, array_keys($recommendations));
        array_multisort($array[0], SORT_DESC,  $array[1], SORT_DESC);
        $ret = array_combine($array[1], $array[0]);
        unset($array);

        return $ret;
    }//endof method get_recommendations(.,.)

    /**
     * This method will return the most dissimilar item of the user's top recommendations
     * @param array $elements array of array of recommendation for all actors.
     * @param string $item identifier of the items
     * @stringr item identifier.
     */
    public function make_alternative_suggestion($elements, $thisactor) {
        //get top recommendations
        $recommendations = $this->get_recommendations($elements, $thisactor);
        if(count($recommendations) > 0) {
            $similar_items = $this->get_similar_items($elements, array_keys($recommendations)[0]);
            return array_keys($similar_items)[count($similar_items) - 1];
        } else {
            return null;
        }//endif
    }//endof method make_alternative_suggestion(.,.)
    

    /**
     * this method will return an array of items similar to the parameter.
     * @param type $elements
     * @param type $item
     * @return array
     */
    public function get_similar_items($elements, $item) {
        $similar_items = array();
        
        //transform matrix. 
        $elements = $this->transform_matrix($elements);
        
        foreach($elements as $other_item=>$reviews) {
            // don't compare an item to items (similarity will be 100%)
            if($item != $other_item) {
                // using the same similarity function but with a transformed matrix
                $similarity = $this->get_similarity_distance($elements, $item, $other_item);

                if($similarity > 0) {
                    $similar_items[$other_item] = $similarity;
                }//endif
            }//endif
        }//endfor

        // array multisort will re-index if the keys are numerical which we don't want here.
        // the code below is a work around from here:
        // https://www.php.net/manual/en/function.array-multisort.php
        // c.f. post by rnacken at gmail dot com
        $array = array($similar_items, array_keys($similar_items));
        array_multisort($array[0], SORT_DESC,  $array[1], SORT_DESC);
        $ret = array_combine($array[1], $array[0]);
        unset($array);

        return $ret;
    }//endof function get_similar_items(.,.)
    
    /**
     * this worker function transform the matrix from an array of reviewer reviewing
     * publications, to a matrix of publication with ratings from reviewers.
     * @param type $elements
     */
    private function transform_matrix($elements) {
        $transformed_matrix = array();
        foreach($elements as $actor=>$ratings) {
            foreach($ratings as $pub=>$rating) {
                $transformed_matrix[$pub][$actor] = $rating;
            }//endfor
        }//endfor
        return $transformed_matrix;
    }//endof transform_matrix(.)
}//endof class SimpleRecommenderSystem


/**
 * This class implements a very simplistic distance based recommendation approach
 * this is very resource intensive which would not be efficient for very large datasets. 
 */
class EclideanDistanceRecommenderSystem extends SimpleRecommenderSystem {
 
    /**
     * This function will return the similary measure of two actors
     * @param type $elements
     * @param type $thisactor
     * @param type $actor
     */
    protected function get_similarity_distance($elements, $thisactor, $actor) {
        $has_similarity = false;
        $diff_sum_sq = 0;
        
        foreach($elements[$thisactor] as $element=>$rating) {
            if(array_key_exists($element, $elements[$actor])) {
                $has_similarity = true;
                $diff_sum_sq  += pow($rating - $elements[$actor][$element], 2);
            }//endif
        }//endfor
        return $has_similarity ? 1/(1+sqrt($diff_sum_sq)) : 0;
    }//endof get_similarity_distance(.,.,.)
}//endof class EclideanDistanceRecommenderSystem

/**
 * This class implements a very simplistic distance based recommendation approach
 * this is very resource intensive which would not be efficient for very large datasets. 
 */
class PearsonCorrelationRecommenderSystem extends SimpleRecommenderSystem {
 
    /**
     * This function will return the similarity measure of two actors
     * @param type $elements
     * @param type $thisactor
     * @param type $actor
     */
    protected function get_similarity_distance($elements, $thisactor, $actor) {
        $num_matches = 0;
        $sum1 = 0;
        $sum2 = 0;
        $sum1Sq = 0;
        $sum2Sq = 0;
        $pSum = 0;
        
        foreach($elements[$thisactor] as $element=>$rating) {
            if(array_key_exists($element, $elements[$actor])) {
                $sum1 += $rating;
                $sum2 += $elements[$actor][$element];
                $sum1Sq += pow($rating, 2);
                $sum2Sq += pow($elements[$actor][$element], 2);
                $pSum += $rating * $elements[$actor][$element];
                $num_matches+=1;
            }//endif
        }//endfor
        
        if($num_matches != 0) { 
            $numerator = ($num_matches*$pSum) - ($sum1*$sum2);
            $denominator = sqrt(($num_matches*$sum1Sq) - pow($sum1, 2)) * sqrt(($num_matches*$sum2Sq) - pow($sum2, 2));
            return $denominator != 0 ? $numerator/$denominator : 0;
        } else {
            return 0;
        }
    }//endof get_similarity_distance(.,.,.)
}//endof class PearsonCorrelationRecommenderSystem



