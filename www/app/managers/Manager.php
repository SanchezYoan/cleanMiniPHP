<?php

/**
 * Gestionnaire applicatif de base.
 *
 * Centralise la gestion des erreurs pour les services métiers et propose
 * un contrat de validation à surcharger dans les managers concrets.
 */
abstract class Manager
{

    /**
     * Liste des erreurs collectées lors des opérations métier.
     *
     * @var list<string>
     */
    protected array $errors = [];

    /**
     * Valider des données métier (à implémenter dans les classes filles).
     *
     * @param array<string, mixed> $data Jeu de données à contrôler.
     *
     * @return bool true si les données sont valides.
     */
    public function validate(array $data): bool
    {
        return false;
    }

    /**
     * Récupérer la liste des erreurs courantes.
     *
     * @return list<string> erreurs recensées.
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Vérifier la présence d'au moins une erreur.
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }
    
    /**
     * Ajouter une erreur et logger l'information.
     *
     * @param string $error    Message d'erreur.
     * @param bool   $sendMail Indique si le logger doit notifier par mail.
     */
    public function addError(string $error, bool $sendMail = false): void
    {
        if(!empty($error)) {
            Logger::error($error, $sendMail);
            $this->errors[] = $error;
        }
        
    }
    
    /**
     * Ajouter plusieurs erreurs et les journaliser.
     *
     * @param list<string> $errors   Messages d'erreur à ajouter.
     * @param bool         $sendMail Indique si le logger doit notifier par mail.
     *
     * @return void
     */
    public function addErrors(array $errors, bool $sendMail = false): void
    {
        foreach ($this->errors as $err) {
            Logger::error($err, $sendMail);
        }
        $this->errors = array_merge($this->errors,$errors);
    }
    
    /**
     * Retourner les erreurs concaténées.
     */
    public function errorsAsString(): string
    {
        if (!empty($this->errors)) {
            return implode(", ", $this->errors);
        }

        return "";
    }
    
    /**
     * Réinitialiser complètement l'état des erreurs.
     */
    public function resetErrors(): void
    {
        $this->errors = [];
    }

}
