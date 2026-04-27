<?php

/**
 * Classe utilitaire.
 *
 * Fournit des méthodes de manipulation et d'extraction de données.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */
abstract class Utility
{
    
    /**
     * Supprimer un fichier.
     *
     * @param string $path Chemin du fichier.
     *
     * @return bool true si supprimé, false sinon.
     */
    public static function removeFile(string $path)
    {
        if (!is_file($path)) {
            return false;
        }
        
        return unlink($path);
    }
    
    /**
     * Supprimer récursivement un dossier.
     *
     * @param string $path Chemin du dossier.
     *
     * @return bool true si supprimé, false sinon.
     */
    public static function removeFolder(string $path)
    {
        if (!is_dir($path)) {
            return false;
        }
        
        $items = array_diff(scandir($path), ['.', '..']);
        
        foreach ($items as $item) {
            $file = $path . DIRECTORY_SEPARATOR . $item;
            is_dir($file) ? self::removeFolder($file) : unlink($file);
        }
        
        return rmdir($path);
    }
    
    /**
     * Ajouter un élément à un fichier JSON.
     *
     * @param string $filename Chemin du fichier JSON.
     * @param mixed  $newData  Données à ajouter.
     *
     * @return void Aucune valeur de retour.
     */
    public static function addDataToJsonFile($filename, $newData)
    {
        $data   = self::readJsonFile($filename);
        $data[] = $newData;
        self::writeJsonFile($filename, $data);
    }

    /**
     * Lire un fichier JSON et retourner les données.
     *
     * @param string $filename Chemin du fichier JSON.
     *
     * @return array<mixed> Données décodées.
     */
    public static function readJsonFile($filename)
    {
        $jsonData = file_get_contents($filename);
        return json_decode($jsonData, true);
    }

    /**
     * Écrire un tableau dans un fichier JSON.
     *
     * @param string $filename Chemin du fichier JSON.
     * @param mixed  $data     Données à écrire.
     *
     * @return void Aucune valeur de retour.
     */
    public static function writeJsonFile($filename, $data)
    {
        $jsonData = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents($filename, $jsonData);
    }

    /**
     * Supprimer une entrée d'un fichier JSON.
     *
     * @param string $filename Chemin du fichier JSON.
     * @param int    $index    Index à supprimer.
     *
     * @return void Aucune valeur de retour.
     */
    public static function removeDataFromJsonFile($filename, $index)
    {
        $data = self::readJsonFile($filename);
        if (isset($data[$index])) {
            unset($data[$index]);
            self::writeJsonFile($filename, array_values($data));
        }
    }

    /**
     * Mettre à jour une entrée d'un fichier JSON.
     *
     * @param string $filename Chemin du fichier JSON.
     * @param int    $index    Index à mettre à jour.
     * @param mixed  $newData  Nouvelles données.
     *
     * @return void Aucune valeur de retour.
     */
    public static function updateDataInJsonFile($filename, $index, $newData)
    {
        $data = self::readJsonFile($filename);
        if (isset($data[$index])) {
            $data[$index] = $newData;
            self::writeJsonFile($filename, $data);
        }
    }
    
    /**
     * Générer un identifiant GUID.
     *
     * @param string $prefix Préfixe optionnel.
     *
     * @return string GUID généré.
     */
    public static function makeGUID(string $prefix): string
    {
        if (empty($prefix)) {
            $prefix = (string)random_int(5, 10);
        }
        $charid = strtoupper(md5(uniqid($prefix, true)));
        $hyphen = chr(45);// "-"
        $uuid   = substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
        return strtolower($uuid);
    }
    
    /**
     * Générer une chaîne aléatoire via un générateur sécurisé (random_int).
     *
     * Cette fonction utilise les types PHP 7+ mais a été pensée pour PHP 5.
     * Pour PHP 7, random_int est natif.
     * Pour PHP 5.x, dépend de https://github.com/paragonie/random_compat.
     *
     * @param int    $length   Nombre de caractères souhaités.
     * @param string $keyspace Ensemble des caractères possibles.
     *
     * @return string Chaîne générée.
     *
     * @throws Exception Si la longueur est invalide.
     */
    public static function random_str(
        int    $length = 64,
        string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ): string
    {
        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }
        $pieces = [];
        $max    = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces [] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
    
    /**
     * Générer un identifiant court basé sur le timestamp.
     *
     * @param int $length Longueur souhaitée.
     *
     * @return string Identifiant généré.
     */
    public static function uniqueID(int $length)
    {
        // sha1 the timestamps and returns substring
        // of specified length
        return substr(sha1(time()), 0, $length);
    }
    
    /**
     * Normaliser un tableau et le convertir dans un format standard.
     *
     * @param array $arr Tableau source.
     *
     * @return array Tableau normalisé.
     */
    public static function normalize(array $arr): array
    {
        
        $keys = array_keys($arr);
        
        $newArr = [];
        foreach ($keys as $iValue) {
            if (is_int($iValue)) {
                $newArr[$arr[$iValue]] = null;
            } else {
                $newArr[$iValue] = $arr[$iValue];
            }
        }
        
        return $newArr;
    }
    
