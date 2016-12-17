<?php

namespace App\Models;

use PDO;

/**
 * Subcategory model
 */
class Subcategory extends \Core\Model
{

    public static function getSubCategories()
    {
        // retrieve post data from ajax code
        $category_id = isset($_POST['category_id']) ? filter_var($_POST['category_id'], FILTER_SANITIZE_NUMBER_INT) : '';

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT id, sub_cat_name FROM sub_category WHERE category_id = :category_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':category_id' => $category_id
            ];
            $stmt->execute($parameters);
            $sub_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // test
            // echo "<pre>";
            // print_r($sub_categories);
            // echo "</pre>";
            // exit();;

            return $sub_categories;

        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }
}
