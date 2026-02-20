<?php
declare(strict_types=1);

use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\ImageRenderer;

/**
 * Encapsuler la logique de double authentification Google Authenticator.
 *
 * Centralise la génération de secrets/QR codes et la vérification de codes TOTP
 * en s'appuyant sur la configuration et l'état utilisateur.
 */
class Google2FaManager extends Manager
{
    /**
     * Générateur Google2FA utilisé pour les secrets et la validation TOTP.
     */
    private Google2FA $google2fa;
    
    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }
    
    /**
     * Déterminer si la 2FA est autorisée pour le rôle d'un utilisateur.
     *
     * @param User $user Utilisateur à évaluer.
     *
     * @return bool true si la configuration active la 2FA pour ce rôle.
     */
    public function is2faAllowedForUser(User $user): bool
    {
        $level = strtoupper($user->getLevel());
        $value = Config::get("GOOGLE_AUTHENTIFICATOR.IS_ACTIVE.{$level}");
        
        return (bool)$value; // false par defaut si n'existe pas.
    }
    
    /**
     * Vérifier si la 2FA est activée pour un utilisateur.
     *
     * Combine la configuration de rôle et les indicateurs persistés côté utilisateur.
     *
     * @param User $user Utilisateur à contrôler.
     *
     * @return bool true si la 2FA est activée et autorisée pour ce rôle.
     */
    public function is2faEnabledForUser(User $user): bool
    {
        if (!$this->is2faAllowedForUser($user)) {
            return false;
        }
        
        return $user->isTwoFactorActive();
    }
    
    
    /**
     * Activer la 2FA pour un utilisateur autorisé.
     *
     * Génère un secret si nécessaire, active le flag utilisateur et persiste en base.
     *
     * @param User $user Utilisateur à activer.
     *
     * @return string Secret 2FA actif.
     *
     * @throws RuntimeException Si la 2FA est désactivée pour ce rôle.
     */
    public function enable2faForUser(User $user): string
    {
        if (!$this->is2faAllowedForUser($user)) {
            throw new RuntimeException("La double authentification n'est pas disponible pour ce type de compte.");
        }
        
        $secret = $user->getGoogle2faSecret();
        if (empty($secret)) {
            $secret = $this->google2fa->generateSecretKey(16);
        }
        
        $user->setGoogle2faSecret($secret)
             ->setGoogle2faEnabled(true)
             ->save();
        
        return $secret;
    }
    
    
    /**
     * Désactiver la 2FA pour un utilisateur.
     *
     * Révoque le secret, désactive le flag utilisateur et persiste en base.
     *
     * @param User $user Utilisateur ciblé.
     *
     * @return void
     */
    public function disable2faForUser(User $user): void
    {
        $user->setGoogle2faEnabled(false)
             ->setGoogle2faSecret(null)
             ->save();
    }
    
    /**
     * Obtenir ou créer le secret 2FA d'un utilisateur.
     *
     * Si la 2FA n'est pas autorisée pour ce rôle, renvoie une chaîne vide.
     *
     * @param User $user Utilisateur cible.
     *
     * @return string Secret 2FA ou chaîne vide si non autorisée.
     */
    public function getOrCreateSecretForUser(User $user): string
    {
        if (!$this->is2faAllowedForUser($user)) {
            return '';
        }
        
        $secret = $user->getGoogle2faSecret();
        
        if (empty($secret)) {
            $secret = $this->google2fa->generateSecretKey(16);
            $user->setGoogle2faSecret($secret)
                 ->save();
        }
        
        return $secret;
    }
    
    /**
     * Générer l'URL otpauth:// utilisée par Google Authenticator.
     *
     * @param string $companyName Nom d'affichage de l'émetteur.
     * @param string $userEmail   Email de l'utilisateur.
     * @param string $secret      Secret 2FA.
     *
     * @return string URL otpauth:// prête pour un QR code.
     */
    public function getOtpAuthUrlForUser(string $companyName, string $userEmail, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            $companyName,
            $userEmail,
            $secret
        );
    }
    
    /**
     * Générer un QR code SVG inline à partir d'une URL otpauth://.
     *
     * @param string $otpAuthUrl URL otpauth://.
     * @param int    $size       Taille du rendu en pixels.
     *
     * @return string Data URI SVG base64.
     */
    public function generateInlineQrCode(string $otpAuthUrl, int $size = 220): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd()
        );
        
        $writer  = new Writer($renderer);
        $svgData = $writer->writeString($otpAuthUrl);
        
        return 'data:image/svg+xml;base64,' . base64_encode($svgData);
    }
    
    /**
     * Vérifier un code TOTP pour un utilisateur.
     *
     * La validation ne s'exécute que si la 2FA est autorisée pour ce rôle et activée.
     *
     * @param User   $user Utilisateur cible.
     * @param string $code Code TOTP saisi.
     *
     * @return bool true si le code est valide.
     */
    public function verifyCodeForUser(User $user, string $code): bool
    {
        if (!$this->is2faAllowedForUser($user)) {
            return false;
        }
        
        $secret = $user->getGoogle2faSecret();
        if (empty($secret)) {
            return false;
        }
        
        return $this->verifyCode($secret, $code);
    }
    
    /**
     * Vérifier un code TOTP à partir d'un secret.
     *
     * @param string $secret Secret 2FA.
     * @param string $code   Code TOTP saisi.
     *
     * @return bool true si le code est valide.
     */
    public function verifyCode(string $secret, string $code): bool
    {
        $code = trim($code);
        
        if ($code === '') {
            return false;
        }
        
        return $this->google2fa->verifyKey($secret, $code);
    }
    

}
