<?php


/**
 * Valider des données via un ensemble de règles.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */
class Validation
{
    /**
     * Liste des erreurs de validation.
     *
     * @var list<string>
     */
    private array $errors = [];

    /**
     * Indique si une erreur réseau est détectée.
     */
    private bool $networkError = false;

    /**
     * Langue active (FR/EN).
     */
    private ?string $lang = "FR";

    /**
     * Messages personnalisés par règle.
     *
     * @var array<string, string>
     */
    private array $ruleMessages = [];

    /**
     * Initialiser le validateur.
     *
     * @param string|null $lang Langue (FR/EN).
     */
    public function __construct(?string $lang = null)
    {
        if (!empty($lang)) {
            $lang = strtoupper($lang);
            if ($lang !== "FR") {
                $lang = "EN";
            }
            $this->lang = $lang;
        }
    }

    /**
     * Définir la langue du validateur.
     *
     * @param string $lang Langue (FR/EN).
     *
     * @return Validation Instance courante.
     */
    public function setLang(string $lang): Validation
    {
        $this->lang = strtoupper($lang);
        return $this;
    }

    /**
     * Lancer la validation d'un jeu de données.
     *
     * @param array<string, array{0:mixed,1:string}> $data Tableau valeur + règles.
     * @param bool                                  $skip Stopper à la première erreur.
     *
     * @return bool true si toutes les règles passent.
     *
     * @throws Exception Si une règle n'existe pas.
     */
    public function validate(array $data, bool $skip = false): bool
    {
        $passed = true;

        foreach ($data as $placeholder => $rules) {
            $value = $rules[0];
            $rules = explode('|', $rules[1]);

            $hasEmptyFile = isset($value["tmp_name"]) && empty($value["tmp_name"]);
            // no need to validate the value if the value is empty and not required
            if (!$this->isRequired($rules) && ($this->isEmpty($value) || $hasEmptyFile)) {
                continue;
            }
            // it doesn't make sense to continue and validate the rest of rules on an empty & required value.
            // instead add error, and skip this value.
            if ($this->isRequired($rules) && (null === $value || "" === $value)) {
                $this->addError("required", $placeholder, $value);
                $passed = false;
                continue;
            }

            foreach ($rules as $rule) {
                $method = $rule;
                $args   = [];

                // if it was empty and required or not required,
                // it would be detected by the previous ifs
                if ($rule === "required") {
                    continue;
                }

                if ($this->isruleHasArgs($rule)) {

                    // get arguments for rules like in max(), min(), ..etc.
                    $method = $this->getRuleName($rule);
                    $args   = $this->getRuleArgs($rule);
                }

                if (!method_exists($this, $method)) {
                    throw new Exception("Method doesnt exists: " . $method);
                }

                if (!call_user_func_array([$this, $method], [$value, $args])) {
                    if ($this->networkError) {
                        $this->errors = ["Certaines fonctionnalités sont indisponibles actuellement. Veuillez réessayer ultérieurement."];
                    } else {
                        $this->addError($method, $placeholder, $value, $args);
                    }

                    $passed = false;

                    if ($skip) {
                        return false;
                    }
                }
            }
        }

        // possible change is to return the current validation object,
        // and use passes() instead.
        return $passed;
    }

    /**
     * Déterminer si la règle "required" est présente.
     *
     * @param array<int, string> $rules Règles à analyser.
     *
     * @return bool true si "required" est présent.
     */
    private function isRequired(array $rules): bool
    {
        return in_array("required", $rules, true);
    }

    /**
     * Déterminer si une valeur est considérée vide.
     *
     * @param mixed $value Valeur à tester.
     *
     * @return bool true si vide.
     */
    private function isEmpty($value): bool
    {
        return empty($value);
    }

