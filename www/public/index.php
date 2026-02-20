<?php
setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR@euro', 'fr_FR.utf8', 'fr-FR', 'fra');
date_default_timezone_set('UTC');
if (!defined("LOG_LEVEL")) {
    define('LOG_LEVEL', E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
}

define("BASE_DIR", dirname(__DIR__));
const APP = BASE_DIR . "/app";
const VENDOR_DIR = BASE_DIR . "/vendor";
const VIEWS = APP . "/views";
const ASSETS = BASE_DIR . "/public/assets";

require_once VENDOR_DIR . '/autoload.php';

if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle): bool
    {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}
if (!function_exists('str_contains_lower')) {
    function str_contains_lower($haystack, $needle): bool
    {
        return $needle !== '' && mb_strpos(strtolower($haystack), strtolower($needle)) !== false;
    }
}
$isCRON = isset($argv) && $argv[1] === "CRON";
$host = strtolower($_SERVER["HTTP_HOST"] ?? "");
define("DOMAIN", $host);
// TODO : Changer les urls prod/dev
if ($host === "cleanminiphp.fr") {
    define("ENV", "PROD");
    define("SUBDOMAIN", "");
    // TODO : Changer les urls prod/dev
    define("DOMAIN_API", "api.production.com");
} else {
    // TODO : Changer les urls prod/dev
    if ($host === "cleanminiphp.local") {
        define("ENV", "LOCAL");
        define("SUBDOMAIN", "");
        // TODO : Changer les urls prod/dev
        define("DOMAIN_API", "api.site.local");
    } else {
        return;
    }
}
// pour mesure le temps de chargement des pages
if (ENV == "LOCAL"){
    $depart = microtime(true);
}

$https = "https://";
define('PUBLIC_ROOT', "{$https}$host");
define('PUBLIC_ROOT_UNIV', "//$host");

// Debug croisé en local. Metre a jour le port xdebug en fonction des besoins
const XDEBUG = null;

Handler::register();
Session::init();
if (!$isCRON) {
    (new App())->run();
}


// pour afficher le temps de chargement des pages (en local uniquement)
if (ENV === 'LOCAL') {
    $fin   = microtime(true);
    $temps = $fin - $depart;
    
    // Récupère les headers de réponse qui vont être envoyés (poue viter d'afficher si c'est du json par exemple)
    $isHtml = false;
    foreach (headers_list() as $h) {
        if (stripos($h, 'Content-Type:') === 0 && stripos($h, 'text/html') !== false) {
            $isHtml = true;
            break;
        }
    }
    
    if ($isHtml) {
        echo "Script exécuté en " . number_format($temps, 3) . " sec";
    }
}
