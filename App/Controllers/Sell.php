<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Listing;

class Sell extends \Core\Controller
{
    public function indexAction()
    {
        // get listing data
        $listing = Listing::getSellPageListingDetails('70');

        // test
        // echo '<pre>';
        // print_r($listing);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Sell/index.html', [
            'listing' => $listing
        ]);
    }
}