    /**
     * Ajouter une erreur de validation.
     *
     * @param string $rule        Règle en échec.
     * @param string $placeholder Nom du champ.
     * @param mixed  $value       Valeur fournie.
     * @param array  $args        Arguments de la règle.
     *
     * @return void
     */
    private function addError($rule, $placeholder, $value, $args = []): void
    {
        if (isset($this->ruleMessages[$rule])) {
            $this->errors[] = $this->ruleMessages[$rule];
        } else {

            // get the default message for the current $rule
            $message = $this->defaultMessages($rule);

            if (isset($message)) {

                // if $message is set to empty string,
                // this means the error will be added inside the validation method itself
                // check attempts()
                if (trim($message) !== "") {

                    // replace placeholder, value, arguments with their values
                    $replace = ['{placeholder}', '{value}'];
                    if ($rule === "fileSize" && isset($value["size"])) {
                        $value = $value["size"];
                    } else {
                        $value = (is_string($value) || is_int($value)) ? $value : "";
                    }
                    $with  = array_merge([$placeholder, $value], $args);
                    $count = count($args);

                    // arguments will take the shape of: {0} {1} {2} ...
                    for ($i = 0; $i < $count; $i++) {
                        $replace[] = "{{$i}}";
                    }
                    if ($rule === "fileSize") {
                        $with[1] = $this->humanFileSize($with[1], 2);
                    }
                    $this->errors[] = str_replace($replace, $with, $message);
                }
            } else {

                // if no message defined, then use this one.
                $this->errors[] = "La valeur entrée pour le champs " . $placeholder . " est incorrecte";
            }
        }
    }

