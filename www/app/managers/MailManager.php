<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Orchestrer l'envoi d'emails via PHPMailer.
 *
 * Prépare les sujets, formatages HTML et ajoute les destinataires selon
 * différents types d'utilisateurs/structures.
 */
class MailManager extends Manager
{

    /**
     * Formater un contenu HTML dans le gabarit email.
     *
     * @param string $content Contenu HTML brut.
     *
     * @return string HTML final pour le corps du message.
     */
    private function formateMailHTML(string $content = ''): string
    {
        $path = Config::get('EMAIL_VIEWS_PATH');
        ob_start();
        //require $path . "/header.php";
        echo $content;
        //require $path . "/footer.php";

        return ob_get_clean();
    }

    /**
     * Préfixer le sujet par l'environnement courant si nécessaire.
     *
     * @param string $subject Sujet original.
     *
     * @return string Sujet prêt à être envoyé.
     */
    public static function formateSubject(string $subject = ''): string
    {
        // Mantis 1937 : Emails : ajout ENV dans tous les titres de mail si ENV !== PROD
        return ((ENV !== "PROD") ? "[" . ENV . "] - " : "") . $subject;
    }

    /**
     * Envoyer un email HTML à une liste de destinataires.
     *
     * @param string                                                $subject         Sujet du message.
     * @param string                                                $body            Corps HTML.
     * @param array<int, User|UtilisateurWeb|array{mail:string,pseudo:string}|string> $destinataires Destinataires.
     * @param bool                                                  $duplicateSubjet Ajoute le sujet en en-tête du body.
     *
     * @return bool true si l'envoi est réussi.
     */
    public function sendMail($subject, $body, array $destinataires, bool $duplicateSubjet = true): bool
    {
        try {

            $mail = $this->createMailer();

            // Set Charset encoding
            $mail->CharSet = $mail::CHARSET_UTF8;
            $mail->isHTML();
            $mail->isSendmail();
            $mail->Subject = MailManager::formateSubject($subject);

            if ($duplicateSubjet) {
                $body = "<h2>$subject</h2>
                        $body";
            }

            $this->addUsers($mail, $destinataires);


            if (ENV !== "PROD") {
                $subject = "[" . ENV . "] " . $subject;
            }

            //formater le corps du message
            $mail->Body = $this->formateMailHTML($body);
            if (ENV !== "PROD") {
                $mail->setFrom(Config::get('EMAILS.SETTINGS.NO_REPLY'), ENV . " | NouveauProjet");
            } else {
                $mail->setFrom(Config::get('EMAILS.SETTINGS.NO_REPLY'), "NouveauProjet");
            }

            if ($mail->send()) {
                Logger::debug("[Mail] Envoi [$subject] a : " . $this->getFooter($destinataires));
                return true;
            }

            $this->addError("L'envoi du mail [$subject] a échoué. Destinataires : " . $this->getFooter($destinataires) . " erreur : $mail->ErrorInfo");

        } catch (Exception $e) {
            Logger::error($e);
            $this->addError(__METHOD__ . " : [$subject] Exception, " . $e->getMessage());
        }

        return false;

    }

    /**
     * Fournir une instance PHPMailer (point d'extension pour tests).
     *
     * @return PHPMailer Instance configurée.
     */
    protected function createMailer(): PHPMailer
    {
        return new PHPMailer();
    }

    /**
     * Envoyer un email de réinitialisation du mot de passe.
     *
     * @param User   $user  Utilisateur cible.
     * @param string $token Jeton de réinitialisation.
     *
     * @return bool true si l'email est envoyé.
     */
    public function sendForgotPassword(User $user, string $token): bool
    {

        $url = PUBLIC_ROOT . '/login/forgot/' . $user->getId() . '/' . $token;
        $subject = "Mot de passe oublié";
        $corps = [
            "<p>Bonjour,<br>",
            "Si vous êtes bien à l’origine de cette demande de modification de mot de passe, veuillez cliquer sur le lien ci-après afin de recréer votre mot de passe :</p>",
            "<a href=\"$url\"><strong>Changer mon mot de passe</strong></a>",
            "<p>Si vous n’avez pas demandé à changer votre mot de passe, veuillez ignorer cet email.",
            "<p>Cordialement,<br />L’équipe NouveauProjet.</p>",
        ];
        $this->sendMail($subject, implode('<br />', $corps), [$user]);
        if ($this->hasErrors()) {
            Logger::error(implode(' / ', $this->errors()));

            return false;
        }
        Logger::debug("[MAIL] Demande de changement de mot de passe envoyée - ID : " . $user->getId());

        return true;

    }

