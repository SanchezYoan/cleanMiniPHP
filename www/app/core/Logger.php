<?php

/**
 * Journaliser les événements applicatifs.
 *
 * Centralise l'enregistrement en base, fichier et email des erreurs,
 * exceptions et événements de sécurité.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class Logger
{
    /**
     * Adresse IP capturée.
     */
    private static ?string $ip;
    /**
     * Niveau de log.
     */
    private static ?string $level;
    /**
     * URI de la requête.
     */
    private static ?string $request;
    /**
     * Message de log.
     */
    private static ?string $message;
    /**
     * Fichier source.
     */
    private static ?string $file;
    /**
     * Ligne source.
     */
    private static ?int $line;
    /**
     * Trace d'exécution.
     */
    private static ?string $trace;
    /**
     * Informations supplémentaires.
     */
    private static ?string $info;
    /**
     * Date de log.
     */
    private static ?string $date;
    /**
     * Instance singleton.
     */
    private static ?Logger $_instance = null;
    
    private function __construct()
    {
        if (class_exists("Database") && $db = Database::openConnection()) {
            // si la table n'existe pas, elle se créer tout seule dès que le premier log est enregistrés
            if ($db->prepare("SHOW TABLES LIKE 'logs'")->execute()) {
                if ($db->countRows() === 0) {
                    $db->prepare("CREATE TABLE IF NOT EXISTS `logs` (
                                                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                                  `date` datetime DEFAULT CURRENT_TIMESTAMP(),
                                                  `ip` varchar(40) DEFAULT NULL,
                                                  `request` varchar(250) DEFAULT NULL,
                                                  `level` varchar(25) DEFAULT NULL,
                                                  `message` text DEFAULT NULL,
                                                  `file` tinytext DEFAULT NULL,
                                                  `line` varchar(10) DEFAULT NULL,
                                                  `trace` text DEFAULT NULL,
                                                  `infos` text DEFAULT NULL,
                                                  PRIMARY KEY (`id`)
                                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")
                       ->execute();
                }
            }
        }
    }
    
    
    /**
     * Initialiser le singleton.
     *
     * @return Logger|null Instance ou null si échec.
     */
    private static function init(): ?Logger
    {
        
        if (is_null(self::$_instance)) {
            self::$_instance = new Logger();
        }
        
        return self::$_instance;
    }
    
    /**
     * Charger les données d'une erreur/exception dans le logger.
     *
     * @param Exception|string $error     Erreur ou exception.
     * @param bool             $withInfos Inclure les infos de requête.
     * @param string|null      $file      Fichier source.
     * @param int|null         $line      Ligne source.
     *
     * @return void
     */
    private static function load($error, bool $withInfos = false, ?string $file = null, $line = null)
    {
        $ip      = $_SERVER['HTTP_CLIENT_IP'] ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? (SUBDOMAIN === "CRON" ? "CRON" : "nc")));
        $request = $_SERVER['REQUEST_URI'] ?? "";
        $trace   = null;
        if ($withInfos) {
            Logger::setInfo(Logger::getRequestData());
        }
        if ($error instanceof Exception) {
            
            $message  = $error->getMessage();
            $file     = $error->getFile();
            $line     = $error->getLine();
            $previous = null;
            if (!empty($error->getPrevious()) && is_a($error->getPrevious(), Exception::class)) {
                $previous = "\n\n" . $error->getPrevious()->getTraceAsString();
            }
            $trace = $error->getTraceAsString() . $previous;
            
            // Si l'exception vient d'une précédente exception, on veut plutot ses infos
            if (is_a($error->getPrevious(), Throwable::class)) {
                $message = $error->getPrevious()->getMessage();
                $file    = $error->getPrevious()->getFile();
                $line    = $error->getPrevious()->getLine();
                $trace   = $error->getPrevious()->getTraceAsString();
            }
            
        } else {
            $message = $error;
            Logger::setTrace("");
            Logger::setLine($line);
            Logger::setFile($file);
        }
        
        Logger::setIp($ip);
        Logger::setRequest($request);
        Logger::setFile($file);
        Logger::setLine($line);
        Logger::setMessage($message);
        Logger::setTrace($trace);
        Logger::setDate(date("Y-m-d H:i:s"));
        Logger::setInfo(Logger::getRequestData());
        
        if ($error instanceof ErrorException) {
            Logger::setLevel($error->getSeverity());
        } else {
            Logger::setLevel(E_ERROR);
        }
        
    }
    
    /**
     * Enregistrer un log de niveau DEBUG.
     *
     * @param Exception|string $message   Message ou exception.
     * @param bool             $withInfos Inclure les infos de requête.
     * @param string|null      $file      Fichier source.
     * @param int|null         $line      Ligne source.
     *
     * @return void
     */
    public static function debug(Exception|string $message, bool $withInfos = true, ?string $file = null, ?int $line = null,): void
    {
        
        $log = Logger::init();
        $log::load($message, $withInfos, $file, $line);
        Logger::setLevel(Logger::errorNumber("DEBUG"));
        
        if (Config::get("FEATURES.LOGGER.DEBUG.WRITE_TXT")) {
            $log::saveToTxt();
        }
        if (Config::get("FEATURES.LOGGER.DEBUG.WRITE_DB")) {
            $log::saveToDB();
        }
        if (Config::get("FEATURES.LOGGER.DEBUG.SEND_MAIL")) {
            $log::sendMail();
        }
        
    }
    
    /**
     * Enregistrer un log de niveau NOTICE.
     *
     * @param Exception|string $message   Message ou exception.
     * @param bool             $withInfos Inclure les infos de requête.
     * @param string|null      $file      Fichier source.
     * @param int|null         $line      Ligne source.
     *
     * @return void
     */
    public static function notice(Exception|string $message, bool $withInfos = true, ?string $file = null, ?int $line = null,): void
    {
        $log = Logger::init();
        $log::load($message, $withInfos, $file, $line);
        Logger::setLevel(Logger::errorNumber("NOTICE"));
        if (Config::get("FEATURES.LOGGER.NOTICE.WRITE_TXT")) {
            $log::saveToTxt();
        }
        if (Config::get("FEATURES.LOGGER.NOTICE.WRITE_DB")) {
            $log::saveToDB();
        }
        if (Config::get("FEATURES.LOGGER.NOTICE.SEND_MAIL")) {
            $log::sendMail();
        }
        
    }
    
    /**
     * Enregistrer un log de niveau WARNING.
     *
     * @param Exception|string $message   Message ou exception.
     * @param bool             $withInfos Inclure les infos de requête.
     * @param string|null      $file      Fichier source.
     * @param int|null         $line      Ligne source.
     *
     * @return void
     */
    public static function warning(Exception|string $message, bool $withInfos = true, ?string $file = null, ?int $line = null,): void
    {
        $log = Logger::init();
        $log::load($message, $withInfos, $file, $line);
        Logger::setLevel(Logger::errorNumber("WARNING"));
        
        if (Config::get("FEATURES.LOGGER.WARNING.WRITE_TXT")) {
            $log::saveToTxt();
        }
        if (Config::get("FEATURES.LOGGER.WARNING.WRITE_DB")) {
            $log::saveToDB();
        }
        if (Config::get("FEATURES.LOGGER.WARNING.SEND_MAIL")) {
            $log::sendMail();
        }
        
    }
    
    /**
     * Enregistrer un log de niveau ERROR.
     *
     * @param Exception|string $message   Message ou exception.
     * @param bool             $withInfos Inclure les infos de requête.
     * @param string|null      $file      Fichier source.
     * @param int|null         $line      Ligne source.
     * @param int|null         $level     Niveau d'erreur PHP optionnel.
     *
     * @return void
     */
    public static function error(Exception|string $message, bool $withInfos = true, ?string $file = null, ?int $line = null, $level = null): void
    {
        $log = Logger::init();
        $log::load($message, $withInfos, $file, $line);
        if (!$level) {
            Logger::setLevel(Logger::errorNumber("ERROR"));
        } else {
            Logger::setLevel($level);
        }
        
        if (Config::get("FEATURES.LOGGER.ERROR.WRITE_TXT")) {
            $log::saveToTxt();
        }
        if (Config::get("FEATURES.LOGGER.ERROR.WRITE_DB")) {
            $log::saveToDB();
        }
        if (Config::get("FEATURES.LOGGER.ERROR.SEND_MAIL")) {
            $log::sendMail();
        }
        
    }
    
    /**
     * Enregistrer un log de niveau CRITICAL.
     *
     * @param Exception|string $message   Message ou exception.
     * @param bool             $withInfos Inclure les infos de requête.
     * @param string|null      $file      Fichier source.
     * @param int|null         $line      Ligne source.
     *
     * @return void
     */
    public static function critical(Exception|string $message, bool $withInfos = true, ?string $file = null, ?int $line = null,): void
    {
        $log = Logger::init();
        $log::load($message, $withInfos, $file, $line);
        Logger::setLevel(Logger::errorNumber("CRITICAL"));
        
        if (Config::get("FEATURES.LOGGER.CRITICAL.WRITE_TXT")) {
            $log::saveToTxt();
        }
        if (Config::get("FEATURES.LOGGER.CRITICAL.WRITE_DB")) {
            $log::saveToDB();
        }
        if (Config::get("FEATURES.LOGGER.CRITICAL.SEND_MAIL")) {
            $log::sendMail();
        }
        
    }
    
    
    /**
     * Enregistrer un log de niveau SECURITY.
     *
     * @param Exception|string $message   Message ou exception.
     * @param bool             $withInfos Inclure les infos de requête.
     * @param string|null      $file      Fichier source.
     * @param int|null         $line      Ligne source.
     *
     * @return void
     */
    public static function security(Exception|string $message, bool $withInfos = true, ?string $file = null, ?int $line = null,): void
    {
        
        $log = Logger::init();
        $log::load($message, $withInfos, $file, $line);
        Logger::setLevel(Logger::errorNumber("SECURITY"));
        if (Config::get("FEATURES.LOGGER.SECURITY.WRITE_TXT")) {
            $log::saveToTxt();
        }
        if (Config::get("FEATURES.LOGGER.SECURITY.WRITE_DB")) {
            $log::saveToDB();
        }
        if (Config::get("FEATURES.LOGGER.SECURITY.SEND_MAIL")) {
            $log::sendMail();
        }
        
    }
    
    /**
     * Enregistrer un log SECURITY lié au blocage 2FA.
     *
     * Effets de bord : peut envoyer un email de notification.
     *
     * @param Exception|string $message     Message ou exception.
     * @param User             $user        Utilisateur concerné.
     * @param int              $lockMinutes Durée de blocage.
     * @param bool             $withInfos   Inclure les infos de requête.
     * @param string|null      $file        Fichier source.
     * @param int|null         $line        Ligne source.
     *
     * @return void
     */
    public static function security2FA(Exception|string $message, User $user, int $lockMinutes, bool $withInfos = true, ?string $file = null, ?int $line = null): void
    {
        
        $log = Logger::init();
        $log::load($message, $withInfos, $file, $line);
        Logger::setLevel(Logger::errorNumber("SECURITY"));
        if (Config::get("GOOGLE_AUTHENTIFICATOR.SECURITY.WRITE_DB")) {
            $log::saveToDB();
        }
        if (Config::get("GOOGLE_AUTHENTIFICATOR.SECURITY.SEND_EMAIL")) {
            $log::send2FALockEmail($user, $lockMinutes);
        }
        
    }
    
    /**
     * Fusionner les données de requête pour diagnostic.
     *
     * @return string Informations formatées.
     */
    private static function getRequestData(): string
    {
        
        $phpinput = file_get_contents("php://input");
        if (!isset($_SERVER) || !isset($_SERVER["REQUEST_SCHEME"])) {
            return "Variable \$_SERVER no set. Must be a cron job";
        }
        
        $session = $_SESSION ?? null;
        if (isset($session["parametres"])) {
            unset($session["parametres"]);
        }
        $headers = apache_request_headers();
        return var_export(
            [
                "HTTP_HOST"      => $_SERVER["HTTP_HOST"] ?? "NC",
                "REQUEST_URI"    => $_SERVER["REQUEST_URI"] ?? "NC",
                "SERVER_NAME"    => $_SERVER["SERVER_NAME"] ?? "NC",
                "REQUEST_METHOD" => $_SERVER["REQUEST_METHOD"] ?? "NC",
                "HTTP_REFERER"   => $_SERVER["HTTP_REFERER"] ?? "NC",
                "REMOTE_ADDR"    => $_SERVER["REMOTE_ADDR"] ?? "NC",
                
                "HEADERS" => $headers,
                
                "HTTP_USER_AGENT" => $_SERVER["HTTP_USER_AGENT"] ?? "NC",
                "POST"            => $_POST ?? "NC",
                //                "GET"             => isset($_POST) ? $_GET : "NC",
                //                "SESSION"         => $session ?? "NC",
                //                "COOKIE"          => $_COOKIE ?? "NC",
                "PHP INPUT"       => !empty($phpinput) ? $phpinput : "NC",
            ],
            true
        );
    }
    
    /**
     * Enregistrer le log en base.
     *
     * @return void
     */
    private static function saveToDB(): void
    {
        
        if (class_exists("Database")) {
            
            $db = Database::openConnection();
            $db->prepare("INSERT INTO logs 
                                        SET date = NOW(),
                                            level = :level,
                                            ip  = :ip,
                                            request  = :request,
                                            message = :message,
                                            file = :file,
                                            line = :line,
                                            trace = :trace, 
                                            infos = :infos")
               ->bindValue(":level", Logger::errorType(Logger::getLevel()))
               ->bindValue(":ip", Logger::getIp(), PDO::PARAM_STR)
               ->bindValue(":request", Logger::getRequest(), PDO::PARAM_STR)
               ->bindValue(":file", Logger::getFile())
               ->bindValue(":line", Logger::getLine())
               ->bindValue(":message", Logger::getMessage())
               ->bindValue(":trace", Logger::getTrace())
               ->bindValue(":infos", Logger::getInfo());
            $db->execute();
        }
        
    }
    
    /**
     * Charger les logs depuis la base
     *
     * @param $nb
     * @param $level
     * @return array
     */
    public static function loadBDD($nb, $level = 'DEBUG'): array
    {
        if (class_exists("Database")) {
            
            $db = Database::openConnection();
            // Construction dynamique de la requête
            if ($level !== 'ALL') {
                $db->prepare("SELECT * FROM logs WHERE level = :level ORDER BY id DESC LIMIT " . intval($nb));
                $db->bindValue(":level", $level);
            } else {
                $db->prepare("SELECT * FROM logs ORDER BY id DESC LIMIT " . intval($nb));
            }
            
            if ($db->execute()) {
                return $db->fetchAllObject();
            }
        }
        return [];
    }
    
    /**
     * Enregistrer le log dans un fichier texte local.
     *
     * @return void
     */
    private static function saveToTxt()
    {
        $request = Logger::getRequest();
        $date    = date("Y-m-d H:i:s");
        $file    = self::getFile();
        $line    = Logger::getLine();
        $content = Logger::getMessage() . (($file && $line) ? " in $file at $line" : null) . "\nREQUEST:\n" . Logger::getRequestData() . "\n";
        
        // error_log is not binary safe : null chars will break it
        $result     = "[#] $request ($date) | $content\n";
        $dateString = date("Y-m-d");
        $logfile    = APP . "/logs/error.$dateString.log";
        error_log($result, 3, $logfile);
        
    }
    
    
    /**
     * Envoyer un email de log via PHPMailer.
     *
     * @return void
     */
    private static function sendMail(): void
    {
        $level   = Logger::errorType(Logger::getLevel());
        $date    = Logger::getDate() ?? date("Y-m-d H:i:s");
        $message = Logger::getMessage() ?? "Aucun message défini dans le log";
        $file    = Logger::getFile();
        $line    = Logger::getLine();
        $trace   = Logger::getTrace();
        $info    = self::getInfo();
        
        $textLog = "[lvl] $level - date : $date\n";
        $textLog .= "MESSAGE :\n$message\n";
        $textLog .= "IN $file at line $line\n\n";
        $textLog .= "TRACE : \n$trace\n\n";
        $textLog .= "INFO : \n$info\n";
        
        $domain = DOMAIN;
        
        $emails = Config::get("EMAILS.ERRORS");
        
        if (class_exists("PHPMailer\PHPMailer\PHPMailer")) {
            $textLog = "<!DOCTYPE html><html lang='fr'>
                            <head><title>Logger $domain</title><meta charset=\"utf-8\"></head>
                            <body>";
            $textLog .= "<p><strong>[#] $level</strong> - $date, in $file at line $line</p>";
            $textLog .= "<p><strong>Message:</strong> $message</p>";
            $textLog .= "<p><strong>Trace:</strong> <pre>$trace</pre></p>";
            $textLog .= "<p><strong>Infos:</strong> <pre>$info</pre></p>";
            $textLog .= "</body></html>";
            
            try {
                $mail          = new PHPMailer\PHPMailer\PHPMailer();
                $mail->CharSet = "UTF-8";
                $mail->Subject = "Logger $domain";
                $mail->Body    = $textLog;
                $mail->setFrom(Config::get("EMAILS.SETTINGS.NO_REPLY"), DOMAIN);
                foreach ($emails as $name => $email) {
                    $mail->addAddress($email, $name);
                }
                $mail->isHTML(true);
                $mail->send();
            } catch (\PHPMailer\PHPMailer\Exception) {
                
            }
        } else {
            
            foreach ($emails as $email) {
                error_log($textLog, 1, $email, "From: logger@$domain");
            }
            
        }
    }
    
    /**
     * Envoyer un email de sécurité lié aux tentatives 2FA.
     *
     * @param User $user        Utilisateur concerné.
     * @param int  $lockMinutes Durée de blocage.
     *
     * @return void
     */
    private static function send2FALockEmail(User $user, int $lockMinutes): void
    {
        $domain = defined('DOMAIN') ? DOMAIN : ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $date   = (new DateTime())->format('Y-m-d H:i:s');
        
        $userId    = $user->getId();
        $userEmail = $user->getEmail();
        $userLogin = $user->getLogin();
        $ip        = $_SERVER['REMOTE_ADDR'] ?? 'IP inconnue';
        

        $recipients = Config::get("GOOGLE_AUTHENTIFICATOR.SECURITY.SEND_EMAIL") ?? [];
        if (!is_array($recipients) || empty($recipients)) {
            // fallback : on envoie au mail d'erreurs si défini
            $recipients = Config::get("EMAILS.SETTINGS.NO_REPLY") ?? [];
        }
        
        $attempts = Config::get("GOOGLE_AUTHENTIFICATOR.ATTEMPTS");
        $subject = "[$domain] Compte bloqué après {$attempts} tentatives 2FA";
        
        // Corps HTML
        $html = "<!DOCTYPE html><html lang='fr'>";
        $html .= "<head><meta charset='utf-8'><title>$subject</title></head><body>";
        $html .= "<h2>Blocage 2FA sur le site $domain</h2>";
        $html .= "<p>Un compte vient d'être <strong>temporairement bloqué</strong> après plusieurs codes de double authentification incorrects.</p>";
        $html .= "<p><strong>Date :</strong> {$date}</p>";
        $html .= "<h3>Informations utilisateur</h3>";
        $html .= "<ul>";
        $html .= "<li><strong>ID :</strong> {$userId}</li>";
        $html .= "<li><strong>Login :</strong> {$userLogin}</li>";
        $html .= "<li><strong>Email :</strong> {$userEmail}</li>";
        $html .= "<li><strong>Adresse IP (dernière tentative) :</strong> {$ip}</li>";
        $html .= "</ul>";
        $html .= "<h3>Détails du blocage</h3>";
        $html .= "<p>";
        $html .= "Le compte est bloqué pour une durée de <strong>{$lockMinutes} minute(s)</strong> après {$attempts} tentatives 2FA échouées.<br>";
        $html .= "Passé ce délai, l'utilisateur pourra à nouveau tenter de se connecter.";
        $html .= "</p>";
        $html .= "<p style='font-size:12px;color:#666;margin-top:20px;'>Cet email est généré automatiquement, merci de ne pas y répondre.</p>";
        $html .= "</body></html>";
        
        // Fallback texte simple
        $text = "Blocage 2FA sur le site {$domain}\n\n";
        $text .= "Un compte vient d'être temporairement bloqué après plusieurs codes 2FA incorrects.\n";
        $text .= "Date : {$date}\n";
        $text .= "ID utilisateur : {$userId}\n";
        $text .= "Login : {$userLogin}\n";
        $text .= "Email : {$userEmail}\n";
        $text .= "IP (dernière tentative) : {$ip}\n";
        $text .= "Durée de blocage : {$lockMinutes} minute(s)\n";
        
        // Envoi
        $from = Config::get("EMAILS.SETTINGS.NO_REPLY") ?? "no-reply@{$domain}";
        
        if (class_exists("PHPMailer\PHPMailer\PHPMailer")) {
            try {
                $mail          = new PHPMailer\PHPMailer\PHPMailer();
                $mail->CharSet = "UTF-8";
                $mail->Subject = $subject;
                $mail->Body    = $html;
                $mail->AltBody = $text;
                $mail->setFrom($from, $domain);
                
                // $recipients est supposé être du type ["Nom" => "email", ...] ou ["email1", "email2", ...]
                foreach ($recipients as $name => $email) {
                    if (is_int($name)) {
                        $mail->addAddress($email);
                    } else {
                        $mail->addAddress($email, $name);
                    }
                }

                
                $mail->isHTML(true);
                $mail->send();
            } catch (\PHPMailer\PHPMailer\Exception $e) {
                Logger::error("Erreur envoi mail blocage 2FA : " . $e->getMessage());
            }
        } else {
            // Fallback via error_log() / mail()
            foreach ($recipients as $name => $email) {
                if (is_int($name)) {
                    error_log($text, 1, $email, "From: {$from}");
                } else {
                    error_log($text, 1, $email, "From: {$from}");
                }
            }
            if (!empty($userEmail)) {
                error_log($text, 1, $userEmail, "From: {$from}");
            }
        }
    }
    
    
    /**
     * Mapper un code d'erreur vers un libellé.
     *
     * @param int $errno Code d'erreur PHP.
     *
     * @return string Libellé associé.
     */
    private static function errorType(int $errno): string
    {
        // define an assoc array of error string
        $errortype = [
            -2                  => 'SECURITY',
            -1                  => 'CRITICAL',
            E_ERROR             => 'ERROR',
            E_WARNING           => 'WARNING',
            E_PARSE             => 'PARSING ERROR',
            E_NOTICE            => 'NOTICE',
            E_CORE_ERROR        => 'Core Error',
            E_CORE_WARNING      => 'Core Warning',
            E_COMPILE_ERROR     => 'Compile Error',
            E_COMPILE_WARNING   => 'Compile Warning',
            E_USER_ERROR        => 'User Error',
            E_USER_WARNING      => 'User Warning',
            E_USER_NOTICE       => 'User Notice',
            18000               => 'DEBUG',
            E_USER_DEPRECATED   => 'User Deprecated',
            E_DEPRECATED        => 'Deprecated',
            E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
        ];
        
        return $errortype[$errno] ?? "ERROR";
    }
    
    /**
     * Mapper un libellé vers un code d'erreur.
     *
     * @param string $errLevel Libellé d'erreur.
     *
     * @return int Code numérique.
     */
    private static function errorNumber(string $errLevel): int
    {
        // define an assoc array of error string
        $errorNum = [
            'SECURITY'              => -2,
            'CRITICAL'              => -1,
            'ERROR'                 => E_ERROR,
            'WARNING'               => E_WARNING,
            'PARSING ERROR'         => E_PARSE,
            'NOTICE'                => E_NOTICE,
            'Core Error'            => E_CORE_ERROR,
            'Core Warning'          => E_CORE_WARNING,
            'Compile Error'         => E_COMPILE_ERROR,
            'Compile Warning'       => E_COMPILE_WARNING,
            'User Error'            => E_USER_ERROR,
            'User Warning'          => E_USER_WARNING,
            'User Notice'           => E_USER_NOTICE,
            'User Deprecated'       => E_USER_DEPRECATED,
            'Deprecated'            => E_DEPRECATED,
            'Catchable Fatal Error' => E_RECOVERABLE_ERROR,
            'DEBUG'                 => 18000,
        ];
        
        return $errorNum[$errLevel] ?? 0;
    }
    
    /**
     * Compter les logs par type pour aujourd'hui.
     *
     * @return array Tableau avec les comptes par type.
     */
    public static function countByTypeToday()
    {
        $countToday = [
            "NOTICE"   => 0,
            "DEBUG"    => 0,
            "WARNING"  => 0,
            "ERROR"    => 0,
            "CRITICAL" => 0,
            "SECURITY" => 0
        ];
        
        if (class_exists("Database")) {
            
            $db = Database::openConnection();
            
            $sql = "SELECT level, COUNT(*) AS count
                FROM logs
                WHERE DATE(date) = CURDATE()
                AND level IN ('NOTICE', 'DEBUG', 'WARNING', 'ERROR', 'CRITICAL', 'SECURITY')
                GROUP BY level";
            
            $db->prepare($sql);
            
            if ($db->execute()) {
                
                if ($db->countRows() > 0) {
                    // Parcourir les résultats et les assigner au tableau
                    while ($row = $db->fetchAssociative()) {
                        $level              = $row['level'];
                        $countToday[$level] = $row['count'];
                    }
                }
                return $countToday;
            }
        }
        return $countToday;
    }
    
    /**
     * Récupérer l'IP enregistrée.
     *
     * @return string|null IP.
     */
    public static function getIp()
    {
        return self::$ip;
    }
    
    /**
     * Définir l'IP enregistrée.
     *
     * @param string|null $ip IP.
     *
     * @return void
     */
    public static function setIp($ip)
    {
        self::$ip = $ip;
        
    }
    
    /**
     * Récupérer le message de log.
     *
     * @return string|null Message.
     */
    public static function getMessage()
    {
        return self::$message;
    }
    
    /**
     * Définir le message de log.
     *
     * @param string $message Message.
     *
     * @return void
     */
    public static function setMessage($message)
    {
        self::$message = $message;
    }
    
    /**
     * Récupérer le fichier source.
     *
     * @return string|null Fichier.
     */
    public static function getFile()
    {
        return self::$file;
    }
    
    /**
     * Définir le fichier source.
     *
     * @param string|null $file Fichier.
     *
     * @return void
     */
    public static function setFile($file)
    {
        self::$file = $file;
    }
    
    /**
     * Récupérer la ligne source.
     *
     * @return int|null Ligne.
     */
    public static function getLine()
    {
        return self::$line;
    }
    
    /**
     * Définir la ligne source.
     *
     * @param int|null $line Ligne.
     *
     * @return void
     */
    public static function setLine($line)
    {
        self::$line = $line;
        
    }
    
    /**
     * Récupérer la trace.
     *
     * @return string|null Trace.
     */
    public static function getTrace()
    {
        return self::$trace;
    }
    
    /**
     * Définir la trace.
     *
     * @param string|null $trace Trace.
     *
     * @return void
     */
    public static function setTrace($trace)
    {
        self::$trace = $trace;
        
    }
    
    /**
     * Récupérer les informations additionnelles.
     *
     * @return string Informations de requête.
     */
    public static function getInfo()
    {
        
        return self::$info ?? Logger::getRequestData();
    }
    
    /**
     * Définir les informations additionnelles.
     *
     * @param string $info Informations.
     *
     * @return void
     */
    public static function setInfo($info)
    {
        self::$info = $info;
        
    }
    
    /**
     * Récupérer le niveau courant.
     *
     * @return int Niveau.
     */
    public static function getLevel(): int
    {
        return (int)self::$level;
    }
    
    /**
     * Définir le niveau courant.
     *
     * @param int $level Niveau.
     *
     * @return void
     */
    public static function setLevel($level)
    {
        self::$level = (int)$level;
    }
    
    /**
     * Récupérer la date de log.
     *
     * @return string|null Date.
     */
    public static function getDate()
    {
        return self::$date;
    }
    
    /**
     * Définir la date de log.
     *
     * @param string $date Date.
     *
     * @return void
     */
    public static function setDate($date)
    {
        self::$date = $date;
        
    }
    
    /**
     * Récupérer la requête enregistrée.
     *
     * @return string|null Requête.
     */
    public static function getRequest()
    {
        return self::$request;
    }
    
    /**
     * Définir la requête enregistrée.
     *
     * @param string $request Requête.
     *
     * @return void
     */
    public static function setRequest(string $request): void
    {
        self::$request = $request;
    }
    
    
    #endregion
    
    
}