    /**
     * Déterminer l'orientation d'une image.
     *
     * @param string $imgPath Chemin de l'image.
     *
     * @return string|null "Landscape", "Portrait" ou null si absent.
     */
    public static function imageProfile(string $imgPath): ?string
    {
        if (!file_exists($imgPath)) {
            return null;
        }
        [$width, $height] = getimagesize($imgPath);
        if ($width > $height) {
            return "Landscape";
        }
        
        return "Portrait";
        
    }
    
    /**
     * Déterminer l'orientation d'une image avec seuil de ratio.
     *
     * @param string $imgPath Chemin de l'image.
     *
     * @return string|null "Landscape", "Portrait" ou null si absent.
     */
    public static function imageRawProfile(string $imgPath): ?string
    {
        if (!file_exists($imgPath)) {
            return null;
        }
        [$width, $height] = getimagesize($imgPath);
        $ratio = ($width / $height);
        
        if ($ratio > 1.2) {
            return "Landscape";
        }
        
        return "Portrait";
        
    }
    
    /**
     * Retourner une chaîne en séparant les éléments par des virgules.
     *
     * @param array $arr Tableau source.
     *
     * @return string Chaîne concaténée.
     */
    public static function commas(array $arr): string
    {
        return implode(",", (array)$arr);
    }
    
    /**
     * Encoder une URL en préservant les éléments de base.
     *
     * @param string $val URL source.
     *
     * @return string|null URL encodée ou null.
     */
    public static function urlEncode($val): ?string
    {
        //return $val;
        if (empty($val)) return null;
        $parts     = parse_url($val);
        $scheme    = $parts["scheme"];
        $host      = $parts["host"];
        $path      = isset($parts["path"]) ? urlencode(ltrim($parts["path"], "/")) : null;
        $query     = isset($parts["query"]) ? urlencode($parts["query"]) : null;
        $fragments = $parts["fragment"] ?? null;
        
        return $scheme . "://" . $host . "/" . $path . $query . $fragments;
    }
    
