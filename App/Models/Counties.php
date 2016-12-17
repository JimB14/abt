<?php

namespace App\Models;


/**
 * Counties model
 */
class Counties extends \Core\Model
{

    /**
     * retrives counties from counties array @App/Models/Library/counties.php
     *
     * @return array The counties of selected state
     */
    public static function getCounties()
    {
        // counties array
        require 'Library/counties.php';

        // retrieve data from Ajax
        $selected_state = isset($_POST['state']) ? filter_var($_POST['state'], FILTER_SANITIZE_STRING) : '';

        // initialize array
        $county_list = [];

        // loop thru $counties array (required above @Library/counties.php)
        foreach($counties as $state => $county)
        {
            if($state == $selected_state )
            {
                $county_list = $county;
            }
        }

        return $county_list;
    }




    /**
     * retrives counties from counties array @App/Models/Library/counties.php
     *
     * @return array The counties of selected state
     */
    public static function getCounty()
    {
        // counties array
        require 'Library/county.php';

        // retrieve data from Ajax
        $selected_state = isset($_POST['state']) ? filter_var($_POST['state'], FILTER_SANITIZE_STRING) : '';

        // initialize array
        $county_list = [];

        // loop thru $county array (required above @Library/county.php)
        foreach($county as $state => $county)
        {
            if($state == $selected_state )
            {
                $county_list = $county;
            }
        }

        return $county_list;
    }
}
