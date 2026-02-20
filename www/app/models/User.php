<?php

/**
 * Représenter un utilisateur applicatif.
 *
 * Gère la persistance, l'authentification et l'état 2FA.
 */
class User extends Model
{
    /**
     * Rôle super administrateur.
     */
    public const ADMINSU = "adminsu";

    /**
     * Rôle administrateur.
     */
    public const ADMIN = "admin";

    /**
     * Rôle super utilisateur.
     */
    public const USERSU = "usersu";

    /**
     * Rôle utilisateur standard.
     */
    public const USER = "user";

    /**
     * Identifiant utilisateur.
     */
    private int $id;

    /**
     * Niveau de rôle.
     */
    private string $level;

    /**
     * Login utilisateur.
     */
    private string $login;

    /**
     * Email utilisateur.
     */
    private string $email;

    /**
     * Mot de passe haché.
     */
    private string $password;

    /**
     * Date de création.
     */
    private ?DateTime $createdAt;

    /**
     * Dernière activité enregistrée.
     */
    private ?DateTime $lastActivity = null;

    /**
     * Jeton cookie "remember me".
     */
    private ?string $cookieToken = null;

    /**
     * Hash du token de réinitialisation du mot de passe.
     */
    private ?string $resetTokenHash = null;

    /**
     * Date d'expiration du reset.
     */
    private ?DateTime $resetExpiresAt = null;

    /**
     * Secret Google Authenticator.
     */
    private ?string $google2faSecret = null;

    /**
     * Indique si la 2FA est activée.
     */
    private bool $google2faEnabled = false;

    /**
     * Nombre de tentatives 2FA échouées.
     */
    private int $failed2faAttempts = 0;

    /**
     * Dernière tentative 2FA.
     */
    private ?DateTime $last2faAttempt = null;

    /**
     * Indique si l'utilisateur existe en base.
     */
    private bool $exists = false;

    /**
     * Charger un utilisateur si l'identifiant est fourni.
     *
     * @param int|null $id Identifiant utilisateur.
     */
    public function __construct(?int $id = null)
    {
        parent::__construct();
        if (!is_null($id)) {
            $db = Database::openConnection();
            $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1")
                ->bindValue(":id", $id)
                ->execute();
            if ($db->countRows() === 1) {
                $data = $db->fetchObject();
                $this->setId($data->id)
                    ->setLevel($data->level)
                    ->setLogin($data->login)
                    ->setEmail($data->email)
                    ->setPassword($data->password)
                    ->setLastActivity(!empty($data->last_activity) ? new DateTime($data->last_activity) : null)
                    ->setCookieToken($data->cookie_token)
                    ->setResetTokenHash($data->reset_token)
                    ->setResetExpiresAt(!empty($data->reset_expires_at) ? new DateTime($data->reset_expires_at) : null)
                    ->setCreatedAt(new DateTime($data->gmt_create))
                    ->setGoogle2faSecret($data->google2fa_secret ?? null)
                    ->setGoogle2faEnabled((bool)($data->google2fa_enabled ?? 0))
                    ->setFailed2faAttempts((int)($data->failed_2fa_attempts ?? 0))
                    ->setLast2faAttempt(!empty($data->last_2fa_attempt) ? new DateTime($data->last_2fa_attempt) : null);
                
                
                $this->exists = true;
            }
        }
    }
    