    /**
     * Encoder une chaîne pour le HTML.
     *
     * @param string $str Chaîne brute.
     *
     * @return string Chaîne encodée.
     */
    public static function encodeHTML($str): string
    {
        return htmlentities($str, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Retirer les accents et caractères spéciaux d'une chaîne.
     *
     * @param string $string Chaîne source.
     *
     * @return string Chaîne normalisée.
     */
    public static function removeAccents($string)
    {
        $string = str_replace(["#", " ", "/", "\\", "'", '"', "--", "&"], ["", "-", "-", "-", "-", "-", "-", "-"], $string);
        $string = mb_strtolower($string);
        
        if (!preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }
        
        $chars = [
            // Decompositions for Latin-1 Supplement
            chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
            chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
            chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
            chr(195) . chr(135) => 'C', chr(195) . chr(136) => 'E',
            chr(195) . chr(137) => 'E', chr(195) . chr(138) => 'E',
            chr(195) . chr(139) => 'E', chr(195) . chr(140) => 'I',
            chr(195) . chr(141) => 'I', chr(195) . chr(142) => 'I',
            chr(195) . chr(143) => 'I', chr(195) . chr(145) => 'N',
            chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
            chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
            chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
            chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
            chr(195) . chr(156) => 'U', chr(195) . chr(157) => 'Y',
            chr(195) . chr(159) => 's', chr(195) . chr(160) => 'a',
            chr(195) . chr(161) => 'a', chr(195) . chr(162) => 'a',
            chr(195) . chr(163) => 'a', chr(195) . chr(164) => 'a',
            chr(195) . chr(165) => 'a', chr(195) . chr(167) => 'c',
            chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
            chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
            chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
            chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
            chr(195) . chr(177) => 'n', chr(195) . chr(178) => 'o',
            chr(195) . chr(179) => 'o', chr(195) . chr(180) => 'o',
            chr(195) . chr(181) => 'o', chr(195) . chr(182) => 'o',
            chr(195) . chr(182) => 'o', chr(195) . chr(185) => 'u',
            chr(195) . chr(186) => 'u', chr(195) . chr(187) => 'u',
            chr(195) . chr(188) => 'u', chr(195) . chr(189) => 'y',
            chr(195) . chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
            chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
            chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
            chr(196) . chr(134) => 'C', chr(196) . chr(135) => 'c',
            chr(196) . chr(136) => 'C', chr(196) . chr(137) => 'c',
            chr(196) . chr(138) => 'C', chr(196) . chr(139) => 'c',
            chr(196) . chr(140) => 'C', chr(196) . chr(141) => 'c',
            chr(196) . chr(142) => 'D', chr(196) . chr(143) => 'd',
            chr(196) . chr(144) => 'D', chr(196) . chr(145) => 'd',
            chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
            chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
            chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
            chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
            chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
            chr(196) . chr(156) => 'G', chr(196) . chr(157) => 'g',
            chr(196) . chr(158) => 'G', chr(196) . chr(159) => 'g',
            chr(196) . chr(160) => 'G', chr(196) . chr(161) => 'g',
            chr(196) . chr(162) => 'G', chr(196) . chr(163) => 'g',
            chr(196) . chr(164) => 'H', chr(196) . chr(165) => 'h',
            chr(196) . chr(166) => 'H', chr(196) . chr(167) => 'h',
            chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
            chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
            chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
            chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
            chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
            chr(196) . chr(178) => 'IJ', chr(196) . chr(179) => 'ij',
            chr(196) . chr(180) => 'J', chr(196) . chr(181) => 'j',
            chr(196) . chr(182) => 'K', chr(196) . chr(183) => 'k',
            chr(196) . chr(184) => 'k', chr(196) . chr(185) => 'L',
            chr(196) . chr(186) => 'l', chr(196) . chr(187) => 'L',
            chr(196) . chr(188) => 'l', chr(196) . chr(189) => 'L',
            chr(196) . chr(190) => 'l', chr(196) . chr(191) => 'L',
            chr(197) . chr(128) => 'l', chr(197) . chr(129) => 'L',
            chr(197) . chr(130) => 'l', chr(197) . chr(131) => 'N',
            chr(197) . chr(132) => 'n', chr(197) . chr(133) => 'N',
            chr(197) . chr(134) => 'n', chr(197) . chr(135) => 'N',
            chr(197) . chr(136) => 'n', chr(197) . chr(137) => 'N',
            chr(197) . chr(138) => 'n', chr(197) . chr(139) => 'N',
            chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
            chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
            chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
            chr(197) . chr(146) => 'OE', chr(197) . chr(147) => 'oe',
            chr(197) . chr(148) => 'R', chr(197) . chr(149) => 'r',
            chr(197) . chr(150) => 'R', chr(197) . chr(151) => 'r',
            chr(197) . chr(152) => 'R', chr(197) . chr(153) => 'r',
            chr(197) . chr(154) => 'S', chr(197) . chr(155) => 's',
            chr(197) . chr(156) => 'S', chr(197) . chr(157) => 's',
            chr(197) . chr(158) => 'S', chr(197) . chr(159) => 's',
            chr(197) . chr(160) => 'S', chr(197) . chr(161) => 's',
            chr(197) . chr(162) => 'T', chr(197) . chr(163) => 't',
            chr(197) . chr(164) => 'T', chr(197) . chr(165) => 't',
            chr(197) . chr(166) => 'T', chr(197) . chr(167) => 't',
            chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
            chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
            chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
            chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
            chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
            chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u',
            chr(197) . chr(180) => 'W', chr(197) . chr(181) => 'w',
            chr(197) . chr(182) => 'Y', chr(197) . chr(183) => 'y',
            chr(197) . chr(184) => 'Y', chr(197) . chr(185) => 'Z',
            chr(197) . chr(186) => 'z', chr(197) . chr(187) => 'Z',
            chr(197) . chr(188) => 'z', chr(197) . chr(189) => 'Z',
            chr(197) . chr(190) => 'z', chr(197) . chr(191) => 's',
        ];
        
        $string = strtr($string, $chars);
        
        return $string;
    }
    
    /**
     * Fusionner deux tableaux.
     *
     * @param mixed $arr1 Premier tableau.
     * @param mixed $arr2 Second tableau.
     *
     * @return array Tableau fusionné.
     */
    public static function merge($arr1, $arr2): array
    {
        return array_merge((array)$arr1, (array)$arr2);
    }
    
    /**
     * Exécuter une requête HTTP via cURL.
     *
     * @param string $url URL à appeler.
     *
     * @return bool true si la réponse est valide, false sinon.
     */
    public static function curl(string $url): bool
    {
        
        if (!defined('CURL_HTTP_VERSION_2_0')) {
            define('CURL_HTTP_VERSION_2_0', 3);
        }
        // create curl resource
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13)');
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        // $output contains the output string
        $output   = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        
        curl_close($ch);
        
        return ($output !== false) && ((int)$httpcode !== 404);
    }
    
    
    /**
     * Retourner la liste des langues acceptées par le navigateur.
     *
     * @param string|null $sLanguage Langue optionnelle fournie par l'utilisateur.
     *
     * @author MPI
     *
     * @return mixed Tableau formaté contenant langues et pays.
     */
    public static function get_accepted_languages($sLanguage = null)
    {
        
        if (!is_null($sLanguage)) {
            $httplanguages = $sLanguage;
        } else {
            $httplanguages = ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
        }
        
        $languages = [];
        
        if (empty($httplanguages)) {
            return $languages;
        }
        
        foreach (preg_split('/,\s*/', $httplanguages) as $accept) {
            $result = preg_match('/^([a-z]{1,8}(?:[-_][a-z]{1,8})*)(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $accept, $match);
            
            if (!$result) {
                continue;
            }
            if (isset($match[2])) {
                $quality = (float)$match[2];
            } else {
                $quality = 1.0;
            }
            
            $countries   = explode('-', $match[1]);
            $region      = array_shift($countries);
            $country_sub = explode('_', $region);
            $region      = array_shift($country_sub);
            
            foreach ($countries as $country) {
                $languages[] = $region . '-' . strtoupper($country);
            }
            
            foreach ($country_sub as $country) {
                $languages[] = $region . '-' . strtoupper($country);
            }
            
            $languages[] = $region;
        }
        
        return $languages;
    }
    
    public static function getDateFormat(): string
    {
        $format = Session::get('FORMAT_DATE');
        if ($format === 'EN') {
            return 'm/d/Y';
        }
        
        return 'd/m/Y';
    }
    
    /**
     * Formater une date selon la préférence de session ou une valeur forcée.
     *
     * @param mixed      $var   Date source.
     * @param bool|mixed $force Format forcé (false pour utiliser la session).
     *
     * @return string|null Date formatée ou null.
     */
    public static function formatDate($var, $force = false)
    {
        
        if (empty($var)) return null;
        
        if ($force === false) {
            $format = Session::get('FORMAT_DATE');
        } else {
            $format = $force;
        }
        
        if ($format === 'EN') {
            return date('m/d/Y', strtotime($var));
        }
        if ($format === 'FR') {
            return date('d/m/Y', strtotime($var));
        }
        
        return null;
    }
    
    
    /**
     * Formater un nombre avec séparateurs personnalisés.
     *
     * @param mixed  $nombre       Valeur à formater.
     * @param int    $decimal      Nombre de décimales.
     * @param string $sep_decimal  Séparateur décimal.
     * @param string $sep_thousand Séparateur de milliers.
     *
     * @return string Nombre formaté.
     */
    public static function formatNombre($nombre, $decimal = 0, string $sep_decimal = ',', string $sep_thousand = ' '): string
    {
        if (preg_match("/[a-zA-Z]+/", $nombre)) {
            return $nombre;
        }
        return number_format($nombre, $decimal, $sep_decimal, $sep_thousand);
    }
    
    /**
     * Convertir une date en format français, avec heure optionnelle.
     *
     * @param mixed       $var       Date source.
     * @param string|null $separator Séparateur entre date et heure.
     *
     * @return string|null Date formatée ou null.
     */
    public static function dateFr($var, $separator = null)
    {
        if (empty($var)) {
            return null;
        }
        $separator = !empty($separator) ? "$separator " : null;
        // date ou date time ?
        $hasTime = strpos($var, ":") !== false;
        $format  = ($hasTime) ? "d/m/Y {$separator}H:i" : "d/m/Y";
        return date($format, strtotime($var));
    }
    
    /**
     * Convertir une date FR (avec ou sans heure) en format SQL Y-m-d H:i:s.
     *
     * @param string $string Date source en format FR.
     *
     * @return false|string|null Date SQL ou null si vide.
     */
    public static function dateTimeFR_to_SQL(string $string)
    {
        
        if (empty($string)) {
            return null;
        }
        
        // check if string has a space, only one in the middle and string has / and :
        $aParts = explode(" ", $string);
        if (count($aParts) === 2 && strpos($aParts[0], "/") !== false && strpos($aParts[1], ":") !== false) {
            
            $date = $aParts[0];
            $time = $aParts[1];
            
            // check if we have valid date and time
            $aDate = explode("/", $date);
            $aTime = explode(":", $time);
            
            if (count($aDate) === 3) {
                if (count($aTime) === 3) {
                    
                    $heures   = str_pad($aTime[0], 2, 0, STR_PAD_LEFT);
                    $minutes  = str_pad($aTime[1], 2, 0, STR_PAD_LEFT);
                    $secondes = str_pad($aTime[2], 2, 0, STR_PAD_LEFT);
                    $dateFR   = DateTime::createFromFormat("d/m/Y H:i:s", "$date $heures:$minutes:$secondes");
                    return $dateFR->format("Y-m-d H:i:s");
                    
                }
                if (count($aTime) === 2) {
                    $heures  = str_pad($aTime[0], 2, 0, STR_PAD_LEFT);
                    $minutes = str_pad($aTime[1], 2, 0, STR_PAD_LEFT);
                    $dateFR  = DateTime::createFromFormat("d/m/Y H:i:s", "$date $heures:$minutes:00");
                    return $dateFR->format("Y-m-d H:i:s");
                    
                }
                $dateFR = DateTime::createFromFormat("d/m/Y H:i:s", "$date 08:00:00");
                return $dateFR->format("Y-m-d H:i:s");
            }
        } else if (count($aParts) === 2 && strpos($aParts[0], "/") !== false && strpos($aParts[1], "h") !== false) {
            
            $date = $aParts[0];
            $time = $aParts[1];
            
            // check if we have valid date and time
            $aDate = explode("/", $date);
            $aTime = explode("h", $time);
            
            if (count($aDate) === 3) {
                if (count($aTime) === 3) {
                    
                    $heures   = str_pad($aTime[0], 2, 0, STR_PAD_LEFT);
                    $minutes  = str_pad($aTime[1], 2, 0, STR_PAD_LEFT);
                    $secondes = str_pad($aTime[2], 2, 0, STR_PAD_LEFT);
                    $dateFR   = DateTime::createFromFormat("d/m/Y H:i:s", "$date $heures:$minutes:$secondes");
                    return $dateFR->format("Y-m-d H:i:s");
                    
                }
                if (count($aTime) === 2) {
                    $heures  = str_pad($aTime[0], 2, 0, STR_PAD_LEFT);
                    $minutes = str_pad($aTime[1], 2, 0, STR_PAD_LEFT);
                    $dateFR  = DateTime::createFromFormat("d/m/Y H:i:s", "$date $heures:$minutes:00");
                    return $dateFR->format("Y-m-d H:i:s");
                    
                }
                $dateFR = DateTime::createFromFormat("d/m/Y H:i:s", "$date 08:00:00");
                return $dateFR->format("Y-m-d H:i:s");
            }
        }
        
        return false;
    }
    
    public static function dateFR_to_SQL(string $date)
    {
        
        if (empty($date)) {
            return null;
        }
        return DateTime::createFromFormat("d/m/Y", $date)->format("Y-m-d");
    }
    
    public static function convertToDateFr($var = null)
    {
        if (empty($var)) {
            return null;
        }
        return date("d/m/Y", strtotime($var));
    }
    
    public static function convertToDateTimeFr($var)
    {
        if (empty($var)) {
            return null;
        }
        return date("d/m/Y H:i:s", strtotime($var));
    }
    
    public static function convertToTimeFr($var)
    {
        if (empty($var)) {
            return null;
        }
        return date("H:i", strtotime($var));
    }
    
    /**
     * Convertir une date en libellé français complet.
     *
     * @param mixed $var      Date source.
     * @param bool  $withHour Inclure l'heure.
     *
     * @return string Date formatée en texte.
     */
    public static function dateFrTextual($var, bool $withHour = true): string
    {
        
        $jours      = [
            "Monday"    => "Lundi",
            "Tuesday"   => "Mardi",
            "Wednesday" => "Mercredi",
            "Thursday"  => "Jeudi",
            "Friday"    => "Vendredi",
            "Saturday"  => "Samedi",
            "Sunday"    => "Dimanche",
        ];
        $mois       = [
            "Jan" => "Janvier",
            "Feb" => "Février",
            "Mar" => "Mars",
            "Apr" => "Avril",
            "May" => "Mai",
            "Jun" => "Juin",
            "Jul" => "Juillet",
            "Aug" => "Août",
            "Sep" => "Septembre",
            "Oct" => "Octobre",
            "Nov" => "Novembre",
            "Dec" => "Décembre",
        ];
        $date       = new Datetime($var);
        $jour_digit = $date->format("d");
        $jour       = $jours[$date->format("l")];
        $mois       = $mois[$date->format("M")];
        $annee      = $date->format("Y");
        $heure      = $date->format("H\hi");
        
        if ($withHour) {
            return "$jour $jour_digit $mois $annee à $heure";
        }
        
        return "$jour $jour_digit $mois $annee";
        
        
    }
    
    /**
     * Afficher une date SQL YYY-MM-DD HH:ii:ss dans un format spécifique.
     *
     * @param mixed  $stringDateTime Date SQL.
     * @param string $formatDate     Format de sortie.
     *
     * @return false|string|null Date formatée ou null.
     */
    public static function convertSQLToLiteral($stringDateTime, $formatDate = 'dd/mm/yyyy+')
    {
        if (empty($stringDateTime)) return null;
        
        
        if ($formatDate === 'mm/dd/yyyy') return date('m/d/Y H:i', strtotime($stringDateTime));
        if ($formatDate === 'dd/mm/yyyy') return date('d/m/Y H:i', strtotime($stringDateTime));
        if ($formatDate === 'dd/mm/yyyy-') {
            if (strpos($stringDateTime, ' 00:00:00') !== false) {
                return date('d/m/Y', strtotime($stringDateTime));
            }
            
            return date('d/m/Y H:i', strtotime($stringDateTime));
        }
        //actuellement sur le projet
        if ($formatDate === 'dd/mm/yyyy+') {
            if (strpos($stringDateTime, ' 00:00:00') !== false) {
                return date('d/m/Y', strtotime($stringDateTime));
            }
            
            return date('d/m/Y à H:i', strtotime($stringDateTime));
        }
        if ($formatDate === 'month') {
            $map = [
                1  => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre',
                10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
            ];
            
            return $map[date('n', strtotime($stringDateTime))];
        }
        
        return null;
        
    }
    
    /**
     * Convertir un nombre de secondes en format H:i:s.
     *
     * @param mixed $var          Durée en secondes.
     * @param bool  $optionalHour Masquer l'heure si elle est à zéro.
     *
     * @return string Durée formatée.
     */
    public static function secToTime($var, bool $optionalHour = false): string
    {
        $sec = $var % 60;
        $var = ($var - $sec) / 60;
        $min = $var % 60;
        $heu = ($var - $min) / 60;
        
        if ($heu < 10) {
            $heu = "0" . $heu;
        }
        if ($min < 10) {
            $min = "0" . $min;
        }
        if ($sec < 10) {
            $sec = "0" . $sec;
        }
        
        if ($optionalHour && $heu == "00") {
            return "$min:$sec";
        }
        
        return "$heu:$min:$sec";
    }
    
    /**
     * Convertit une date en francais jj/mm/aaaa au format sql aaaa-mm-jj
     *
     * @param string $sDate la date à convertir
     *
     * @return string la date convertie. chaîne vide si la conversion a échouée
     * @throws Exception
     */
    public static function dateSQL(string $sDate): ?string
    {
        if (empty($sDate)) return null;
        
        $aParts = explode('/', $sDate);
        
        if (count($aParts) !== 3) {
            $aParts = explode('-', $sDate);
            if (count($aParts) !== 3) {
                return null;
            }
        }
        
        [$day, $month, $year] = [$aParts[0], $aParts[1], $aParts[2]];
        
        if (intval($day) < 10) $day = "0" . intval($day);
        if (intval($month) < 10) $month = "0" . intval($month);
        
        if ($month > 12) {
            throw  new Exception("Date invalide : Mois > 12");
        }
        
        $year_and_hours = explode(" ", $year);
        if (count($year_and_hours) > 1) {
            throw  new Exception("Date invalide : Année");
            
        }
        
        if (intval($year) > 0 && intval($month) > 0 && intval($day) > 0) {
            return "{$year}-{$month}-{$day}";
        }
        
        return null;
        
    }
    
    public static function secondsToTime($seconds): string
    {
        $t = round($seconds);
        
        return sprintf('%02d:%02d:%02d', ($t / 3600), ($t / 60 % 60), $t % 60);
    }
    
    public static function shorten($str, $length = 50, $marker = "…"): string
    {
        
        return mb_strimwidth($str, 0, $length, $marker, "utf-8");
        
    }
    
    public static function isMobile(): bool
    {
        
        $test = new \Detection\MobileDetect();
        
        return $test->isMobile();
    }
    
    public static function isApple(): bool
    {
        
        $user_os = self::getOS();
        
        if (in_array($user_os, ['iPhone', 'iPod', 'iPad'])) {
            return true;
        }
        
        return false;
        
    }
    
    public static function getOS()
    {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }
        
        $user_agent  = $_SERVER['HTTP_USER_AGENT'];
        $os_array    = [
            '/windows nt 10/i'      => 'Windows 10',
            '/windows nt 6.3/i'     => 'Windows 8.1',
            '/windows nt 6.2/i'     => 'Windows 8',
            '/windows nt 6.1/i'     => 'Windows 7',
            '/windows nt 6.0/i'     => 'Windows Vista',
            '/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     => 'Windows XP',
            '/windows xp/i'         => 'Windows XP',
            '/windows nt 5.0/i'     => 'Windows 2000',
            '/windows me/i'         => 'Windows ME',
            '/win98/i'              => 'Windows 98',
            '/win95/i'              => 'Windows 95',
            '/win16/i'              => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i'        => 'Mac OS 9',
            '/linux/i'              => 'Linux',
            '/ubuntu/i'             => 'Ubuntu',
            '/iphone/i'             => 'iPhone',
            '/ipod/i'               => 'iPod',
            '/ipad/i'               => 'iPad',
            '/android/i'            => 'Android',
            '/blackberry/i'         => 'BlackBerry',
            '/webos/i'              => 'Mobile',
        ];
        $os_platform = "Unknown OS Platform";
        
        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $os_platform = $value;
            }
        }
        
