<?php

/**
 * Fournir un accès simplifié à PDO via un singleton.
 *
 * Centralise la préparation, le binding, l'exécution et les helpers de fetch.
 * Effets de bord : ouvre des connexions et exécute des requêtes SQL.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */
class Database
{

    /**
     * Connexion PDO active.
     *
     * @var PDO|null
     */
    private ?PDO $connection = null;

    /**
     * Statement PDO courant.
     *
     * @var PDOStatement|null
     */
    private ?PDOStatement $statement = null;

    /**
     * Instance singleton.
     *
     * @var Database|null
     */
    private static ?Database $database = null;

    /**
     * Requête SQL en cours (debug).
     */
    private string $query = "";
    /**
     * Paramètres bindés (debug).
     *
     * @var array<string, mixed>
     */
    private array $params = [];
    /**
     * Message d'erreur courant (non exploité).
     */
    private string $error = "";


    /**
     * Instancier la connexion PDO selon l'environnement.
     *
     * @return void
     */
    private function __construct()
    {

        if (!isset($this->connection)) {

            $env = ENV;
            $dbName = Config::get("DB.$env.NAME");
            $dbUser = Config::get("DB.$env.USER");
            $dbPass = Config::get("DB.$env.PASS");
            $dbHost = Config::get("DB.$env.HOST");
            $dbPort = Config::get("DB.$env.PORT");
            $dbCharset = Config::get("DB.$env.CHARSET");

            $this->connection = new PDO('mysql:dbname=' . $dbName . ';host=' . $dbHost . ':' . $dbPort . ';charset=' . $dbCharset, $dbUser, $dbPass, [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::ATTR_PERSISTENT => true,
            ]);
        }
    }

    /**
     * Vérifier l'existence d'une table.
     *
     * @param string $name Nom de table.
     *
     * @return bool true si la table existe.
     */
    public function hasTable(string $name): bool
    {
        $this->prepare("SHOW TABLES LIKE '$name'")->execute();
        return $this->countRows() === 1;
    }
    /**
     * Vérifier l'existence d'une colonne.
     *
     * @param string $table  Nom de table.
     * @param string $column Nom de colonne.
     *
     * @return bool true si la colonne existe.
     */
    public function hasColumn(string $table, string $column): bool
    {
        $this->prepare("SHOW COLUMNS FROM $table LIKE '$column'")->execute();
        return $this->countRows() > 0;
    }


    /**
     * Obtenir l'instance singleton de la base.
     *
     * @return Database Instance unique.
     */
    public static function openConnection(): Database
    {
        if (!isset(self::$database)) {
            self::$database = new Database();
        }

        return self::$database;
    }


    /**
     * Préparer une requête SQL.
     *
     * @param string $query Requête SQL.
     *
     * @return Database Instance courante.
     */
    public function prepare(string $query): Database
    {
        //debug
        $this->query = $query;
        $this->params = [];

        $this->statement = $this->connection->prepare($query);

        return $this;
    }

    /**
     * Exécuter un statement préparé.
     *
     * Effets de bord : exécution SQL et écriture du log SQL si activé.
     *
     * @param array<string, mixed>|null $arr Paramètres de bind optionnels.
     *
     * @return bool true si succès.
     *
     * @throws PDOException Si l'exécution échoue.
     */
    public function execute($arr = null): bool
    {
        if (Config::get("DEBUG.SQL")) {
            $url = $_SERVER["REQUEST_URI"] ?? $_SERVER["HTTP_REFERER"] ?? "CRON";
            $day = date("d-m-Y");
            $myfile = fopen(BASE_DIR . "/sql_$day.txt", "a");
            fwrite($myfile, "\n### " . $url . " #####\n");
            fwrite($myfile, $this->debug());
            fwrite($myfile, "\n");
            fclose($myfile);
        }
        try {
            if ($arr === null) {
                $return = $this->statement->execute();
            } else {
                $this->params = $arr;
                $return = $this->statement->execute($arr);
            }
        } catch (PDOException $ex) {
            $message = $ex->getMessage() . "\n errorInfo : " . implode(", ", $this->errorInfo()) . " \n" . $this->debug();
            throw new PDOException($message, 4, $ex->getPrevious());
        }

        return $return;
    }


    /**
     * Binder une valeur sur un paramètre SQL.
     *
     * @param string   $param Nom du paramètre.
     * @param mixed    $value Valeur à binder.
     * @param int|null $type  Type PDO explicite.
     *
     * @return Database Instance courante.
     */
    public function bindValue(string $param, $value, $type = null): Database
    {

        $type = empty($type) ? self::getPDOType($value) : $type;
        $this->statement->bindValue($param, $value, $type);
        $this->params[$param] = $value;
        return $this;
    }

    /**
     * Binder une variable par référence.
     *
     * @param string $param Nom du paramètre.
     * @param mixed  $var   Variable à binder.
     *
     * @return Database Instance courante.
     */
    public function bindParam(string $param, &$var): Database
    {
        $type = self::getPDOType($var);
        $this->statement->bindParam($param, $var, $type);

        return $this;
    }

