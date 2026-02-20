<?php

/**
 * Journaliser les événements liés à la 2FA.
 */
class TwoFactorLog extends Model
{
    /**
     * Créer la table de logs 2FA si nécessaire.
     *
     * @return void
     */
    public static function createTable(): void
    {
        if (!class_exists("Database") || !($db = Database::openConnection())) {
            return;
        }
        
        $db->prepare("SHOW TABLES LIKE 'logs_connexion_2fa'")->execute();
        if ($db->countRows() === 0) {
            $db->prepare("
                CREATE TABLE `logs_connexion_2fa` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `fk_users_id` int(11) DEFAULT NULL,
                  `success` tinyint(1) NOT NULL DEFAULT 0,
                  `reason` varchar(255) DEFAULT NULL,
                  `attempts_count` int(11) DEFAULT NULL,
                  `context` enum('LOGIN','VERIFY_2FA') DEFAULT 'VERIFY_2FA',
                  `ip_address` varchar(45) DEFAULT NULL,
                  `user_agent` text DEFAULT NULL,
                  `gmt_create` datetime DEFAULT current_timestamp(),
                  PRIMARY KEY (`id`),
                  KEY `idx_users_id` (`fk_users_id`),
                  CONSTRAINT `fk_logs2fa_users`
                      FOREIGN KEY (`fk_users_id`) REFERENCES `users`(`id`)
                          ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
            ")->execute();
        }
    }
    
    /**
     * Enregistrer un événement 2FA.
     *
     * Effets de bord : écrit en base si la configuration l'autorise.
     *
     * @param User|null   $user          Utilisateur concerné.
     * @param bool        $success       true si la 2FA est validée.
     * @param string|null $reason        Raison fonctionnelle (ex. code invalide).
     * @param int|null    $attemptsCount Compteur de tentatives au moment de l'événement.
     * @param string      $context       'LOGIN' ou 'VERIFY_2FA'.
     *
     * @return void
     */
    public static function log(
        ?User    $user,
        bool    $success,
        ?string $reason = null,
        ?int    $attemptsCount = null,
        string  $context = 'VERIFY_2FA'
    ): void
    {
        if (!Config::get("GOOGLE_AUTHENTIFICATOR.SECURITY.WRITE_DB")) {
            return;
        }
        
        try {
            $db = Database::openConnection();
            
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            $db->prepare("
                INSERT INTO logs_connexion_2fa
                    (fk_users_id, success, reason, attempts_count, context, ip_address, user_agent, gmt_create)
                VALUES
                    (:users_id, :success, :reason, :attempts_count, :context, :ip_address, :user_agent, NOW())
            ")
               ->bindValue(':users_id', !empty($user) ? $user->getId() : null)
               ->bindValue(':success', $success ? 1 : 0)
               ->bindValue(':reason', $reason)
               ->bindValue(':attempts_count', $attemptsCount)
               ->bindValue(':context', $context)
               ->bindValue(':ip_address', $ip)
               ->bindValue(':user_agent', $ua)
               ->execute();
        } catch (Exception $e) {
            // On ne casse jamais le flux de connexion pour un problème de log
            Logger::error("Impossible d'enregistrer un log 2FA : " . $e->getMessage());
        }
    }
}
