<?php

/**
 * Gérer la session PHP.
 */
class Session
{
    /**
     * Handler personnalisé pour la régénération d'ID de session.
     *
     * @var callable|null
     */
    private static $regenerateHandler = null;

    /**
     * Initialiser la session et sécuriser les cookies.
     *
     * @return void
     */
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $params = session_get_cookie_params();
            session_set_cookie_params([
                'lifetime' => $params['lifetime'],
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    /**
     * Régénérer l'identifiant de session.
     *
     * @param bool $deleteOldSession Supprimer l'ancienne session.
     *
     * @return bool true si la régénération réussit.
     */
    public static function regenerate(bool $deleteOldSession = false): bool
    {
        if (is_callable(self::$regenerateHandler)) {
            return (bool)call_user_func(self::$regenerateHandler, $deleteOldSession);
        }

        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Définir un handler custom pour la régénération d'ID.
     *
     * @param callable|null $handler Handler personnalisé.
     *
     * @return void
     */
    public static function setRegenerateHandler(?callable $handler): void
    {
        self::$regenerateHandler = $handler;
    }
    
    /**
     * Stocker une valeur en session.
     *
     * @param string $key   Clé de session.
     * @param mixed  $value Valeur à stocker.
     *
     * @return void
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Récupérer une valeur en session.
     *
     * @param string $key Clé de session.
     *
     * @return mixed Valeur stockée ou null.
     */
    public static function get(string $key): mixed
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }
    
    /**
     * Supprimer une clé de session.
     *
     * @param string $key Clé à supprimer.
     *
     * @return void
     */
    public static function delete(string $key): void
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Détruire complètement la session.
     *
     * @return void
     */
    public static function destroy(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }
    
    /**
     * Vérifier si un administrateur est connecté.
     *
     * @return bool true si la session admin existe.
     */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['admin']);
    }
    
    /**
     * Vérifier si l'administrateur connecté est super admin.
     *
     * @return bool true si le rôle est adminsu.
     */
    public static function isAdmin(): bool
    {
        //Logger::debug(var_export($_SESSION, true));
        return isset($_SESSION['admin']) && $_SESSION['admin']->getLevel() === "adminsu";
    }
}
