<?php

namespace NGine;

/**
 * Fournit l'accès aux chaînes de traduction.
 */
class Translate
{
    /**
     * Sources de traduction par langue.
     *
     * @var array<string, string>
     */
    private static array $sources = [
        "fr" => APP . "/lang/fr.php",
        "en" => APP . "/lang/en.php",
    ];
    
    /**
     * Récupérer une traduction par référence.
     *
     * @param string      $reference Clé de traduction.
     * @param string|null $lang      Langue forcée (ISO 639-1).
     *
     * @return string Traduction ou clé si introuvable.
     */
    public static function get(string $reference, ?string $lang = null): string
    {
        // If no language is provided, get headers
        if (empty($lang)) {
            $headers     = apache_request_headers();
            $languages   = $headers['Accept-Language'] ?? "en,en-Us";
            $requestLang = explode(",", $languages)[0];
            // get only the first 2 chars
            $requestLang = substr($requestLang, 0, 2);
        } else { // If language is provided, use it
            $requestLang = $lang;
        }
        // Check if the language exists in the sources array
        if (!isset(self::$sources[$requestLang]) && !array_key_exists($lang, self::$sources)) {
            $lang = "en";
        }
        if (!empty($lang)) {
            $requestLang = $lang;
        }
        $tanslations = include self::$sources[$requestLang];
        return $tanslations[$reference] ?? $reference;
    }
    
    /**
     * Récupérer la langue courante déduite des headers.
     *
     * @return string Langue ISO 639-1.
     */
    public static function currentLang(): string
    {
        $headers     = apache_request_headers();
        $languages   = $headers['Accept-Language'] ?? "en,en-Us";
        $requestLang = explode(",", $languages)[0];
        $requestLang = substr($requestLang, 0, 2);
        if (!isset(self::$sources[$requestLang])) {
            $requestLang = "en";
        }
        
        return $requestLang;
    }
}
