<?php

/**
 * Chiffrer et déchiffrer des données applicatives.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class Encryption
{
    
    /**
     * Algorithme de chiffrement symétrique.
     */
    public const CIPHER = 'aes-256-cbc';
    
    /**
     * Fonction de hachage pour HMAC.
     */
    public const HASH_FUNCTION = 'sha256';
    
    /**
     * Ressource OpenSSL de clé privée.
     *
     * @var resource|OpenSSLAsymmetricKey|null
     */
    private $privateKeyResource;
    /**
     * Clé privée en clair.
     *
     * @var string|null
     */
    private $privateKey;
    /**
     * Clé publique en clair.
     *
     * @var string|null
     */
    private $publicKey;
    
    /**
     * Initialiser les clés publique/privée à partir du système de fichiers.
     */
    public function __construct()
    {
        $this->privateKey = file_get_contents(APP . '/keys/AppPrivateKey.key');
        $this->publicKey  = file_get_contents(APP . '/keys/AppPublicKey.key');
    }
    
    /**
     * Chiffrer un identifiant en base courte.
     *
     * @param int|string $id Identifiant numérique.
     *
     * @return string Identifiant chiffré.
     * @throws Exception Si la génération de caractères échoue.
     * @see    http://kvz.io/blog/2009/06/10/create-short-ids-with-php-like-youtube-or-tinyurl/
     */
    public static function encryptId($id): string
    {
        if (empty($id)) {
            return $id;
        }
        $encryptId = "";
        $chars     = self::getCharacters();
        $base      = strlen($chars);
        $id        = ((int)$id * 9518436) + 1142;
        
        for ($t = ($id !== 0 ? floor(log($id, $base)) : 0); $t >= 0; $t--) {
            $bcp       = bcpow($base, $t);
            $a         = floor($id / $bcp) % $base;
            $encryptId .= $chars[$a];
            $id        -= ($a * $bcp);
        }
        
        return $encryptId;
    }
    
    /**
     * Générer la table de caractères pour chiffrement/déchiffrement.
     *
     * @return string Chaîne de caractères triée.
     * @throws Exception Si la clé de hachage est invalide.
     */
    private static function getCharacters(): string
    {
        
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        
        $i = str_split($chars);
        
        $key_hash = hash('sha256', Config::get('ENCRYPTION.HASH_KEY'));
        $key_hash = (strlen($key_hash) < strlen($chars) ? hash('sha512', Config::get('ENCRYPTION.HASH_KEY')) : $key_hash);
        
        for ($n = 0, $nMax = strlen($chars); $n < $nMax; $n++) {
            $p[] = $key_hash[$n];
        }
        
        array_multisort($p, SORT_DESC, $i);
        return implode($i);
    }
    
    /**
     * Déchiffrer un identifiant chiffré.
     *
     * @param string|null $id Identifiant chiffré.
     *
     * @return int|null Identifiant numérique.
     * @throws Exception Si la génération de caractères échoue.
     */
    public static function decryptId(?string $id = null): ?int
    {
        
        if (empty($id)) {
            return $id;
        }
        
        $decryptId = 0;
        $chars     = self::getCharacters();
        $base      = strlen($chars);
        $len       = strlen($id) - 1;
        
        
        for ($t = $len; $t >= 0; $t--) {
            $bcp       = (int)bcpow($base, $len - $t);
            $decryptId = $decryptId + strpos($chars, $id[$t]) * $bcp;
        }
        
        
        return ((int)$decryptId - 1142) / 9518436;
    }
    
    
    /**
     * Chiffrer une chaîne via AES + HMAC.
     *
     * @param string|null $plain Texte en clair.
     *
     * @return string|null Chaîne chiffrée (hex) ou null si entrée vide.
     * @throws RuntimeException Si les fonctions OpenSSL manquent.
     */
    public static function encrypt(?string $plain = null): ?string
    {
        if (empty($plain)) {
            return $plain;
        }
        if (!function_exists('openssl_cipher_iv_length') ||
            !function_exists('openssl_encrypt') ||
            !function_exists('random_bytes')) {
            throw new \RuntimeException("Encryption function doesn't exist");
        }
        
        // generate initialization vector,
        // this will make $iv different every time,
        // so, encrypted string will be also different.
        $iv_size = openssl_cipher_iv_length(self::CIPHER);
        $iv      = random_bytes($iv_size);
        // generate key for authentication using ENCRYPTION_KEY & HMAC_SALT
        $key = mb_substr(hash(self::HASH_FUNCTION, Config::get('ENCRYPTION.KEY') . Config::get('ENCRYPTION.HMAC_SALT')), 0, 32, '8bit');
        
        // append initialization vector
        $encrypted_string = openssl_encrypt($plain, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        $ciphertext       = $iv . $encrypted_string;
        
        // apply the HMAC
        $hmac = hash_hmac('sha256', $ciphertext, $key);
        
        return bin2hex($hmac . $ciphertext);
        
    }
    
    /**
     * Déchiffrer une chaîne chiffrée.
     *
     * @param string $ciphertext Chaîne chiffrée hexadécimale.
     *
     * @return string|false Chaîne déchiffrée ou false si HMAC invalide.
     */
    public static function decrypt($ciphertext)
    {
        
        if (empty($ciphertext) || !ctype_xdigit($ciphertext)) {
            return $ciphertext;
        }
        
        try {
            
            if (!function_exists('openssl_cipher_iv_length') ||
                !function_exists('openssl_decrypt')) {
                throw new \RuntimeException("Encryption function doesn't exist");
            }
            $ciphertext = hex2bin($ciphertext);
            
            
            // generate key used for authentication using ENCRYPTION_KEY & HMAC_SALT
            $key = mb_substr(hash(self::HASH_FUNCTION, Config::get('ENCRYPTION_KEY') . Config::get('HMAC_SALT')), 0, 32, '8bit');
            
            // split cipher into: hmac, cipher & iv
            $macSize   = 64;
            $hmac      = mb_substr($ciphertext, 0, $macSize, '8bit');
            $iv_cipher = mb_substr($ciphertext, $macSize, null, '8bit');
            
            // generate original hmac & compare it with the one in $ciphertext
            $originalHmac = hash_hmac('sha256', $iv_cipher, $key);
            if (!self::hashEquals($hmac, $originalHmac)) {
                return false;
            }
            
            // split out the initialization vector and cipher
            $iv_size = openssl_cipher_iv_length(self::CIPHER);
            $iv      = mb_substr($iv_cipher, 0, $iv_size, '8bit');
            $cipher  = mb_substr($iv_cipher, $iv_size, null, '8bit');
            
            return openssl_decrypt($cipher, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        } catch (Exception $e) {
            Logger::error($e);
        }
        return $ciphertext;
    }
    
    /**
     * Comparer deux HMAC de manière résistante aux attaques temporelles.
     *
     * @param string $hmac    HMAC de la donnée chiffrée.
     * @param string $compare HMAC attendu.
     *
     * @return bool true si identiques.
     * @see    https://github.com/sarciszewski/php-future/blob/bd6c91fb924b2b35a3e4f4074a642868bd051baf/src/Security.php#L36
     */
    private static function hashEquals(string $hmac, string $compare): bool
    {
        
        if (function_exists('hash_equals')) {
            return hash_equals($hmac, $compare);
        }
        
        // if hash_equals() is not available,
        // then use the following snippet.
        // It's equivalent to hash_equals() in PHP 5.6.
        $hashLength    = mb_strlen($hmac, '8bit');
        $compareLength = mb_strlen($compare, '8bit');
        
        if ($hashLength !== $compareLength) {
            return false;
        }
        
        $result = 0;
        for ($i = 0; $i < $hashLength; $i++) {
            $result |= (ord($hmac[$i]) ^ ord($compare[$i]));
        }
        
        return $result === 0;
    }
    
    /**
     * Comparer un mot de passe en clair avec un hash SHA-256.
     *
     * @param string $pwd1         Mot de passe en clair.
     * @param string $databaseHash Hash stocké en base.
     *
     * @return bool true si identique.
     */
    public static function passwordCheck(string $pwd1, string $databaseHash): bool
    {
        $encPwd = self::passwordHash($pwd1);
        
        return $encPwd === $databaseHash;
    }
    
    /**
     * Hacher un mot de passe via SHA-256.
     *
     * @param string|null $password Mot de passe en clair.
     *
     * @return string|null Hash SHA-256 ou null si vide.
     */
    public static function passwordHash($password): ?string
    {
        return empty($password) ? null : hash('sha256', $password);
    }
    
    /**
     * Vérifier un mot de passe via hash Bcrypt peppered.
     *
     * @param string $pwd1         Mot de passe en clair.
     * @param string $databaseHash Hash Bcrypt stocké.
     *
     * @return bool true si valide.
     */
    public static function passwordCheck2(string $pwd1, string $databaseHash): bool
    {
        $pepper = Config::get("ENCRYPTION.HASH_PEPPER");
        $pwdPeppered = hash_hmac("sha256", $pwd1, $pepper);
        return password_verify($pwdPeppered, $databaseHash);
    }
    
    /**
     * Générer un hash Bcrypt peppered.
     *
     * @param string $password Mot de passe en clair.
     *
     * @return string|null Hash Bcrypt.
     */
    public static function passwordHash2($password): ?string
    {
        $pepper       = Config::get("ENCRYPTION.HASH_PEPPER");
        $pwd_peppered = hash_hmac("sha256", $password, $pepper);
        return password_hash($pwd_peppered, PASSWORD_BCRYPT, ["cost" => Config::get("ENCRYPTION.HASH_COST_FACTOR")]);
    }
    
    /**
     * Créer la paire de clés et la stocker sur disque.
     *
     * Effets de bord : écrit des fichiers dans /app/keys.
     *
     * @return bool true si la paire est créée.
     */
    public function createKeyPairs(): bool
    {
        $privateKey    = file_get_contents(APP . '/keys/AppPrivateKey.key');
        $alreadyExists = !empty($privateKey);
        
        if (!$alreadyExists) {
            
            $this->privateKeyResource = openssl_pkey_new(
                [
                    'private_key_bits' => 2048,
                    'private_key_type' => OPENSSL_KEYTYPE_RSA,
                ]);
            // Save the private key to a file. Never share this file with anyone.
            // See https://serverfault.com/questions/9708/what-is-a-pem-file-and-how-does-it-differ-from-other-openssl-generated-key-file
            if (openssl_pkey_export_to_file($this->privateKeyResource, APP . '/keys/AppPrivateKey.key')) {
                $this->createPublicKey();
            }
        }
        
        return false;
        
    }
    
    /**
     * Générer la clé publique et l'enregistrer sur disque.
     *
     * Effets de bord : écrit un fichier et libère la ressource OpenSSL.
     *
     * @return void
     */
    private function createPublicKey(): void
    {
        
        // Generate the public key for the private key
        $privateKeyDetailsArray = openssl_pkey_get_details($this->privateKeyResource);
        
        // Save the public key to another file. Make this file available to anyone (especially anyone who wants to send you encrypted data).
        $fileOK = file_put_contents(APP . '/keys/AppPublicKey.key', $privateKeyDetailsArray['key']);
        
        // Free the key from memory.
        openssl_free_key($this->privateKeyResource);
        $this->privateKeyResource = null;
        
    }
    
    /**
     * Récupérer la clé publique.
     *
     * @return string|null Clé publique.
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }
    
    /**
     * Chiffrer un message avec une clé publique tierce.
     *
     * @param string $data      Données en clair.
     * @param string $publicKey Clé publique de destination.
     *
     * @return string Données chiffrées (hex).
     */
    public function publicKeyEncrypt(string $data, string $publicKey): string
    {
        $crypted = "";
        openssl_public_encrypt($data, $crypted, $publicKey);
        
        return bin2hex($crypted);
        
    }
    
    /**
     * Déchiffrer un message chiffré via notre clé privée.
     *
     * @param string $data Données chiffrées (hex).
     *
     * @return string Données en clair.
     */
    public function publicKeyDecrypt(string $data): string
    {
        $deCrypted = "";
        openssl_private_decrypt(hex2bin($data), $deCrypted, $this->privateKey);
        
        return $deCrypted;
        
    }
    
    
}