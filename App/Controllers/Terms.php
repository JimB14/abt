<?php

namespace App\Controllers;

use \Core\View;

class Terms extends \Core\Controller
{
    public function indexAction()
    {
        View::renderTemplate('Terms/index.html', []);
    }

}
