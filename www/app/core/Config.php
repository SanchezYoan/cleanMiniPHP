<?php

/**
 * Gérer la configuration applicative.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class Config
{
    
    /**
     * Tableau des configurations chargées.
     *
     * @var array<string, mixed>
     */
    public static array $config = [];
    
    /**
     * Tableau des configurations JavaScript.
     *
     * @var array<string, mixed>
     */
    public static array $jsConfig = [];
    
    /**
     * Récupérer une valeur de configuration.
     *
     * @param string $key Clé dotée (ex: "DB.HOST").
     *
     * @return array|string|null|false Valeur ou false en cas d'erreur.
     */
    public static function get(string $key): false|array|string|null
    {
        
        try {
            if (!self::$config) {
                
                $config_file = APP . '/config/config.php';
                
                if (!file_exists($config_file)) {
                    throw new \RuntimeException("Configuration file doesn't exist");
                }
                
                self::$config = require $config_file . "";
            }
            
            if (empty($key)) {
                return self::$config;
            }
            
            if (isset(self::$config[$key])) {
                return self::$config[$key];
            }
            
            $parts = explode('.', $key);
            $arr   = self::$config;
            
            foreach ($parts as $part) {
                $arr = $arr[$part] ?? "";
            }
            
            return $arr;
            
        } catch (Exception $ex) {
            error_log($ex->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer une configuration JavaScript.
     *
     * @param string $key Clé de configuration ou vide pour tout retourner.
     *
     * @return array|string|null Valeur ou tableau complet.
     *
     * @throws Exception Si le fichier de configuration est introuvable.
     */
    public static function getJsConfig(string $key = ""): array|string|null
    {
        
        if (!self::$jsConfig) {
            self::loadJsConfig();
        }
        
        if (empty($key)) {
            return self::$jsConfig;
        }
        
        return self::$jsConfig[$key] ?? null;
    }
    
    /**
     * Charger les configurations JavaScript depuis le fichier dédié.
     *
     * @return void
     *
     * @throws Exception Si le fichier de configuration est introuvable.
     */
    private static function loadJsConfig(): void
    {
        
        if (!self::$jsConfig) {
            
            $config_file = APP . '/config/javascript.php';
            
            if (!file_exists($config_file)) {
                throw new Exception("JavaScript Configuration file doesn't exist");
            }
            
            self::$jsConfig = require $config_file . "";
        }
    }
    
    /**
     * Ajouter une configuration JavaScript.
     *
     * @param string $key   Clé de configuration.
     * @param mixed  $value Valeur à exposer.
     *
     * @return void
     *
     * @throws Exception Si le fichier de configuration est introuvable.
     */
    public static function addJsConfig(string $key, mixed $value): void
    {
        if (!self::$jsConfig) {
            self::loadJsConfig();
        }
        self::$jsConfig[$key] = $value;
    }
    

}