    /**
     * Définir la locale SQL pour les dates.
     *
     * @return void
     */
    public function setLang(): void
    {

        if (Config::get("LANG") === "EN") {
            $this->prepare("SET lc_time_names = 'en_US'");
        } else {
            $this->prepare("SET lc_time_names = 'fr_FR'");
        }
        $this->execute();
    }

    /**
     * Obtenir la requête SQL avec paramètres interpolés.
     *
     * @return string Requête SQL lisible.
     */
    public function debug()
    {
        if ($this->query) {
            $query = $this->query;
            foreach ($this->params as $key => $value) {
                $type = self::getPDOType($value);
                switch ($type) {
                    case  PDO::PARAM_NULL:
                        $value = "NULL";
                        break;
                    case PDO::PARAM_STR:
                        $value = "'{$value}'";
                        break;
                }
                $query = str_replace($key, $value, $query);
            }
        } else {
            $query = $this->statement->queryString;
        }

        return $query;
    }

    /**
     * Récupérer les informations d'erreur du statement.
     *
     * @return array<int, string|null> Tableau errorInfo PDO.
     */
    public function errorInfo(): array
    {
        return $this->statement->errorInfo();
    }


    /**
     * Récupérer la première valeur de la première colonne.
     *
     * @return mixed Valeur ou false si vide.
     */
    public function fetchItem()
    {
        return current($this->statement->fetchAll(PDO::FETCH_COLUMN, 0));
    }

    /**
     * Récupérer une colonne complète.
     *
     * @return array<int, mixed> Valeurs de colonne.
     */
    public function fetchColumn(): array
    {
        return $this->statement->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * Récupérer toutes les lignes en associatif.
     *
     * @return array<int, array<string, mixed>> Liste de lignes.
     */
    public function fetchAllAssociative(): array
    {
        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer la prochaine ligne en associatif.
     *
     * @return array<string, mixed>|false Ligne ou false si vide.
     */
    public function fetchAssociative()
    {
        return $this->statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer toutes les lignes en objets.
     *
     * @return array<int, object> Liste d'objets.
     */
    public function fetchAllObject(): array
    {
        return $this->statement->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Récupérer la prochaine ligne en objet.
     *
     * @return object|false Objet ou false si vide.
     */
    public function fetchObject(): object
    {
        return $this->statement->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Récupérer toutes les lignes en tableau mixte (numérique + associatif).
     *
     * @return array<int, array<int|string, mixed>> Liste de lignes.
     */
    public function fetchAllBoth(): array
    {
        return $this->statement->fetchAll(PDO::FETCH_BOTH);
    }

    /**
     * Récupérer la prochaine ligne en tableau mixte.
     *
     * @return array<int|string, mixed>|false Ligne ou false si vide.
     */
    public function fetchBoth(): array
    {
        return $this->statement->fetch(PDO::FETCH_BOTH);
    }

    /**
     * Récupérer l'identifiant auto-incrémenté de la dernière insertion.
     *
     * @return int Identifiant inséré.
     * @see    http://php.net/manual/en/pdo.lastinsertid.php
     */
    public function lastInsertedId(): int
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Démarrer une transaction.
     *
     * @return void
     * @see    http://php.net/manual/en/pdo.begintransaction.php
     */
    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    /**
     * Valider une transaction.
     *
     * @return void
     * @see    http://php.net/manual/en/pdo.commit.php
     */
    public function commit(): void
    {
        $this->connection->commit();
    }

    /**
     * Annuler une transaction.
     *
     * @return void
     * @see    http://php.net/manual/en/pdo.rollback.php
     */
    public function rollBack(): void
    {
        $this->connection->rollBack();
    }

    /**
     * Obtenir le nombre de lignes affectées.
     *
     * @return int Nombre de lignes affectées.
     * @see    http://php.net/manual/en/pdostatement.rowcount.php
     */
    public function countRows(): int
    {
        return $this->statement->rowCount();
    }


    /**
     * Déterminer le type PDO d'une valeur.
     *
     * @param mixed $value Valeur à analyser.
     *
     * @return int Constante PDO::PARAM_*.
     */
    private static function getPDOType(mixed $value): int
    {
        if (is_null($value)) {
            return 0;
        }
        return match ($value) {
            is_int($value) => PDO::PARAM_INT,
            is_bool($value) => PDO::PARAM_BOOL,
            is_null($value) => PDO::PARAM_NULL,
            default => PDO::PARAM_STR,
        };
    }

    /**
     * Fermer la connexion PDO.
     *
     * @return void
     * @see    http://php.net/manual/en/pdo.connections.php
     */
    public static function closeConnection(): void
    {
        if (isset(self::$database)) {
            self::$database->connection = null;
            self::$database->statement = null;
        }
    }

    /**
     * Exposer l'instance PDO.
     *
     * @return PDO|null Connexion active.
     */
    public function getPdo()
    {
        return $this->connection;
    }
}
