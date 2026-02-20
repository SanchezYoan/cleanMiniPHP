<?php

/**
 * Gestion simple de la langue courante de l'application.
 *
 * Ce service expose la langue active et offre une validation élémentaire
 * sur un jeu restreint de codes langue autorisés.
 */
class Lang
{
    /**
     * Instance singleton du service de langue.
     */
    private static ?Lang  $instance = null;

    /**
     * Code langue actif (par défaut en anglais).
     */
    private static string $current  = "en";
    
    /**
     * Langues autorisées et leur libellé.
     *
     * @var array<string, string>
     */
    private static array $allowedLanguage = [
        "en" => "English",
        "fr" => "Français",
    ];
    
    private function __construct()
    {
        // Private constructor to prevent direct instantiation
    }
    
    /**
     * Récupérer l'instance unique du gestionnaire de langue.
     */
    public static function getInstance(): Lang
    {
        if (self::$instance === null) {
            self::$instance = new Lang();
        }
        
        return self::$instance;
    }
    
    /**
     * Définir la langue si elle fait partie des codes autorisés.
     *
     * @param string $lang Code langue (ex. "fr", "en").
     */
    public static function set(string $lang): void
    {
        if (isset(self::$allowedLanguage[$lang])) {
            self::$current = $lang;
        }
    }
    
    /**
     * Récupérer la langue courante ou tester l'égalité avec un code fourni.
     *
     * @param string|null $lang Code à comparer (optionnel).
     *
     * @return string|bool Code langue actif, ou booléen si comparaison fournie.
     */
    public static function current(?string $lang = null): string|bool
    {
        if (null !== $lang) {
            return self::$current === $lang;
        }
        return self::$current;
    }
}
