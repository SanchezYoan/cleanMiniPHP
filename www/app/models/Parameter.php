<?php

/**
 * Représenter un paramètre applicatif stocké en base.
 */
class Parameter extends Model
{
    /**
     * Nom du paramètre.
     *
     * @var string|null
     */
    private $nom;

    /**
     * Valeur du paramètre.
     *
     * @var string|int|null
     */
    private $valeur;

    /**
     * Clé du login super admin.
     */
    public const _LOGIN_SUPER_ADMIN_ = 'LOGIN_SUPER_ADMIN';

    /**
     * Clé du mot de passe super admin.
     */
    public const _PWD_SUPER_ADMIN_ = 'PWD_SUPER_ADMIN';

    /**
     * Récupérer une valeur de paramètre par son nom.
     *
     * @param string $nom Nom du paramètre.
     *
     * @return mixed|false Valeur récupérée ou false si indisponible.
     */
    public static function getUnique(string $nom)
    {
        Logger::debug("Recherche du paramètre : " . $nom);
        try {
            $db = Database::openConnection();
            $db->prepare("SELECT value FROM parameters WHERE name = :nom LIMIT 1")
               ->bindValue(':nom', $nom, PDO::PARAM_STR)
               ->execute();

            if ($db->countRows() === 1) {
                return $db->fetchItem();
            }
        } catch (Exception $e) {
            if ($e->getCode() === '42S02') { // on teste si la table existe, si non on la crée
                return self::createParametersTable();
            } else {
                Logger::error("Impossible de récupérer le paramètre : " . $e->getMessage());
            }
        }

        return false;
    }

    /**
     * Initialiser un paramètre en chargeant sa valeur.
     *
     * @param string $nom Nom du paramètre.
     */
    public function __construct(string $nom)
    {
        parent::__construct();
        Logger::debug("Recherche du paramètre : " . $nom);
        $value = Parameter::getUnique($nom);
        if ($value !== false) {
            $this->nom = $nom;
            $this->valeur = $value;
        }
    }

    /**
     * Créer la table parameters si elle n'existe pas.
     *
     * @return void
     */
    public static function createParametersTable(): void
    {
        if (class_exists("Database") && $db = Database::openConnection()) {
            $db->prepare("CREATE TABLE `parameters` (
                          `name` varchar(100) NOT NULL,
                          `value` varchar(100) DEFAULT NULL,
                          PRIMARY KEY (`name`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;")
               ->execute();
        }
    }

    /**
     * Définir le nom du paramètre.
     *
     * @param string $nom Nom du paramètre.
     *
     * @return Parameter Instance courante.
     */
    public function setNom(string $nom): Parameter
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Récupérer le nom du paramètre.
     *
     * @return string|null Nom du paramètre.
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Définir la valeur du paramètre.
     *
     * @param string|int|null $valeur Valeur à stocker.
     *
     * @return Parameter Instance courante.
     */
    public function setValeur($valeur): Parameter
    {
        $this->valeur = $valeur;

        return $this;
    }

    /**
     * Récupérer la valeur du paramètre.
     *
     * @return int|string|null Valeur stockée.
     */
    public function getValeur()
    {
        return $this->valeur;
    }

    /**
     * Sauvegarder la valeur du paramètre en base.
     *
     * @return bool true si la mise à jour réussit.
     */
    public function save(): bool
    {
        try {
            $db = Database::openConnection();
            $db->prepare(
                "UPDATE parameters SET value = :valeur WHERE name = :nom")
                ->bindValue(":nom", $this->getNom())
                ->bindValue(":valeur", $this->getValeur())
                ->execute();

            return true;
        } catch (Exception $e) {
            Logger::exception($e);
        }

        return false;
    }

    /**
     * Raccourci d'accès à un paramètre.
     *
     * @param string $nom Nom du paramètre.
     *
     * @return int|string|null Valeur du paramètre.
     */
    public static function get(string $nom)
    {
        return (new Parameter($nom))->getValeur();
    }
}