    /**
     * Récupérer le message par défaut d'une règle.
     *
     * @param string $rule Nom de règle.
     *
     * @return string|null Message ou null.
     */
    private function defaultMessages(string $rule): ?string
    {
        $messages = [
            "FR" => [
                "required"           => "{placeholder} ne peut être vide",
                "min"                => "{placeholder} doit être au moins de {0} caractères",
                "max"                => "{placeholder} doit être inférieur ou égale à {0} caractères",
                "rangeNum"           => "{placeholder} doit être compris entre {0} et {1}",
                "integer"            => "{placeholder} doit être un chiffre rond",
                "inArray"            => "{placeholder} non reconnue",
                "isArray"            => "{placeholder} doit être un array",
                "alphaNum"           => "Seuls les lettres et chiffres sont autorisés pour {placeholder}",
                "isNumeric"          => "Seuls les chiffres sont autorisés pour {placeholder}",
                "isTimestamp"        => "{placeholder} n'est pas un format de date valide : integer ou ISO 8601",
                "alpha"              => "Seuls les lettres, espaces et tirets sont autorisés pour {placeholder}",
                "alphaNumWithSpaces" => "Seuls les lettres, espaces et chiffres sont autorisés pour {placeholder}",
                "validatePpassword"  => "{placeholder} : Le mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère special (&, @, ?, €, ;, …)",
                "validateShortLink"  => "{placeholder} : Le short link ne doit contenir que des lettres et des chiffres sans espace",
                "equals"             => "{placeholder}s ne sont pas égaux",
                "notEqual"           => "{placeholder} ne peut être égale à {0}",
                "email"              => "Email incorrect. Vérifiez votre saisie",
                "unique"             => "{placeholder} existe déjà",
                "emailUnique"        => "{placeholder} : Cette adresse mail est déjà utilisée",
                "credentials"        => "{placeholder} : Aucun compte trouvé avec cette combinaison",
                "attempts"           => "{placeholder} : Trop de tentatives consécutives. Recommencez plus tard",
                "fileUnique"         => "{placeholder} : Ce fichier existe déjà",
                "fileUploaded"       => "{placeholder} : Fichier à envoyé invalide ",
                "nofileErrors"       => "{placeholder} : Erreurs rencontrées avec le fichier envoyé",
                "fileSize"           => "{placeholder} : fichier trop lourd : {value}",
                "imageSize"          => "{placeholder} : image trop grande",
                "imageMinSize"       => "{placeholder} : La longeur et hauteur de votre image doivent être supérieures à {0} par {1} pixels",
                "imageMaxSize"       => "{placeholder} : La longeur et hauteur de votre image doivent être inférieures à {0} par {1} pixels",
                "mimeType"           => "Format de fichier invalide",
                "fileExtension"      => "Format de fichier invalide",
                "isDecimal"          => "{placeholder} : Seuls les nombres sont acceptés",
                "isURL"              => "{placeholder} : n'est pas une URL correcte",
                "isDateTimeSQL"      => "{placeholder} : n'est pas au bon format",
                "isDateSQL"          => "{placeholder} : n'est pas au bon format",
                "isDateFr"           => "{placeholder} : n'est pas une date correcte (JJ/MM/AAAA)",
                "isDateTimeFr"       => "{placeholder} : n'est pas une date correcte (JJ/MM/AAAA H:M)",
                "pattern"            => "{placeholder} : ne correspond pas au format {0}",
                "exists"             => "{placeholder} : {value} n'existe pas",
                "validateMessage"    => "{placeholder} Votre contenu est trop long, merci de le réduire. (Cela peut être également du à une image trop volumineuse)",
            ],
            "EN" => [
                "required"           => "{placeholder} cant' be empty",
                "min"                => "{placeholder} must be at least {0} characters",
                "max"                => "{placeholder} must be less than {0} characters",
                "rangeNum"           => "{placeholder} must be between {0} and {1}",
                "integer"            => "{placeholder} must be an integer",
                "inArray"            => "{placeholder} non reconnue",
                "isArray"            => "{placeholder} must be an array",
                "alphaNum"           => "Only letters and numbers are allowed for {placeholder}",
                "isNumeric"          => "Only numbers are allowed for {placeholder}",
                "isTimestamp"        => "{placeholder} is not a valid date format : integer or ISO 8601",
                "alpha"              => "Only letters, spaces and dashes are allowed for {placeholder}",
                "alphaNumWithSpaces" => "Only letters, spaces and numbers are allowed for {placeholder}",
                "validatePpassword"  => "{placeholder} : The password must contain at least one lowercase, uppercase, number and special (&, @, ?, €, ;, …)",
                "validateShortLink"  => "{placeholder} : The short link must only contain letters and numbers without spaces",
                "equals"             => "{placeholder}s are not equal",
                "notEqual"           => "{placeholder} cant' be equal to {0}",
                "email"              => "Invalid email address. Check your input",
                "unique"             => "{placeholder} already exists",
                "emailUnique"        => "{placeholder} : This email address is already in use",
                "credentials"        => "{placeholder} : No account found with this those credentials",
                "attempts"           => "{placeholder} : Too many consecutive attempts. Try again later",
                "fileUnique"         => "{placeholder} : This file already exists",
                "fileUploaded"       => "{placeholder} : Invalid file uploaded",
                "nofileErrors"       => "{placeholder} : Errors encountered with the file uploaded",
                "fileSize"           => "{placeholder} : file too large : {value}",
                "imageSize"          => "{placeholder} : image too large",
                "imageMinSize"       => "{placeholder} : The longeur and height of your image must be greater than {0} by {1} pixels",
                "imageMaxSize"       => "{placeholder} : The longeur and height of your image must be less than {0} by {1} pixels",
                "mimeType"           => "Format of file invalid",
                "fileExtension"      => "Format of file invalid",
                "isDecimal"          => "{placeholder} : Only numbers are allowed",
                "isURL"              => "{placeholder} : is not a valid URL",
                "isDateTimeSQL"      => "{placeholder} : is an invalid format",
                "isDateSQL"          => "{placeholder} : is an invalid format",
                "isDateFr"           => "{placeholder} : is not a valid date (JJ/MM/AAAA)",
                "isDateTimeFr"       => "{placeholder} : is not a valid date (JJ/MM/AAAA H:M)",
                "pattern"            => "{placeholder} : doesn't match the format {0}",
                "exists"             => "{placeholder} : {value} doesn't exist",
                "validateMessage"    => "{placeholder} Your content is too long, please reduce it. (It may be an image too volumineuse)",
            ],
        ];

        return $messages[$this->lang][$rule] ?? null;
    }