        return $os_platform;
    }
    
    public static function getFileLastModifiedDate(string $path)
    {
        
        //Clear the file status cache
        if (function_exists("clearstatcache")) {
            clearstatcache();
        }
        //Get the last modified time using the filemtime function.
        //This function will return a Unix timestamp.
        return filemtime($path);
    }
    
    public static function generateChar($length): string
    {
        $characters   = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $index        = random_int(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        
        return $randomString;
    }
    
    public static function hasUppercase($string): bool
    {
        return (bool)preg_match('/[A-Z]/', $string);
    }
    
    /**
     * @throws Exception
     */
    public static function getBrowserUID(): string
    {
        
        $os      = self::getOS();
        $browser = self::getBrowser();
        $ip      = self::getIP();
        
        return base64_encode(str_pad(random_int(0, 9999), 4, 0, STR_PAD_LEFT) . time() . self::sanitize(str_replace(" ", "", ($os . $ip . $browser))));
        
    }
    
    /**
     * Déterminer le navigateur courant à partir de l'user agent.
     *
     * @return string|false Nom du navigateur ou false si inconnu.
     */
    public static function getBrowser()
    {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }
        $user_agent    = $_SERVER['HTTP_USER_AGENT'];
        $browser_array = [
            '/msie/i'      => 'Internet Explorer',
            '/trident/i'   => 'Internet Explorer',
            '/firefox/i'   => 'Firefox',
            '/safari/i'    => 'Safari',
            '/chrome/i'    => 'Chrome',
            '/edge/i'      => 'Edge',
            '/opera/i'     => 'Opera',
            '/netscape/i'  => 'Netscape',
            '/maxthon/i'   => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i'    => 'Handheld Browser',
        ];
        $browser       = "Unknown Browser";
        
        foreach ($browser_array as $regex => $value) {
            
            if (preg_match($regex, $user_agent)) {
                $browser = $value;
            }
        }
        
        return $browser;
    }
    
    /**
     * Récupérer l'adresse IP de la requête.
     *
     * @return string Adresse IP ou chaîne vide.
     */
    public static function getIP(): string
    {
        if (isset($_SERVER["REMOTE_ADDR"])) {
            return self::sanitize($_SERVER["REMOTE_ADDR"]);
        }
        return "";
    }
    
    /**
     * Supprimer les caractères spéciaux d'une chaîne.
     *
     * @param mixed|null $str     Chaîne source.
     * @param array      $exclude Caractères à conserver.
     *
     * @return string Chaîne nettoyée.
     */
    public static function sanitize($str = null, array $exclude = []): string
    {
        if (!is_string($str) || empty($str)) {
            return "";
        }
        $remove         = ["{", "}", "[", "]", "\\", "\"", "'", "$", "#", "%", "`", "(", ")", "&", "@", "<", ">", ";"];
        $originalRemove = $remove;
        foreach ($exclude as $item) {
            foreach ($originalRemove as $key => $val) {
                if ($val === $item) {
                    unset($remove[$key]);
                }
            }
        }
        return str_replace($remove, "", $str) ?? "";
    }
    
    /**
     * Formater une taille disque en unité lisible.
     *
     * @param mixed $size      Taille en octets.
     * @param int   $precision Précision d'arrondi.
     *
     * @return string Taille formatée.
     */
    public static function formatDiskVolume($size, $precision = 2)
    {
        if ($size == 0) return '';
        // return $size;
        $base     = log($size, 1024);
        $suffixes = ['o', 'Mo', 'Go', 'To'];
        
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }
    
    /**
     * Convertir une taille en octets vers une représentation lisible.
     *
     * @param int $bytes     Taille en octets.
     * @param int $precision Précision d'arrondi.
     *
     * @return string Taille formatée.
     */
    public static function humanFileSize(int $bytes, int $precision = 0): string
    {
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        
        // Uncomment one of the following alternatives
        $bytes /= 1024 ** $pow;
        //$bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
        
    }
    
    /**
     * Vérifier si un serveur est accessible sur le port 80.
     *
     * @param string $domain Domaine à vérifier.
     *
     * @return bool true si accessible, false sinon.
     */
    public static function checkServerStatus(string $domain): bool
    {
        
        $errstr = "";
        try {
            $file = fsockopen($domain, 80, $errno, $errstr, 10);
            if (!$file) {
                return false;
            }
            fclose($file);
        } catch (Exception $ex) {
            if ($errno) {
                switch ($errno) {
                    case SOCKET_ECONNREFUSED:
                        return true;
                    default:
                }
                \Logger::error(__METHOD__ . " [$domain] > error $errno : $errstr");
                
            } else {
                \Logger::error($ex);
            }
            return false;
        }
        
        return true;
        
    }
    
    public static function getBodyContent(string $txt): string
    {
        // get the string between <body> and </body> tags
        preg_match('/<body.*?>(.*?)<\/body>/s', $txt, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return $txt;
    }
    
    public static function getHeadContent(string $txt): string
    {
        // get the string between <body> and </body> tags
        preg_match('/<head.*?>(.*?)<\/head>/s', $txt, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return $txt;
    }
    
    public static function getTheme(string $txt): string|null
    {
        preg_match('/<body[^>]*class="([^"]*)"/', $txt, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return null;
    }
    
    public static function getImageContent(string $txt, string $editeur_image_url): array|null
    {
        // Load the HTML into DOMDocument
        $doc = new DOMDocument();
        @$doc->loadHTML($txt);
        // Create a new DOMXPath object
        $xpath = new DOMXPath($doc);
        // Query for images in .attachments
        $images              = [];
        $imagesInAttachments = $xpath->query('//div[@class="attachments"]//img');
        
        // Add images from .attachments to the array
        foreach ($imagesInAttachments as $img) {
            $images[] = $img;
        }
        
        // Query for all images in the document
        $allImages = $xpath->query('//img');
        
        // Add images from the rest of the document, avoiding duplicates
        foreach ($allImages as $img) {
            if (!$imagesInAttachments->length || !$xpath->evaluate('ancestor::div[@class="attachments"]', $img)->length) {
                $images[] = $img;
            }
        }
        
        // Function to get image details
        function getImageDetails($img): array|null
        {
            $src       = $img->getAttribute('src');
            $imageInfo = @getimagesize($src);
            
            if ($imageInfo) {
                return [
                    'src'    => $src,
                    'width'  => $imageInfo[0],
                    'height' => $imageInfo[1],
                    'mime'   => $imageInfo['mime'],
                ];
            } else if (pathinfo($src, PATHINFO_EXTENSION) === 'svg') { // Pour les SVG car @getimagesize ne marche pas pour les svg
                $svgContent = @file_get_contents($src);
                if ($svgContent) {
                    $svg = @simplexml_load_string($svgContent);
                    if ($svg) {
                        $attributes = $svg->attributes();
                        $width      = (string)$attributes->width;
                        $height     = (string)$attributes->height;
                        if ($width && $height) {
                            return [
                                'src'    => $src,
                                'width'  => (int)$width,
                                'height' => (int)$height,
                                'mime'   => 'image/svg+xml',
                            ];
                        }
                    }
                }
            }
            
            return null;
        }
        
        // Iterate over the images and find one that meets the size criteria
        $minWidth      = 400;
        $minHeight     = 400;
        $selectedImage = null;
        
        foreach ($images as $img) {
            $imageDetails = getImageDetails($img);
            if ($imageDetails && $imageDetails['width'] >= $minWidth && $imageDetails['height'] >= $minHeight) {
                $selectedImage = $imageDetails;
                break;
            }
        }
        // Si pas d'images dans le message alors on regarde pour prendre l'image de l'éditeur
        if (empty($selectedImage)) {
            $selectedImage = [
                'src'    => PUBLIC_ROOT . '/public/assets/img/nouveauprojet-ship400.png',
                'width'  => 400,
                'height' => 400,
                'mime'   => 'image/png',
            ];
        }
        // Si pas d'image de l'éditeur alors on prende celle de nouveauprojet
        if (empty($selectedImage)) {
            $selectedImage = [
                'src'    => PUBLIC_ROOT . '/public/assets/img/nouveauprojet-ship400.png',
                'width'  => 400,
                'height' => 400,
                'mime'   => 'image/png',
            ];
        }
        
        return $selectedImage;
    }
    
    public static function getStyleContent(string $css): string
    {
        // Extraire le contenu CSS à l'intérieur de la balise <style>
        preg_match('/<style\b[^>]*>(.*?)<\/style>/is', $css, $matches);
        if (!empty($matches[1])) {
            $css_content = $matches[1];
            // Remplacer les sélecteurs `body` par `#container-message`
            $css_content = preg_replace('/\bbody\b/', '#container-message', $css_content);
            // Décomposer le CSS en règles individuelles
            $rules = [];
            preg_match_all('/([^{]+)\s*\{\s*([^}]*)\s*\}/', $css_content, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $selector   = trim($match[1]);
                $properties = trim($match[2]);
                $rules[]    = ['selector' => $selector, 'properties' => $properties];
            }
            // Traiter chaque règle individuellement
            // ATTENTION : On utilise un pointeur pour modifier le tableau $rules directement
            // Ne plus utiliser $rule après car il est un pointeur ce qui modifie $rules
            foreach ($rules as &$rule) {
                // Ajout de #container-message avant certains sélecteurs
                $selecteurs_a_modifier = ['header', 'footer', 'main', 'h1', 'h2', 'a', 'img'];
                
                foreach ($selecteurs_a_modifier as $selecteur) {
                    if (strpos($rule['selector'], $selecteur) !== false && strpos($rule['selector'], '#container-message') === false) {
                        $rule['selector'] = '#container-message ' . $rule['selector'];
                    }
                }
            }
            // Recomposer le CSS modifié
            $css_content_modifie = '';
            foreach ($rules as $rule_parsed) {
                $css_content_modifie .= $rule_parsed['selector'] . ' {' . $rule_parsed['properties'] . '} ';
            }
            // Ajouter le CSS personnalisé pour override les styles de tabler
            $custom_css = <<<CSS
                #container-message .footer {
                    background: unset;
                    color: unset;
                    margin: unset;
                    padding: unset;
                    border: unset;
                    box-sizing: unset;
                }

                #container-message .footer img {
                    width: fit-content !important;
                }
                CSS;
            // Ajouter le CSS personnalisé au debut du contenu CSS existant
            $custom_css .= $css_content_modifie;
            // Remettre le CSS modifié dans la balise <style>
            return "<style>" . $custom_css . "</style>";
        }
        
        return $css;
    }
    
    /**
     * Détecter la présence d'une image en base64 dans un contenu.
     *
     * @param string $content Contenu à analyser.
     *
     * @return bool true si une image base64 est détectée, false sinon.
     */
    public static function containsBase64Image(string $content): bool
    {
        // Regex pattern to match base64 encoded images
        $pattern = '/data:image\/(png|jpg|jpeg|gif);base64,([A-Za-z0-9+\/=]+)/';
        return preg_match($pattern, $content) === 1;
    }
}
