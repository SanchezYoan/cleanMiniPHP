<?php

/**
 * Diriger la requête entrante vers le contrôleur approprié.
 *
 * La classe instancie la Request/Response, résout le contrôleur et déclenche
 * l'action ciblée en tenant compte du sous-domaine et de l'URL.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class App
{
    /**
     * Identifiant technique interne du cycle de requête.
     */
    private ?string $guid = null;
    
    /**
     * Nom de contrôleur ciblé ou instance résolue.
     *
     * @var Controller|string|null
     */
    private string|null|Controller $controller = null;
    
    /**
     * Nom de la méthode d'action à invoquer.
     */
    private ?string $method = null;
    
    /**
     * Arguments extraits de l'URL à transmettre à l'action.
     *
     * @var list<mixed>
     */
    private array $args = [];
    
    /**
     * Requête HTTP encapsulée.
     */
    private ?Request $request;
    
    /**
     * Réponse HTTP en cours de construction.
     */
    private ?Response $response;
    
    /**
     * Construire l'application et initialiser Request/Response.
     */
    public function __construct()
    {
        
        // initialize request and respond objects
        $this->request  = new Request();
        $this->response = new Response();
        
    }
    
    /**
     * Exécuter la résolution du contrôleur et de l'action cible.
     *
     * Définit le contrôleur par défaut selon le sous-domaine, parse l'URL puis
     * invoque l'action. La réponse HTTP est renvoyée ou directement envoyée.
     *
     * @throws Exception Si l'instanciation du contrôleur échoue.
     *
     * @return Response|string Corps de réponse ou instance Response envoyée.
     */
    public function run(): Response|string
    {

        // $date = new DateTime( "NOW" );
        // Logger::debug("APP run : " . $date->format( "H:i:s.u" ));
        $this->controller = "BoController";
        $this->method     = "index";

        /* Mis en commentaire le 10/10/2024, car fait perdre 1 seconde a chaque appel
        if ($this->ipBanned()) {
            return print_r("IP BANNED", false);
        }
        */

        $this->splitUrl();
        // finally instantiate the controller object, and call it's action method.
        return $this->invoke($this->controller, $this->method, $this->args);
        
    }
    
    /**
     * Bloquer une IP référencée comme interdite.
     *
     * Lit/alimente le fichier banned_ip.json et journalise l'IP en base si elle
     * cible des chemins interdits. Écrit potentiellement sur disque et en base.
     *
     * @return bool true si l'accès doit être refusé.
     */
    private function ipBanned(): bool
    {
        $ip           = $_SERVER["REMOTE_ADDR"] ?? null;
        $bannedIpFile = BASE_DIR . "/banned_ip.json";
        
        if (!empty($ip)) {
            
            if (file_exists($bannedIpFile)) {
                $bannedIps = Utility::readJsonFile($bannedIpFile);
                foreach ($bannedIps as $bannedIp) {
                    if ($bannedIp["ip"] === $ip) {
                        // refuse access
                        return true;
                    }
                }
            } else {
                // create file if it doesn't exist
                Utility::writeJsonFile($bannedIpFile, []);
            }
            
            $db = Database::openConnection();
            $db->prepare("SELECT ip FROM logs WHERE ip = :ip
                                    AND (request LIKE '%wp-includes%' OR request LIKE '%xmlrpc%' OR request LIKE '%wp-content%' OR request LIKE '%wp-login%')")
               ->bindValue(":ip", $ip)
               ->execute();
            if ($db->countRows() > 0) {
                // insert $ip into banned_ip.json
                Utility::addDataToJsonFile($bannedIpFile, ["date" => date("Y-m-d H:i:s"), "ip" => $ip]);
            }
        }
        // allow access
        return false;
    }
    
    /**
     * Découper l'URL courante et déduire contrôleur, action et arguments.
     *
     * Détermine le contrôleur en fonction du sous-domaine (BO/APP/EDITOR) et
     * renseigne API_VERSION pour l'éditeur. Les segments restants alimentent
     * la liste d'arguments.
     *
     * @return void
     */
    public function splitUrl(): void
    {
        $URL_PATH = $_SERVER["REQUEST_URI"];
        if (!empty($URL_PATH)) {
            $url = explode("?", $URL_PATH)[0];
            $url = explode('/', filter_var(trim($url, '/'), FILTER_SANITIZE_URL));
            if (count($url) === 1 && $url[0] === "") {
                $url = [];
            }
            if (SUBDOMAIN === "BO") {
                if (isset($url[0]) && $url[0] === "test") {
                    $this->controller = "TestController";
                    $this->method     = $url[1] ?? "index";
                    unset($url[0], $url[1]);
                } else {
                    $this->controller = "BoController";
                }
            } else if (SUBDOMAIN === "APP" || SUBDOMAIN === "EDITOR") {
                $this->controller = (SUBDOMAIN === "EDITOR") ? "EditorController" : "AppController";
                // https://editor.nouveauprojet.fr/v1/…/…
                // test if $url[0] respect pattern letter 'v' + integers
                if (!isset($url[0]) || !preg_match('/^v[0-9]+$/i', $url[0])) {
                    return;
                }
                define("API_VERSION", $url[0]);
                unset($url[0]);
            }
            
            $this->args = !empty($url) ? array_values($url) : [];
        }
    }
    
    
    /**
     * Instancier le contrôleur et déclencher son action.
     *
     * @param string $controller Nom de classe du contrôleur à instancier.
     * @param string $method     Nom de la méthode appelée (index par défaut).
     * @param array  $args       Arguments positionnels issus de l'URL.
     *
     * @return string|Response Contenu ou objet Response déjà configuré.
     * @throws Exception Si l'instanciation du contrôleur échoue.
     */
    private function invoke(string $controller, string $method = "index", array $args = []): Response|string
    {
        $this->request->addParams(['controller' => $controller, 'action' => $method, 'args' => $args]);
        $this->controller = new $controller($this->request, $this->response);
        
        
        $result = $this->controller->startupProcess();
        if ($result instanceof Response) {
            return $result->send();
        }
        
        if (!empty($args)) {
            if (is_callable([$this->controller, $method])) {
                $response = call_user_func_array([$this->controller, $method], $args);
            } else {
                $this->response->error(404);
                return $this->response->send();
            }
        } else if (is_callable([$this->controller, $method])) {
            $response = $this->controller->{$method}();
        } else {
            $this->response->error(404);
            return $this->response->send();
        }
        if ($response instanceof Response) {
            return $response->send();
        }
        return $this->response->send();
    }
    
    
    /**
     * Vérifier la validité syntaxique d'une méthode d'action.
     *
     * L'appel direct d'une méthode `index` est refusé car géré par le
     * constructeur du contrôleur.
     *
     * @param string $controller Nom de classe du contrôleur.
     * @param string $method     Nom de méthode à tester.
     *
     * @return boolean true si la méthode est autorisée et existante.
     */
    private function isMethodValid($controller, $method): bool
    {
        
        if (!empty($method)) {
            if (!preg_match('/\A[a-zA-Z0-9_]+\z/i', $method)) {
                return false;
            }
            if (!method_exists($controller, $method)) {
                return false;
            }
            if (strtolower($method) === "index") {
                return false;
            }
            
        }
        
        return true;
        
    }
    
    /**
     * Contrôler le nombre et le format des arguments fournis.
     *
     * @param string $controller Nom de classe du contrôleur visé.
     * @param string $method     Méthode ciblée.
     * @param array  $args       Arguments positionnels à valider.
     *
     * @return boolean true si le compte et le format sont conformes.
     * @see http://stackoverflow.com/questions/346777/how-to-dynamically-check-number-of-arguments-of-a-function-in-php?lq=1
     */
    private function areArgsValid($controller, $method, $args): bool
    {
        try {
            $reflection                   = new ReflectionMethod($controller, $method);
            $_min                         = $reflection->getNumberOfRequiredParameters();
            $_max                         = $reflection->getNumberOfParameters();
            $hasTheRightNumberOfArguments = (count($args) >= $_min && count($args) <= $_max);
            if (!$hasTheRightNumberOfArguments) {
                return false;
            }
            foreach ($args as $arg) {
                if (is_array($arg)) {
                    foreach ($arg as $a) {
                        if (!preg_match('/\A[a-z0-9%@$\-_.=]+\z/i', $a)) {
                            return false;
                        }
                    }
                } else {
                    if (!preg_match('/\A[a-z0-9%@$\-_.=]+\z/i', $arg)) {
                        return false;
                    }
                }
            }
        } catch (ReflectionException $ex) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * Renvoyer une réponse 404 minimale.
     *
     * @return Response Réponse HTTP 404 prête à être envoyée.
     */
    private function notFound(): Response
    {
        $response = new Response("Not Found", "404");
        return $response->send();
        
    }
    
}