    /**
     * Déterminer si une règle possède des arguments (ex: max(4)).
     *
     * @param string $rule Règle brute.
     *
     * @return bool true si arguments présents.
     */
    private function isruleHasArgs(string $rule): bool
    {
        return isset(explode('(', $rule)[1]);
    }

    /**
     * Extraire le nom de règle sans arguments.
     *
     * @param string $rule Règle brute.
     *
     * @return string Nom de règle.
     */
    private function getRuleName($rule): string
    {
        return explode('(', $rule)[0];
    }

    /**
     * Extraire les arguments d'une règle.
     *
     * @param string $rule Règle brute.
     *
     * @return array<int, string> Arguments.
     */
    private function getRuleArgs(string $rule): array
    {
        $argsWithBracketAtTheEnd = explode('(', $rule)[1];
        $args                    = rtrim($argsWithBracketAtTheEnd, ')');
        $args                    = preg_replace('/\s+/', '', $args);

        // as result of an empty array coming from user input
        // $args will be empty string,
        // So, using explode(',', empty string) will return array with size = 1
        // return empty($args)? []: explode(',', $args);
        return explode(',', $args);
    }

    /**
     * Ajouter un message personnalisé pour une règle.
     *
     * @param string $rule    Nom de règle.
     * @param string $message Message personnalisé.
     *
     * @return void
     */
    public function addRuleMessage($rule, $message): void
    {
        $this->ruleMessages[$rule] = $message;
    }

    /**
     * Vérifier si la validation a réussi.
     *
     * @return bool true si aucune erreur.
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Récupérer toutes les erreurs.
     *
     * @return list<string> Erreurs.
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Réinitialiser les erreurs.
     *
     * @return void
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * Vérifie la validitié d'un téléphone
     * format accepté
     *
     * @param $tel
     *
     * @return bool
     */
    private function isTelephone($tel): bool
    {
        return (bool)preg_match('/^[+0-9. ()\/-]*$/', $tel);
    }

    /** *********************************************** **/
    /** **************    Validations    ************** **/
    /** *********************************************** **/

    private function isDecimal($value)
    {
        return preg_match('/^[0-9]+(\.[0-9]*)?$/', $value);
    }

    /**
     * Vérifier qu'une valeur n'est pas vide.
     *
     * @param mixed $value Valeur à tester.
     *
     * @return bool true si non vide.
     */
    private function required($value): bool
    {
        return !$this->isEmpty($value);
    }

    /**
     * Vérifier la longueur minimale d'une chaîne.
     *
     * @param string $value Valeur à tester.
     * @param array  $args  (min)
     *
     * @return bool true si la longueur est suffisante.
     */
    private function min($value, array $args): bool
    {
//        if(is_int($value)){
//            return $value >= (int)$args[0];
//        }
        return is_string($value) && mb_strlen($value, 'UTF-8') >= (int)$args[0];
    }

    /**
     * Vérifier la longueur maximale d'une chaîne.
     *
     * @param string $value Valeur à tester.
     * @param array  $args  (max)
     *
     * @return bool true si la longueur est valide.
     */
    private function max($value, array $args): bool
    {
        return is_string($value) && mb_strlen($value, 'UTF-8') <= (int)$args[0];
    }

