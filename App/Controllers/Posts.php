<?php

namespace App\Controllers; // identy namespace

use \Core\View;
use App\Models\Post;

/**
 * Posts controller
 *
 * PHP version 7.0
 */
class Posts extends \Core\Controller
{
    /**
     * Show the index page
     * @return void
     */
    public function indexAction()
    {
        //echo "Hello from index method of Posts controller."; exit();

        echo '<p>Query string parameters: <pre>' .
              htmlspecialchars(print_r($_GET, true)) . '</pre></p>';

        // // call getAll() static method of App/Models/Post model
        // $posts = Post::getAll();
        //
        // // call renderTemplate() method of App/Core/View class & pass $posts
        // View::renderTemplate('Posts/index.html', [
        //     'posts' => $posts
        // ]);
    }


    /**
     * Show the add new page
     *
     * @return void
     */
    public function addNewAction()
    {
        echo "Hello from the addNew action in the Posts controller!";
    }


    /**
     * Show the edit page
     *
     * @return void
     */
    public function editAction()
    {
        echo "Hello from the edit action in the Posts controller!";
        echo "<p>Route parameters: <pre>" .
              htmlspecialchars(print_r($this->route_params, true)) . "</pre></p>";
    }

}