    /**
     * Construire un footer listant les destinataires.
     *
     * @param array<int, User|UtilisateurWeb|array{mail:string,pseudo:string}|string> $aUsers Liste des destinataires.
     *
     * @return string HTML du footer.
     */
    private function getFooter(array $aUsers): string
    {
        $footer = "<br><hr>Destinataire original : ";
        $uniciteMail = [];
        foreach ($aUsers as $user) {
            if ($user instanceof UtilisateurWeb) {
                if (!in_array($user->getEmail(), $uniciteMail, true)) {
                    $uniciteMail[] = $user->getEmail();
                    $footer .= '<br> (' . $user->getEmail() . ')' . $user->getPrenom() . ' ' . $user->getNom();
                }
            } else if (is_array($user) && isset($user['pseudo']) && isset($user['mail'])) {
                if (!filter_var($user['mail'], FILTER_VALIDATE_EMAIL)) {

                    Logger::warning('Le mail :' . $user['mail'] . ' n\'est pas un mail valide.');
                } else if (!in_array($user['mail'], $uniciteMail, true)) {
                    $uniciteMail[] = $user['mail'];
                    $footer .= '<br> (' . $user['mail'] . ')' . $user['pseudo'];
                }
            } else if (is_string($user)) {
                $footer .= "<br> ($user)";
            }
        }

        return $footer;
    }

    /**
     * Ajouter les destinataires au mailer.
     *
     * @param PHPMailer                                                $mail   Instance PHPMailer.
     * @param array<int, User|UtilisateurWeb|array{mail:string,pseudo:string}|string> $aUsers Destinataires.
     *
     * @return void
     */
    private function addUsers(PHPMailer $mail, array $aUsers): void
    {
        $uniciteMail = [];
        foreach ($aUsers as $i => $user) {
            if ($user instanceof User) {
                if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
                    Logger::warning('User n° :' . $user->getId() . ' n\'a pas un mail valide : ' . $user->getEmail());
                } else {
                    if (!in_array($user->getEmail(), $uniciteMail, true)) {
                        $uniciteMail[] = $user->getEmail();
                        if ($i === 0) {
                            $mail->addAddress($user->getEmail(), $user->getEmail());
                        } else {
                            $mail->addBCC($user->getEmail(), $user->getEmail());
                        }
                    }
                }

            } else if (is_array($user) && isset($user['pseudo']) && isset($user['mail'])) {

                $user['mail'] = trim($user['mail']);

                if (!filter_var($user['mail'], FILTER_VALIDATE_EMAIL)) {
                    Logger::warning('Le mail :' . $user['mail'] . ' n\'est pas un mail valide.');
                } else {
                    if (!in_array($user['mail'], $uniciteMail, true)) {
                        $uniciteMail[] = $user['mail'];
                        if ($i === 0) {
                            $mail->addAddress($user['mail'], $user['pseudo']);
                        } else {
                            $mail->addBCC($user['mail'], $user['pseudo']);
                        }
                    }
                }
            } else {

                if (!filter_var($user, FILTER_VALIDATE_EMAIL)) {
                    Logger::warning('Le mail :' . $user . ' n\'est pas un mail valide.');
                } else {
                    if (!in_array($user, $uniciteMail, true)) {
                        $uniciteMail[] = $user;
                        if ($i === 0) {
                            $mail->addAddress($user);
                        } else {
                            $mail->addBCC($user);
                        }
                    }
                }
            }
        }
    }
}
