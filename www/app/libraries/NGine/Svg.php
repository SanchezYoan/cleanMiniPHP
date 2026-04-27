<?php


namespace NGine;
use const BASE_DIR;

/**
 * Renvoi le contenu text d'un fichier svg pour insertion dans le html d'une page.
 */
class Svg
{
    
    public static function get(string $path, $imageFilePath = null)
    {
        
        $file = BASE_DIR . "/public_html" . $path;
        if (file_exists($file) && "svg" === pathinfo($file, PATHINFO_EXTENSION)) {
            $data = file_get_contents($file);
            if (!empty($imageFilePath)) {
                $data = str_replace("@@imageFilePath@@", $imageFilePath, $data);
            }
            
            return $data;
        }
        
        return null;
    }
}