    /**
     * Créer la table users si elle n'existe pas.
     *
     * @return void
     */
    public static function createUsersTable(): void
    {
        if (class_exists("Database") && $db = Database::openConnection()) {
            
            if ($db->prepare("SHOW TABLES LIKE 'users'")->execute()) {
                if ($db->countRows() === 0) {
                    $db->prepare("CREATE TABLE `users` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `level` enum('adminsu','usersu','admin','user') DEFAULT NULL,
                          `login` varchar(150) DEFAULT NULL,
                          `email` varchar(150) DEFAULT NULL,
                          `password` varchar(256) DEFAULT NULL,
                          `gmt_last_activity` datetime DEFAULT NULL,
                          `gmt_create` datetime DEFAULT current_timestamp(),
                          `gmt_update` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                          `cookie_token` varchar(255) DEFAULT NULL,
                          `reset_token` varchar(255) DEFAULT NULL,
                          `reset_expires_at` datetime DEFAULT NULL,
                          `google2fa_secret` varchar(64) DEFAULT NULL,
                          `google2fa_enabled` tinyint(1) DEFAULT 0,
                          `failed_2fa_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                          `last_2fa_attempt` datetime DEFAULT NULL,
                          PRIMARY KEY (`id`),
                          UNIQUE (`login`),
                          UNIQUE (`email`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;")
                       ->execute();
                }
            }
        }
    }

    /**
     * Récupérer tous les utilisateurs.
     *
     * @return list<User> Liste d'objets User.
     */
    public static function getAll(): array
    {
        $db = Database::openConnection();
        $db->prepare("SELECT id FROM users")
            ->execute();
        if ($db->countRows() > 0) {
            return array_map(static function ($v) {
                return new User($v);
            }, $db->fetchColumn());
        }
        return [];
    }

    /**
     * Récupérer l'identifiant utilisateur.
     *
     * @return int Identifiant.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Définir l'identifiant utilisateur.
     *
     * @param int $id Identifiant.
     *
     * @return User Instance courante.
     */
    public function setId(int $id): User
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Récupérer le rôle utilisateur.
     *
     * @return string Rôle.
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * Définir le rôle utilisateur.
     *
     * @param string $level Rôle.
     *
     * @return User Instance courante.
     */
    public function setLevel(string $level): User
    {
        $this->level = $level;
        return $this;
    }

    /**
     * Récupérer le login.
     *
     * @return string Login.
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * Définir le login.
     *
     * @param string $login Login.
     *
     * @return User Instance courante.
     */
    public function setLogin(string $login): User
    {
        $this->login = $login;
        return $this;
    }

    /**
     * Récupérer l'email.
     *
     * @return string Email.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Définir l'email.
     *
     * @param string $email Email.
     *
     * @return User Instance courante.
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Récupérer le mot de passe haché.
     *
     * @return string Hash du mot de passe.
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Définir le mot de passe (haché).
     *
     * @param string $password Hash du mot de passe.
     *
     * @return User Instance courante.
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Récupérer la dernière activité.
     *
     * @return DateTime|null Date de dernière activité.
     */
    public function getLastActivity(): ?DateTime
    {
        return $this->lastActivity;
    }

    /**
     * Définir la dernière activité.
     *
     * @param DateTime|null $lastActivity Date de dernière activité.
     *
     * @return User Instance courante.
     */
    public function setLastActivity(?DateTime $lastActivity): User
    {
        $this->lastActivity = $lastActivity;
        return $this;
    }

    /**
     * Récupérer la date de création.
     *
     * @return DateTime Date de création.
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Définir la date de création.
     *
     * @param DateTime|null $createdAt Date de création.
     *
     * @return User Instance courante.
     */
    public function setCreatedAt(?DateTime $createdAt): User
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    
    /**
     * Récupérer le token cookie.
     *
     * @return string|null Jeton cookie.
     */
    public function getCookieToken()
    {
        return $this->cookieToken;
    }
    
    /**
     * Définir le token cookie.
     *
     * @param string|null $cookieToken Jeton cookie.
     *
     * @return User Instance courante.
     */
    public function setCookieToken(?string $cookieToken): User
    {
        $this->cookieToken = $cookieToken;
        return $this;
    }

    /**
     * Récupérer le hash de token de réinitialisation.
     *
     * @return string|null Hash du token.
     */
    public function getResetToken(): ?string
    {
        return $this->resetTokenHash;
    }

    /**
     * Définir le hash de token de réinitialisation.
     *
     * @param string|null $resetToken Hash du token.
     *
     * @return User Instance courante.
     */
    public function setResetTokenHash(?string $resetToken): User
    {
        $this->resetTokenHash = $resetToken;
        return $this;
    }

    /**
     * Récupérer le hash de token de réinitialisation.
     *
     * @return string|null Hash du token.
     */
    public function getResetTokenHash(): ?string
    {
        return $this->resetTokenHash;
    }

    /**
     * Récupérer la date d'expiration du reset.
     *
     * @return DateTime|null Date d'expiration.
     */
    public function getResetExpiresAt(): ?DateTime
    {
        return $this->resetExpiresAt;
    }

    /**
     * Définir la date d'expiration du reset.
     *
     * @param DateTime|null $resetExpiresAt Date d'expiration.
     *
     * @return User Instance courante.
     */
    public function setResetExpiresAt(?DateTime $resetExpiresAt): User
    {
        $this->resetExpiresAt = $resetExpiresAt;
        return $this;
    }
    
    /**
     * Récupérer le secret Google Authenticator.
     *
     * @return string|null Secret 2FA.
     */
    public function getGoogle2faSecret(): ?string
    {
        return $this->google2faSecret;
    }
    
    /**
     * Définir le secret Google Authenticator.
     *
     * @param string|null $google2faSecret Secret 2FA.
     *
     * @return User Instance courante.
     */
    public function setGoogle2faSecret(?string $google2faSecret): User
    {
        $this->google2faSecret = $google2faSecret;
        return $this;
    }
    
    /**
     * Récupérer l'état d'activation 2FA.
     *
     * @return bool true si activée.
     */
    public function getGoogle2faEnabled(): bool
    {
        return $this->google2faEnabled;
    }
    
    /**
     * Définir l'état d'activation 2FA.
     *
     * @param bool $google2faEnabled true pour activer.
     *
     * @return User Instance courante.
     */
    public function setGoogle2faEnabled(bool $google2faEnabled): User
    {
        $this->google2faEnabled = $google2faEnabled;
        return $this;
    }
    
    /**
     * Vérifier si la 2FA est active.
     *
     * @return bool true si 2FA activée.
     */
    public function isTwoFactorActive(): bool
    {
        return $this->google2faEnabled;
    }
    
    /**
     * Récupérer le nombre d'échecs 2FA.
     *
     * @return int Nombre d'échecs.
     */
    public function getFailed2faAttempts(): int
    {
        return $this->failed2faAttempts;
    }
    
    /**
     * Définir le nombre d'échecs 2FA.
     *
     * @param int $failed2faAttempts Compteur d'échecs.
     *
     * @return User Instance courante.
     */
    public function setFailed2faAttempts(int $failed2faAttempts): User
    {
        $this->failed2faAttempts = $failed2faAttempts;
        return $this;
    }
    
    /**
     * Récupérer la date de dernière tentative 2FA.
     *
     * @return DateTime|null Date de dernière tentative.
     */
    public function getLast2faAttempt(): ?DateTime
    {
        return $this->last2faAttempt;
    }
    
    /**
     * Définir la date de dernière tentative 2FA.
     *
     * @param DateTime|null $last2faAttempt Date de dernière tentative.
     *
     * @return User Instance courante.
     */
    public function setLast2faAttempt(?DateTime $last2faAttempt): User
    {
        $this->last2faAttempt = $last2faAttempt;
        return $this;
    }
    
    /**
     * Vérifier si l'utilisateur existe en base.
     *
     * @return bool true si l'utilisateur est chargé.
     */
    public function exists(): bool
    {
        return $this->exists;
    }

    /**
     * Sauvegarder l'utilisateur en base.
     *
     * Effets de bord : écritures en base (INSERT/UPDATE).
     *
     * @return void
     */
    public function save(): void
    {
        $db = Database::openConnection();
        if (empty($this->id)) {
            $db->prepare("INSERT INTO users (level, login, email, password, cookie_token, reset_token, reset_expires_at, google2fa_secret, google2fa_enabled, failed_2fa_attempts) VALUES (:level, :login, :email, :password, :cookie_token, :reset_token, :reset_expires_at, :google2fa_secret, :google2fa_enabled, :failed_2fa_attempts)")
                ->bindValue(":level", $this->getLevel())
                ->bindValue(":login", $this->getLogin())
                ->bindValue(":email", $this->getEmail())
                ->bindValue(":password", $this->getPassword())
                ->bindValue(":cookie_token", $this->getCookieToken())
                ->bindValue(":reset_token", $this->getResetTokenHash())
                ->bindValue(":reset_expires_at", $this->getResetExpiresAt()?->format("Y-m-d H:i:s"))
                ->bindValue(":google2fa_secret", $this->getGoogle2faSecret())
                ->bindValue(":google2fa_enabled", $this->getGoogle2faEnabled() ? 1 : 0, PDO::PARAM_INT)
                ->bindValue(":failed_2fa_attempts", $this->getFailed2faAttempts())
                ->execute();
            $this->setId($db->lastInsertedId());
            $this->setCreatedAt(new DateTime());
        } else {
            $db->prepare("UPDATE users SET level = :level, login = :login, email = :email, password = :password, cookie_token = :cookie_token, reset_token = :reset_token, reset_expires_at = :reset_expires_at, google2fa_secret = :google2fa_secret, google2fa_enabled = :google2fa_enabled, failed_2fa_attempts = :failed_2fa_attempts, last_2fa_attempt = :last_2fa_attempt WHERE id = :id")
                ->bindValue(":level", $this->getLevel())
                ->bindValue(":login", $this->getLogin())
                ->bindValue(":email", $this->getEmail())
                ->bindValue(":password", $this->getPassword())
                ->bindValue(":cookie_token", $this->getCookieToken())
                ->bindValue(":reset_token", $this->getResetTokenHash())
                ->bindValue(":reset_expires_at", $this->getResetExpiresAt()?->format("Y-m-d H:i:s"))
                ->bindValue(":google2fa_secret", $this->getGoogle2faSecret())
                ->bindValue(":google2fa_enabled", $this->getGoogle2faEnabled() ? 1 : 0, PDO::PARAM_INT)
                ->bindValue(":failed_2fa_attempts", $this->getFailed2faAttempts())
                ->bindValue(":last_2fa_attempt", $this->getLast2faAttempt()?->format("Y-m-d H:i:s"))
                ->bindValue(":id", $this->getId())
                ->execute();
        }
    }

    /**
     * Supprimer un utilisateur.
     *
     * Effets de bord : suppression en base.
     *
     * @return void
     */
    public function delete(): void
    {
        $db = Database::openConnection();
        $db->prepare("DELETE FROM users WHERE id = :id")
            ->bindValue(":id", $this->getId())
            ->execute();
    }
    
    /**
     * Vérifier le niveau d'accès d'un utilisateur.
     *
     * @param string $userRole     Rôle de l'utilisateur.
     * @param string $requiredRole Rôle requis.
     *
     * @return bool true si l'accès est autorisé.
     */
    public static function checkAccess(string $userRole, string $requiredRole): bool
    {
        
        $access = false;
        
        $roleHierarchy = [
            USER::ADMINSU => 40,
            USER::ADMIN   => 30,
            USER::USERSU  => 20,
            USER::USER    => 10
        ];
        
        if ($roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole]) {
            $access = true;
        }
        
        return $access;
    }

    /**
     * Authentifier un utilisateur via login/email et mot de passe.
     *
     * Effets de bord : création d'un compte adminsu par défaut si absent.
     *
     * @param string $login    Login ou email.
     * @param string $password Mot de passe en clair.
     *
     * @return stdClass Objet contenant {error:bool, admin?:User}.
     */
    public static function login(string $login, string $password): stdClass
    {
        $obj        = new stdClass();
        $obj->error = false;
        
        $validRoles = [
            User::ADMINSU,
            User::ADMIN,
            User::USERSU,
            User::USER
        ];
        
        try {
            $db         = Database::openConnection();
            
            // Préparation de la requête pour récupérer l'identifiant et le mot de passe haché
            $db->prepare("SELECT id, level, password FROM users WHERE (login = :login OR email = :email) LIMIT 1")
               ->bindValue(":login", $login)
               ->bindValue(":email", $login)
               ->execute();
            
            // Vérifie qu'une ligne a bien été trouvée
            if ($db->countRows() === 1) {
                $result = $db->fetchObject();
                
                // Si c'est un adminsu (plu besoins, le mot de apsse de adminsu est hashé
                /*            if ($result->level === User::ADMINSU) {
                                if ($result->password !== $password) {
                                    $obj->error = true;
                                    return $obj;
                                }
                                $obj->admin = new User($result->id);
                                return $obj;
                            }*/
                
                $hashed_password = $result->password;
                
                // Vérification du mot de passe
                if (Encryption::passwordCheck2($password, $result->password)) {
                    if (in_array($result->level, $validRoles, true)) {
                        $obj->admin = new User($result->id);
                        return $obj;
                    }
                }
            } else {
                // l'utilisateur n'exsite, on le crée s'il est adminsu
                if ($login === "adminsu") {
                    
                    // L'utilisateur adminsu n'existe pas, on le crée
                    $adminUser = new User();
                    $adminUser->setLogin("adminsu")
                              ->setEmail(Config::get("DEFAULT_ACCOUNT.EMAIL"))
                              ->setPassword(password_hash($password, PASSWORD_DEFAULT))
                              ->setLevel(User::ADMINSU)
                              ->save();
                    // on se connecte
                    return self::login($login, $password);
                }
            }
        } catch (Exception $e) {
            if ($e->getCode() === '42S02') { // on teste si la table existe, si non on la crée
                self::createUsersTable();
                TwoFactorLog::createTable();
                return self::login($login, $password);
            } else {
                Logger::error("Impossible de se connecter : " . $e->getMessage());
            }
        }

        // Si aucune correspondance ou mot de passe incorrect, retourne une erreur
        $obj->error = true;
        return $obj;
    }

    /**
     * Récupérer un utilisateur par email.
     *
     * @param string $email Email recherché.
     *
     * @return User|null Utilisateur trouvé ou null.
     */
    public static function getByEmail(string $email): ?User
    {
        $db = Database::openConnection();
        $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1")
            ->bindValue(":email", $email)
            ->execute();
        if ($db->countRows() === 1) {
            $result = $db->fetchObject();
            return new User($result->id);
        }
        return null;
    }
    
    /**
     * Hacher un token de réinitialisation avec HMAC.
     *
     * @param string $token Token en clair.
     *
     * @return string Hash HMAC.
     */
    public static function hashResetToken(string $token): string
    {
        $secret = (string)Config::get("SECURITY.RESET_TOKEN.HMAC_SECRET");
        return hash_hmac('sha256', $token, $secret);
    }
    
    /**
     * Vérifier un token de réinitialisation.
     *
     * @param string $token Token en clair.
     *
     * @return bool true si le token est valide.
     */
    public function isResetTokenValid(string $token): bool
    {
        if (empty($this->resetTokenHash) || empty($this->resetExpiresAt)) {
            return false;
        }
        
        $now = new DateTime();
        if ($now > $this->resetExpiresAt) {
            return false;
        }
        
        $hash = self::hashResetToken($token);
        return hash_equals($this->resetTokenHash, $hash);
    }
    
    
    /**
     * Vérifier si le compte est temporairement verrouillé en 2FA.
     *
     * @param int $lockMinutes Durée de verrouillage en minutes.
     *
     * @return bool true si verrouillé.
     */
    public function isTwoFactorTemporarilyLocked(int $lockMinutes = 60): bool
    {
        $attempts = $this->getFailed2faAttempts();
        $last     = $this->getLast2faAttempt();
        
        if ($attempts < Config::get("GOOGLE_AUTHENTIFICATOR.ATTEMPTS")) {
            return false;
        }
        
        if ($last === null) {
            return false;
        }
        
        $unlockAt = (clone $last)->add(new DateInterval('PT' . $lockMinutes . 'M'));
        $now      = new DateTime();
        
        $locked = $now < $unlockAt;
        
        return $locked;
    }
    
    /**
     * Calculer le délai restant avant déverrouillage 2FA.
     *
     * @param int $lockMinutes Durée de verrouillage en minutes.
     *
     * @return int Minutes restantes (0 si non bloqué).
     */
    public function getTwoFactorLockRemainingMinutes(int $lockMinutes = 60): int
    {
        if (!$this->isTwoFactorTemporarilyLocked($lockMinutes)) {
            return 0;
        }
        
        $unlockAt = (clone $this->last2faAttempt)->add(
            new DateInterval('PT' . $lockMinutes . 'M')
        );
        $now      = new DateTime();
        $diff     = $now->diff($unlockAt);
        
        $minutes = $diff->h * 60 + $diff->i;
        if ($diff->s > 0) {
            $minutes++;
        }
        
        return max(1, $minutes);
    }
    
    /**
     * Réinitialiser le blocage 2FA si le délai est dépassé.
     *
     * Effets de bord : met à jour l'utilisateur en base.
     *
     * @param int $lockMinutes Durée de verrouillage en minutes.
     *
     * @return void
     */
    public function resetTwoFactorLockIfExpired(int $lockMinutes = 60): void
    {
        // Si on n'a jamais atteint le seuil ou pas de date, rien à faire
        if ($this->failed2faAttempts < Config::get("GOOGLE_AUTHENTIFICATOR.ATTEMPTS") || $this->last2faAttempt === null) {
            return;
        }
        
        $unlockAt = (clone $this->last2faAttempt)->add(
            new DateInterval('PT' . $lockMinutes . 'M')
        );
        
        // Si le délai est dépassé, on reset les compteurs
        if ((new DateTime()) >= $unlockAt) {
            $this->setFailed2faAttempts(0);
            $this->setLast2faAttempt(null);
            $this->save();
        }
    }
    
    /**
     * Récupérer les utilisateurs bloqués par la 2FA.
     *
     * @return list<User> Liste d'utilisateurs bloqués.
     */
    public static function getTwoFactorBlockedUsers(): array
    {
        try {
            
            $db = Database::openConnection();
            
            // On prend seulement les users qui ont déjà atteint le seuil
            $db->prepare("
                        SELECT id
                        FROM users
                        WHERE failed_2fa_attempts >= :attempts
                          AND last_2fa_attempt IS NOT NULL")
               ->bindValue(":attempts", Config::get("GOOGLE_AUTHENTIFICATOR.ATTEMPTS"), PDO::PARAM_INT)
               ->execute();
            
            if ($db->countRows() > 0) {
                $blocked = [];
                $ids = $db->fetchColumn();
                foreach ($ids as $id) {
                    $user = new User((int)$id);
                    if ($user->exists() && $user->isTwoFactorTemporarilyLocked(Config::get("GOOGLE_AUTHENTIFICATOR.LOCKED_IN_MINUTES"))) {
                        $blocked[] = $user;
                    }
                }
                
                return $blocked;
                
            }
            
            
        } catch (PDOException $e) {
            Logger::error("Impossible de récupérer les utilisateurs bloqués 2FA : " . $e->getMessage());
        }
        
        return [];
    }
    
}