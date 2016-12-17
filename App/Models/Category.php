<?php

namespace App\Models;

use PDO;

/**
 * Category model
 */
class Category extends \Core\Model
{
    public static function getCategories()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM category";
            $stmt = $db->query($sql);
            $categories = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $categories;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }


    /**
     * gets business category name
     *
     * @param  integer $category The category ID
     * @return string           The category name
     */
    public static function getCategoryName($category)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT name FROM category WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $category
            ];
            $stmt->execute($parameters);
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            $category_name = $result->name;

            // return value to Listing Controller
            return $category_name;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * gets business category name
     *
     * @param  integer $subcategory The sub category ID
     * @return string           The sub category name
     */
    public static function getSubCategoryName($subcategory)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT sub_cat_name FROM sub_category WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $subcategory
            ];
            $stmt->execute($parameters);
            $result = $stmt->fetch(PDO::FETCH_OBJ);

            $subcategory_name = $result->sub_cat_name;

            // return value to Listing Controller
            return $subcategory_name;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }

}
