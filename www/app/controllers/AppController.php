<?php

/**
 * Orchestrer les points d'entrée de l'API mobile.
 *
 * Réalise les contrôles de sécurité (HTTPS, headers obligatoires), valide les
 * headers envoyés par l'app puis route vers les actions correspondantes.
 */
class AppController extends Controller
{
    /**
     * Routes reconnues par l'API (url → méthode).
     *
     * @var array<string, string>
     */
    private const ROUTES = [
        # register
        "init"                    => "init", // OK
        // EXEMPLE
        //"register/notification"   => "registerNotification", // OK
        

    ];
    
    
    /**
     * Actions de filtrage et sécurité avant les méthodes métier.
     *
     * Vérifie HTTPS, méthode autorisée, en-têtes requis et existence du device
     * (sauf init). Initialise la langue et hydrate user/device en session.
     *
     * @throws Exception En cas d'échec d'instanciation de dépendances.
     *
     * @return Response|null Réponse d'erreur immédiate ou null pour poursuivre.
     */
    public function beforeAction(): ?Response
    {
        parent::beforeAction();
        Session::destroy();
        
        if (!$this->request->isSSL()) {
            return $this->response->json(Controller::ERR_INVALID_REQUEST, ["message" => "Requests over https only"]);
        }
        if ($this->request->isOptions()) {
            // Test CORS a laissé passer, response 200
            return $this->response->json(Controller::SUCCESS, ["message" => "CORS OK"]);
        }
        if (!$this->request->isPost() && !str_contains($this->request->url, "/pairing/qrcode/show/")) {
            return $this->response->json(Controller::ERR_INVALID_REQUEST, ["message" => "Invalid request. Must be a POST request"]);
        }
        // Check if API Key is valid
        $token      = $this->request->getHeaders("Device-Token");
        $os         = $this->request->getHeaders("Env");
        $os_version = $this->request->getHeaders("Os-Version");
        $vapp       = $this->request->getHeaders("Vapp");
        $this->lang = $this->request->getHeaders("Lang") ?? "en";
        $this->validator->setLang($this->lang);
        
        if (!$this->validator->validate(
        // pas encore sinon ca bloque les versions actuelles
        // "Os-Version"   => [$os_version, "required|max(10)"],
            [
                "Device-Token" => [$token, "required|max(36)"],
                "Env"          => [$os, "required|max(20)"],
                "Vapp"         => [$vapp, "required|max(10)"],
                
                "Lang" => [$this->lang, "required|max(3)"],
            ])) {
            return $this->response->json(Controller::ERR_BAD_PARAMS, ["message" => "Invalid parameters", "errors" => $this->validator->errors()]);
        }
        
        if (empty($token) || empty($os) || empty($vapp)) {
            Logger::security("APP request without proper headers");
            return $this->response->json(Controller::ERR_INVALID_REQUEST, ["message" => "Missing proper headers"]);
        }
        
        if (!str_ends_with($this->request->url, "/init")) {
            $device = Device::byToken($token);
            if (!$device) {
                Logger::security("Unknown device token ($token) doing APP requests.");
                return $this->response->json(Controller::ERR_UNKNOWN_DEVICE, ["message" => "Unknown Device"]);
            }
            // Device trouvé en base
            $device->setLang($this->lang)
                   ->setOs($os === "a" ? "Android" : "iOS")
                   ->setOsVersion($os_version)
                   ->setAppVersion($vapp)
                   ->save();
            
            $this->device = $device;
            $this->user   = $device->getUser();
            if (!$this->user->exists()) {
                Logger::security("API call from unknown device.");
                return $this->response->json(Controller::ERR_UNKNOWN_USER, ["message" => "Unknown user"]);
            }
            // On met a jour la derniere activité du device, pas de l'utilisateur
            $this->device->updateActivity();

            Session::set("user", $this->user);
            Session::set("device", $this->device);
        }
        
        return null;
    }
    
    /**
     * Router dynamiquement les appels API vers les méthodes du contrôleur.
     *
     * @return Response Réponse JSON correspondant à la route ou une 404.
     * @throws Exception Lorsque l'invocation dynamique échoue.
     */
    public function index(): Response
    {
        Logger::debug("APP call");
        
        // Récupération des composants de l'uri
        $args   = $this->request->param("args") ?? [];
        $steps  = [];
        $params = [];
        
        // test si composant sont des safe strings (minuscules et alphabetiques)
        foreach ($args as $arg) {
            if (preg_match('/^[a-z]+$/', $arg)) {
                $steps[] = $arg;
            } else {
                // Permet de récupérer en param des valeurs de l'url non conformes au format (code QR Code)
                // mais on est pas sensé se servir de l'url pour passer des params car toute l'API est en POST,
                // on en a quand même besoin pour l'appel GET d'affichage du QR Code avant de le scanner, il nous faut savoir quel Qr Code afficher.
                $params[] = $arg;
            }
        }
        
        $str = implode("/", $steps);
        // Si la route existe, on appelle la méthode
        if (isset(self::ROUTES[$str])) {
            $this->request->query = $params;
            return $this->{self::ROUTES[$str]}();
        }
        
        return $this->response->json(Controller::ERR_NOT_FOUND, ["message" => "Not found"]);
        
    }
    

    
}
