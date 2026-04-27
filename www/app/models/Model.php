<?php

/**
 * Classe de base des modèles applicatifs.
 *
 * Fournit la gestion d'erreurs commune et quelques helpers de requêtes simples
 * pour les classes métiers spécialisées.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class Model implements jsonSerializable {

    /**
     * Colonnes autorisées pour les filtres génériques.
     *
     * @var list<string>
     */
    protected static array $ALLOWED_COLUMNS = ["id", "email", "login", "level"];

    /**
     * Opérateurs SQL autorisés dans les filtres génériques.
     *
     * @var list<string>
     */
    protected static array $ALLOWED_OPERATORS = ["=", ">", "<", "LIKE"];

    /**
     * Liste des erreurs de validation ou de traitement.
     *
     * @var list<string>
     */
    protected array $errors = [];
    
    /**
     * Nom de la table associée au modèle concret.
     */
    protected string $table = "";
    
    /**
     * Récupérer toutes les lignes filtrées par colonne/valeur autorisées.
     *
     * @param string $column   Colonne autorisée (id, email, login, level).
     * @param string $operator Opérateur SQL autorisé (=, >, <, LIKE).
     * @param string $value    Valeur de comparaison.
     *
     * @return array Liste d'enregistrements en objets stdClass.
     *
     * @throws InvalidArgumentException Si la colonne ou l'opérateur n'est pas autorisé.
     */
    public function getAllFilterBy(string $column = "", string $operator = "", string $value = ""): array
    {
        if (!in_array($column, self::$ALLOWED_COLUMNS, true)) {
            throw new InvalidArgumentException("Colonne non autorisée : {$column}");
        }

        if (!in_array($operator, self::$ALLOWED_OPERATORS, true)) {
            throw new InvalidArgumentException("Opérateur non autorisé : {$operator}");
        }

        $db = Database::openConnection();
        $db->prepare("SELECT * FROM {$this->table} WHERE {$column} {$operator} :value")
            ->bindValue(":value", $value)
            ->execute();
        
        return $db->fetchAllObject();
    }

    /**
     * Initialiser le modèle.
     */
    public function __construct()
    {
    }

    /**
     * Récupérer les erreurs courantes.
     *
     * @return list<string> erreurs recensées.
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Renvoyer les erreurs concaténées.
     *
     * @return string Chaîne formatée des erreurs.
     */
    public function errorsAsString(): string
    {
        if (!empty($this->errors)) {
            return implode(", ", $this->errors);
        }

        return "";
    }

    /**
     * Vérifier la présence d'au moins une erreur.
     *
     * @return bool true si des erreurs existent.
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }
    
    /**
     * Ajouter une erreur et journaliser l'information.
     *
     * @param string $error    Message d'erreur.
     * @param bool   $sendMail Indique si le logger doit notifier par mail.
     *
     * @return void
     */
    public function addError(string $error, bool $sendMail = false): void
    {
        Logger::error($error, $sendMail);
        $this->errors[] = $error;
        
    }
    
    /**
     * Remplacer la liste d'erreurs par un lot donné.
     *
     * @param list<string> $errors Erreurs à enregistrer.
     *
     * @return void
     */
    public function addErrors(array $errors): void
    {
        foreach ($this->errors as $err) {
            Logger::error($err, false);
        }
        $this->errors = $errors;
    }
    
    /**
     * Sérialiser le modèle au format JSON.
     *
     * Les modèles concrets doivent surcharger cette méthode.
     *
     * @return array Données sérialisées.
     */
    public function jsonSerialize(): array
    {
        return [];
    }

    /**
     * Charger les données du modèle.
     *
     * @return $this Instance actuelle.
     */
    public function load(): Model
    {
        
        return $this;
    }
    
}
