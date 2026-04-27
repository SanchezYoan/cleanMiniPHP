<?php

/**
 * Gérer le cookie d'authentification "remember me".
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class Cookie
{
    
    
    /**
     * Identifiant utilisateur extrait du cookie.
     *
     * @var int|null
     */
    private static $userId;
    
    /**
     * Jeton d'authentification stocké en base.
     *
     * @var string|null
     */
    private static $token = "";
    
    /**
     * Hash complet du cookie.
     *
     * @var string|null
     */
    private static $hashedCookie = "";
    
    /**
     * Récupérer l'identifiant utilisateur extrait.
     *
     * @return int Identifiant utilisateur.
     */
    public static function getUserId(): int
    {
        return (int)self::$userId;
    }
    
    /**
     * Extraire et valider le cookie d'authentification.
     *
     * Effets de bord : lit en base et peut supprimer le cookie en cas d'échec.
     *
     * @return bool true si le cookie est valide.
     */
    public static function isCookieValid(): bool
    {
        
        // "auth" or "remember me" cookie
        if (empty($_COOKIE['auth'])) {
            return false;
        }
        
        // check the count before using explode
        $cookie_auth = explode(':', $_COOKIE['auth']);
        if (count($cookie_auth) !== 3) {
            self::remove();
            
            return false;
        }
        
        [$encryptedUserId, self::$token, self::$hashedCookie] = $cookie_auth;
        
        // Remember? $hashedCookie was generated from the original user Id, NOT from the encrypted one.
        self::$userId = Encryption::decryptId($encryptedUserId);
        
        if (self::$hashedCookie === hash('sha256', self::$userId . ':' . self::$token . Config::get('COOKIE_SECRET_KEY')) && !empty(self::$token) && !empty(self::$userId)) {
            
            $db = Database::openConnection();
            $db->prepare("SELECT id, cookie_token FROM users
                      WHERE id = :id AND cookie_token = :cookie_token LIMIT 1")
               ->bindValue(':id', self::$userId)
               ->bindValue(':cookie_token', self::$token)
               ->execute();
            
            $isValid = $db->countRows() === 1;
            
        } else {
            
            $isValid = false;
        }
        
        if (!$isValid) {
            
            Logger::notice("COOKIE : user " . self::$userId . " is trying to login using invalid cookie_token : " . self::$token, __FILE__, __LINE__);
            self::remove(self::$userId);
        }
        
        return $isValid;
    }
    
    /**
     * Supprimer le cookie en base et dans le navigateur.
     *
     * Effets de bord : mise à jour en base et modification des cookies HTTP.
     *
     * @param int|User|null $user Utilisateur ciblé ou identifiant.
     *
     * @return void
     */
    public static function remove(int|User|null $user = null): void
    {
        $userId = null;
        
        if ($user instanceof User) {
            $userId = $user->getId();
        } else if (is_int($user)) {
            $userId = $user;
        }
        
        if (!empty($userId)) {
            
            $db = Database::openConnection();
            $db->prepare("UPDATE users SET cookie_token = NULL WHERE id = :id")
               ->bindValue(":id", $userId);
            $result = $db->execute();
            
            if (!$result) {
                Logger::notice("COOKIE : Couldn't remove cookie from the database for user ID: " . $userId, __FILE__, __LINE__);
            }
        }
        
        self::$userId = self::$token = self::$hashedCookie = null;
        
        if (headers_sent()) {
            return;
        }

        if (isset($_COOKIE['auth'])) {
            // How to kill/delete a cookie in a browser?
            setcookie(
                "auth",
                false,
                time() - (60 * 60 * 24 * 365 * 4000),
                Config::get('COOKIE_PATH'),
                Config::get('COOKIE_DOMAIN'),
                Config::get('COOKIE_SECURE'),
                Config::get('COOKIE_HTTP')
            );
        }
        
        if (isset($_COOKIE[session_id()])) {
            // How to kill/delete a cookie in a browser?
            setcookie(
                session_id(),
                false,
                time() - (60 * 60 * 24 * 365 * 4000),
                Config::get('COOKIE_PATH'),
                Config::get('COOKIE_DOMAIN'),
                Config::get('COOKIE_SECURE'),
                Config::get('COOKIE_HTTP')
            );
        }
    }
    
    /**
     * Réinitialiser le cookie d'authentification.
     *
     * Effets de bord : mise à jour en base et écriture du cookie navigateur.
     *
     * @param User $user Utilisateur ciblé.
     *
     * @return void
     */
    public static function reset(User $user): void
    {
        // Pas de chaine session ID dans les urls.
        
        self::$userId = $user->getId();
        self::$token  = hash('sha256', mt_rand());
        $db           = Database::openConnection();
        $db->prepare("UPDATE users SET cookie_token = :cookie_token WHERE id = :id")
           ->bindValue(":cookie_token", self::$token)
           ->bindValue(":id", self::$userId);
        $result = $db->execute();
        
        if (!$result) {
            Logger::notice("COOKIE : Couldn't remove cookie from the database for user ID: " . self::$userId, __FILE__, __LINE__);
        }
        
        // generate cookie string(remember me)
        // Don't expose the original user id in the cookie, Encrypt It!
        $cookieFirstPart = Encryption::encryptId(self::$userId) . ':' . self::$token;
        
        // $hashedCookie generated from the original user Id, NOT from the encrypted one.
        self::$hashedCookie = hash('sha256', self::$userId . ':' . self::$token . Config::get('COOKIE_SECRET_KEY'));
        $authCookie         = $cookieFirstPart . ':' . self::$hashedCookie;
        
        if (!headers_sent()) {
            setcookie(
                "auth",
                $authCookie,
                time() + Config::get('REMEMBER_ME_COOKIE_LIFETIME'),
                Config::get('COOKIE_PATH'),
                Config::get('COOKIE_DOMAIN'),
                Config::get('COOKIE_SECURE'),
                Config::get('COOKIE_HTTP')
            );
        }
        
    }
    
}
