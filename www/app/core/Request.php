<?php

/**
 * Encapsuler les données de requête HTTP.
 *
 * La classe centralise les superglobales PHP, les en-têtes, le corps de
 * requête (formulaire, fichiers, flux JSON/url-encoded) et expose des
 * helpers de lecture sûrs pour les contrôleurs.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class Request
{
    
    /**
     * Paramètres déduits du routage (contrôleur, action, arguments).
     *
     * @var array{controller:mixed, action:mixed, args:mixed}
     */
    public mixed $params = [
        "controller" => null, "action" => null, "args" => null,
    ];
    
    /**
     * Valeur brute de l'en-tête Content-Type.
     */
    public string $type = "";
    
    /**
     * Données POST fusionnées avec les fichiers uploadés et le flux brut.
     *
     * @var array<string, mixed>
     */
    public array $data = [];
    
    /**
     * Paramètres de chaîne de requête.
     *
     * @var array<string, mixed>
     */
    public array $query = [];
    
    /**
     * URL complète de la requête (avec host mais sans protocole explicite).
     */
    public string $url = "";
    
    
    /**
     * IP d'origine de la requête, si présente.
     */
    private ?string $remoteAddress = null;
    
    /**
     * En-têtes HTTP collectés ou false si indisponibles.
     *
     * @var array<string, string|array>|bool
     */
    private array|bool $headers = false;
    
    
    /**
     * Construire une requête depuis les superglobales PHP.
     *
     * @param array $config Configuration utilisateur (params forcés, etc.).
     */
    public function __construct($config = [])
    {
        $this->query         = $_GET;
        $this->remoteAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $this->type          = $_SERVER['CONTENT_TYPE'] ?? "";
        $this->params        += $config["params"] ?? [];
        $this->url           = ($this->isSSL() ? "https://" : "http://") . $this->currentUrl();
        $this->setHeaders(apache_request_headers());
        $this->data = $this->mergeData($_POST, $_FILES);
        
    }
    
    
    /**
     * Vérifier la présence d'un en-tête HTTP par son nom exact.
     */
    public function hasHeader(string $name): bool
    {
        return array_key_exists($name, $this->headers);
    }
    
    /**
     * Récupérer tous les en-têtes ou une clé spécifique.
     *
     * @param string|null $key Nom d'en-tête recherché (respecte la casse reçue).
     *
     * @return array|string Tableau complet ou valeur de l'en-tête, chaîne vide si absent.
     */
    public function getHeaders(?string $key = null): array|string
    {
        if (!empty($this->headers) && !empty($key)) {
            return $this->headers[$key] ?? "";
        }
        return $this->headers;
    }
    
    /**
     * Remplacer les en-têtes HTTP connus pour la requête.
     *
     * @param array<string, mixed> $headers Tableau d'en-têtes fourni par le serveur.
     *
     * @return Request Instance courante (fluide).
     */
    public function setHeaders(array $headers): Request
    {
        $this->headers = $headers;
        return $this;
    }
    
    
    /**
     * Fusionner données POST, fichiers et flux brut.
     *
     * Les valeurs chaînes sont trimées. Le flux brut (php://input) est
     * interprété en JSON ou en query string puis fusionné. En cas de collision
     * entre clés POST/FILES, la dernière occurrence prévaut.
     *
     * @param array<string, mixed> $post  Superglobale $_POST.
     * @param array<string, mixed> $files Superglobale $_FILES.
     *
     * @return array<string, mixed> Tableau consolidé des données reçues.
     */
    private function mergeData(array $post, array $files): array
    {
        foreach ($post as $key => $value) {
            if (is_string($value)) {
                $post[$key] = trim($value);
            }
        }
        $data = array_merge($files, $post);
        
        //if ($this->type === 'application/json') {
            $rawJson = file_get_contents("php://input");
            if(!empty($rawJson)){
                if(str_contains($rawJson, "{")){
                    $input = json_decode($rawJson, true);
                } else {
                    parse_str($rawJson, $input);
                }
                if(!empty($input)) {
                    $data = array_merge($data, $input);
                }
            }
        //}
        
        return $data;
  
    }
    
    /**
     * Compter les champs dans $this->data en excluant certaines clés.
     *
     * @param array<int, string> $exclude Liste de clés à ignorer.
     *
     * @return int Nombre de champs restants après exclusion.
     */
    public function countData(array $exclude = []): int
    {
        $count = count($this->data);
        foreach ($exclude as $field) {
            if (array_key_exists($field, $this->data)) {
                $count--;
            }
        }
        
        return $count;
    }
    
    /**
     * Accéder à une clé du corps de requête en toute sécurité.
     *
     * @param string $key Clé du champ recherché.
     *
     * @return mixed Valeur du champ ou null si absent.
     */
    public function data(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }
    
    /**
     * Accéder à une clé de la querystring.
     *
     * @param int|string $key Clé ou index de paramètre.
     *
     * @return mixed Valeur du paramètre ou null si absent.
     */
    public function query(int|string $key): mixed
    {
        return $this->query[$key] ?? null;
    }
    
    /**
     * Accéder à un paramètre de routage.
     *
     * @param string $key Nom du paramètre.
     *
     * @return mixed Valeur ou null si non définie.
     */
    public function param(string $key): mixed
    {
        return $this->params[$key] ?? null;
    }
    
    /**
     * Détecter une requête Ajax (en-tête X-Requested-With).
     *
     * @return boolean true si l'en-tête indique XMLHttpRequest.
     */
    public function isAjax(): bool
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        }
        return false;
    }
    
    
    /**
     * Détecter une requête HTTP GET ou HEAD.
     *
     * @return boolean true si la méthode est GET ou HEAD.
     */
    public function isGet(): bool
    {
        $get  = isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "GET";
        $head = isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "HEAD";
        
        return $get || $head;
    }
    
    /**
     * Détecter une requête HTTP POST.
     *
     * @return boolean true si la méthode est POST.
     */
    public function isPost(): bool
    {
        $isPOST = isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "POST";
        
        if (!$isPOST) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Détecter une requête HTTP PUT.
     */
    public function isPut(): bool
    {
        return isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "PUT";
    }
    
    /**
     * Détecter une requête HTTP DELETE.
     */
    public function isDelete(): bool
    {
        return isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "DELETE";
    }
    
    /**
     * Détecter une requête HTTP PATCH.
     */
    public function isPatch(): bool
    {
        return isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "PATCH";
    }
    
    /**
     * Détecter une requête HTTP OPTIONS.
     */
    public function isOptions(): bool
    {
        return isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "OPTIONS";
    }
    
    
    /**
     * Détecter une connexion sécurisée (HTTPS activé).
     *
     * @return boolean true si HTTPS est actif et non désactivé.
     *
     */
    public function isSSL(): bool
    {
        return isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== "off";
    }
    
    /**
     * Ajouter des paramètres supplémentaires à $this->params.
     *
     * @param array<string, mixed> $params Paires clé/valeur à fusionner.
     *
     * @return Request Instance courante (fluide).
     */
    public function addParams(array $params): Request
    {
        $this->params = array_merge($this->params, $params);
        
        return $this;
    }
    
    /**
     * Obtenir la longueur de contenu annoncée par le client.
     *
     * @return integer Taille en octets (0 si non renseignée).
     */
    public function contentLength(): int
    {
        return (int)$_SERVER['CONTENT_LENGTH'];
    }
    
    /**
     * Vérifier une éventuelle troncature des données POST/FILES.
     * Cette situation se produit lorsque les superglobales sont vides malgré un Content-Length.
     *
     * @return bool true si un débordement est suspecté.
     */
    public function dataSizeOverflow(): bool
    {
        $contentLength = $this->contentLength();
        
        return empty($this->data) && isset($contentLength);
    }
    
    /**
     * Obtenir l'URI courante (chemin + query brute).
     *
     * @return string|null URI transmise par le serveur.
     */
    public function uri(): ?string
    {
        return $_SERVER['REQUEST_URI'] ?? null;
    }
    
    /**
     * Obtenir l'hôte HTTP actuel.
     *
     * @return string|null Nom d'hôte envoyé par le client.
     */
    public function host(): ?string
    {
        return $_SERVER['HTTP_HOST'] ?? null;
    }
    
    /**
     * Obtenir le nom du serveur (SERVER_NAME).
     *
     * @return string|null Nom de serveur configuré.
     */
    public function name(): ?string
    {
        return $_SERVER['SERVER_NAME'] ?? null;
    }
    
    /**
     * Obtenir le référent HTTP de la requête.
     *
     * @return string|null URL référente ou null si absente.
     */
    public function referer(): ?string
    {
        return $_SERVER['HTTP_REFERER'] ?? null;
    }
    
    /**
     * Obtenir l'adresse IP cliente la plus fiable.
     *
     * 'REMOTE_ADDR' est privilégié ; les autres en-têtes ne sont pas utilisés ici.
     *
     * @return string|null Adresse IP ou null si inconnue.
     */
    public function clientIp(): ?string
    {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }
    
    /**
     * Obtenir l'agent utilisateur communiqué par le client.
     *
     * @return string Valeur brute de l'en-tête User-Agent.
     */
    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? "";
    }
    
    /**
     * Construire l'URL courante sans le protocole.
     *
     * Les paramètres `url` et `redirect` sont exclus pour éviter les doublons
     * lors de redirections internes.
     *
     * @return string Hostname + chemin + query string filtrée.
     */
    public function currentUrl(): string
    {
        
        // 1. get uri
        $uri = $this->uri();
        if (str_contains($uri, '?')) {
            [$uri] = explode('?', $uri, 2);
        }
        
        // 2. add querystring arguments(neglect 'url' & 'redirect')
        $query    = "";
        $queryArr = $this->query;
        unset($queryArr['url'], $queryArr['redirect']);
        
        if (!empty($queryArr)) {
            $query .= '?' . http_build_query($queryArr, "", '&');
        }
        
        return $this->name() . $uri . $query;
    }
    
    /**
     * Valider une URL externe à partir du protocole et de l'hôte.
     *
     * @param string $url URL à vérifier.
     *
     * @return bool true si le protocole est http/https et l'hôte correspond au serveur.
     */
    public function validateUrl(string $url): bool
    {
        
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }
        
        $parts = explode('/', filter_var(trim($url, '/'), FILTER_SANITIZE_URL));
        $parts = array_values(array_filter($parts));
        
        if (count($parts) <= 1) {
            return false;
        }
        
        $protocol = rtrim($parts[0], ":");
        $host     = $parts[1];
        
        return
            ($protocol === "http" || $protocol === "https") &&
            ($this->name() !== null && $this->name() === $host);
    }
    
    
    /**
     * Obtenir l'IP distante capturée à la construction.
     *
     * @return string|null Adresse IP initiale ou null.
     */
    public function getIP()
    {
        return $this->remoteAddress;
    }
} 
