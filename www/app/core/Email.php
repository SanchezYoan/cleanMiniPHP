<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
/**
 * Envoyer des emails via PHPMailer.
 *
 * Fournit un helper statique pour l'envoi SMTP et la gestion d'éventuelles pièces jointes.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */
class Email
{

     /**
      * Empêcher l'instanciation directe.
      */
     private function __construct()
     {
     }
    
    /**
     * Envoyer un email HTML.
     *
     * Effets de bord : envoi d'email et éventuelle pièce jointe.
     *
     * @param string|array<int, string> $destinataire Adresse(s) email cible(s).
     * @param string                   $subject      Sujet du message.
     * @param string                   $html         Corps HTML du message.
     * @param array<string, mixed>|null $uploadedFile Fichier uploadé (structure $_FILES) ou null.
     *
     * @return void Aucune valeur de retour.
     *
     * @throws Exception Si l'envoi échoue.
     */
     public static function sendEmail($destinataire, $subject, $html, $uploadedFile)
     {

         $mail             = new PHPMailer();
         $mail->isMail();
         $mail->isHTML(true);
         $mail->CharSet = "utf-8";
         $mail->SetFrom(Config::get('EMAILS.SETTINGS.NO_REPLY'), DOMAIN);
         $mail->Body = $html;
         $mail->Subject = $subject;
         if(Config::get("FEATURES.REDIRECTION.EMAIL")) {
             foreach (Config::get("EMAILS.DEVS") as $name => $email) {
                 $mail->AddAddress($email, $name);
             }
         } else {
             if(is_array($destinataire)){
                 foreach ($destinataire as $email){
                     $mail->AddAddress($email);
                 }
             } else {
                 $mail->AddAddress($destinataire);
             }
         }
         if(!empty($uploadedFile) && is_uploaded_file($uploadedFile['tmp_name']) && $uploadedFile["error"] == UPLOAD_ERR_OK) {
             $mail->addAttachment($uploadedFile["tmp_name"], $uploadedFile["name"]);
         }

         // If you don't have an email setup, you can instead save emails in log.txt file using Logger.
         if(!$mail->Send()) {
             throw new Exception("Email [$subject] couldn't be sent to " . var_export($destinataire, true) . "\r\n" . $mail->ErrorInfo);
         }
     }
    

 }