    /**
     * Valider la longueur d'un message en tenant compte des images base64.
     *
     * @param string $value Valeur à tester.
     * @param array  $args  (max)
     *
     * @return bool true si la longueur est valide.
     */
    private function validateMessage($value, array $args): bool
    {
        $maxLength = (int)$args[0];

        // Check if the value is a string
        if (!is_string($value)) {
            return false;
        }

        // Check the string length
        if (mb_strlen($value, 'UTF-8') <= $maxLength) {
            return true; // Valid length
        }

        return false;
    }

    /**
     * Vérifier qu'un nombre est dans un intervalle.
     *
     * @param int   $num  Valeur à tester.
     * @param array $args (min,max)
     *
     * @return bool true si dans l'intervalle.
     */
    private function range(int $num, array $args): bool
    {
        return $num >= (int)$args[0] && $num <= (int)$args[1];
    }

    /**
     * Vérifier qu'une valeur est un entier.
     *
     * @param string|int $value Valeur à tester.
     *
     * @return bool true si entier.
     */
    private function integer($value): bool
    {
        return is_int($value);
    }

    /**
     * Vérifier qu'une valeur est un float.
     *
     * @param string|float $value Valeur à tester.
     *
     * @return bool true si float.
     */
    private function float($value): bool
    {
        return is_float($value);
    }

    private function bool($value): bool
    {
        return is_bool($value);
    }

    /**
     * Vérifier qu'une valeur est contenue dans un tableau.
     *
     * @param string|array $value Valeur(s) à tester.
     * @param array        $arr   Liste autorisée.
     *
     * @return bool true si toutes les valeurs sont présentes.
     */
    private function inArray($value, array $arr): bool
    {
        if (is_array($value)) {
            foreach ($value as $val) {
                if (!in_array($val, $arr, true)) {
                    return false;
                }
            }

            return true;
        }

        return in_array($value, $arr, true);
    }

    private function isArray($value): bool
    {
        return is_array($value);
    }

    /**
     * Vérifier qu'une valeur est alphanumérique.
     *
     * @param mixed $value Valeur à tester.
     *
     * @return bool true si alphanumérique.
     */
    private function alphaNum($value): bool
    {
        return is_string($value) && preg_match('/\A[a-z0-9-_]+\z/i', $value);
    }

    private function pattern($value, array $pattern): bool
    {
        return is_string($value) && preg_match('/\A' . $pattern[0] . '+\z/i', $value);
    }

    private function isNumeric($value): bool
    {
        return is_numeric($value);
    }

    /**
     * Vérifier qu'une valeur est alphanumérique avec espaces.
     *
     * @param mixed $value Valeur à tester.
     *
     * @return bool true si conforme.
     */
    private function alphaNumWithSpaces($value): bool
    {
        return is_string($value) && preg_match('/\A[-a-zA-Z0-9çÇâàäéèêëìîïòöôœùûüõãÕÃÂÀÄÉÈÊËÌÎÏÒÖÔŒÙÛÜ\'`‘ ]+\z/u', $value);
    }

    private function isDateFR(string $date): bool
    {
        return is_string($date) && preg_match("/^\d{2}\/\d{2}\/\d{4}/", $date);
    }

    private function isDateTimeFR(string $date): bool
    {
        return is_string($date) && preg_match("/^\d{2}\/\d{2}\/\d{4}\s\d{2}\:\d{2}/", $date);
    }

    private function isDateSQL(string $date): bool
    {
        return is_string($date) && preg_match("/^\d{4}-\d{2}-\d{2}/", $date);
    }

    private function isDateTimeSQL(string $date): bool
    {
        return is_string($date) && preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/", $date);
    }

    /**
     * Vérifier un timestamp ou format ISO/SQL.
     *
     * @param mixed $date Valeur à tester.
     *
     * @return bool true si conforme.
     */
    private function isTimestamp(mixed $date): bool
    {
        if (!is_int($date) && !is_string($date)) {
            return true;
        }
        if (strlen((string)$date) > 30) {
            return false;
            // 22:22:22 22:22:22
        }
        // is only digits
        if (is_string($date) && preg_match('/^\d+$/', $date)) {
            if (strtotime(date('d-m-Y H:i:s', $date)) === (int)$date) {
                return true;
            }
        }

        // $date is ISO 8601 format or Y-m-d H:i:s format
        if (is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date)) {
            return true;
        }

        if (is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d{1,3})?Z$/', $date)) {
            return true;
        }

        return false;
    }

    /**
     * Vérifier qu'une valeur ne contient que des lettres.
     *
     * @param mixed $value Valeur à tester.
     *
     * @return bool true si conforme.
     */
    private function alpha($value): bool
    {
        return is_string($value) && preg_match('/^[ \-\'\p{L}]+$/ui', $value);
    }

    private function isURL($value): bool
    {
        // check if value is url
        return is_string($value) && filter_var($value, FILTER_VALIDATE_URL);
    }

    /**
     * Vérifier l'égalité stricte avec une valeur.
     *
     * @param string $value Valeur à tester.
     * @param array  $args  (value)
     *
     * @return bool true si égal.
     */
    private function equals($value, array $args): bool
    {
        return $value === $args[0];
    }

    /**
     * Vérifier l'inégalité stricte avec une valeur.
     *
     * @param string $value Valeur à tester.
     * @param array  $args  (value)
     *
     * @return bool true si différent.
     */
    private function notEqual(string $value, array $args): bool
    {
        return $value !== $args[0];
    }

    /**
     * Vérifier un email.
     *
     * @param string $email Email à tester.
     *
     * @return bool true si valide.
     */
    private function email($email): bool
    {
        return is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Valider un mot de passe (minuscule, majuscule, chiffre, spécial).
     *
     * @param string $value Mot de passe à tester.
     *
     * @return bool true si conforme.
     */
    private function validatePassword($value): bool
    {
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/';

        return is_string($value) && preg_match($pattern, $value);
    }

    /**
     * Valider un short link alphanumérique.
     *
     * @param string $value Short link à tester.
     *
     * @return bool true si conforme.
     */
    private function validateShortLink($value): bool
    {
        $pattern = '/^[a-zA-Z0-9]+$/';

        return is_string($value) && preg_match($pattern, $value);
    }


    /** *********************************************** **/
    /** ************   Default Messages     *********** **/
    /** *********************************************** **/

    private function exists($value, array $args): bool
    {
        if (is_array($value) || is_object($value)) {
            return false;
        }
        if (class_exists(Database::class) && count($args) === 2) {
            $table   = strip_tags($args[0]);
            $column  = strip_tags($args[1]);
            $pattern = '/\A[a-z0-9-_]+\z/i';
            if (!preg_match($pattern, $table)) {
                return false;
            }
            if (!preg_match($pattern, $column)) {
                return false;
            }
            $db = Database::openConnection();
            $db->prepare("SELECT 1 FROM {$table} WHERE {$column} = :needle LIMIT 1")->execute(['needle' => $value]);
            return $db->countRows() === 1;
        }

        return false;
    }
    
    private function unique($value, array $args): bool
    {
        if (is_array($value) || is_object($value)) {
            return false;
        }
        if (class_exists(Database::class) && ((count($args) === 2) || (count($args) === 3))) {
            $table  = strip_tags($args[0]);
            $column = strip_tags($args[1]);
            if (count($args) === 3) {
                $sql_id = ' AND id <> ' . strip_tags($args[2]);
            } else {
                $sql_id = '';
            }
            $pattern = '/\A[a-z0-9-_]+\z/i';
            if (!preg_match($pattern, $table)) {
                return false;
            }
            if (!preg_match($pattern, $column)) {
                return false;
            }
            $db = Database::openConnection();
            $db->prepare("SELECT 1 FROM {$table} WHERE {$column} = :needle {$sql_id} LIMIT 1")->execute(['needle' => $value]);
            return $db->countRows() === 0;
        }
        
        return false;
    }
}