<?php
/**
 * Piloter les routes publiques et back-office du site.
 *
 * Assure le routage selon le rôle utilisateur, applique CSRF et CORS, et
 * expose les actions AJAX/HTML du portail d'administration.
 */

use NGine\Html\Html;
use NGine\GeoHelper\GeoHelper;

/**
 * Contrôleur des routes publiques et back-office.
 */
class BoController extends Controller
{
    /**
     * Routes reconnues par le BO.
     *
     * @var array<string, array<string, string>>
     */
    private const ROUTES = [
        "public" => [
            #public
            "home" => "home",
            "contact" => "contact",
            "cgu" => "cgu",
            "mentions-legales" => "mentionsLegales",
            "login" => "login",
            "login/forgot" => "loginForgot",
            "ajax/login" => "ajaxLogin",
            "ajax/forgotPassword" => "ajaxForgotPassword",
            "ajax/resetPassword" => "ajaxResetPassword",
            "ajax/verify2fa" => "ajaxVerify2fa",
            "ajax/createAccount" => "ajaxCreateAccount",
            "download" => "download",
        ],
        "private_USER" => [
            "dashboard"         => "dashboard", # Homepage du dashboard
            "dashboard/account" => "manageAccount", # Modification du compte de l'éditeur
            "dashboard/profile" => "profile", # Interface pour l'édition du profile de l'éditeur
            
            "ajax/account/password/update" => "ajaxUpdatePassword", # AJAX pour mettre à jour le mot de passe d'un compte
            "logout"                       => "logout",
        ],
        "private_USERSU" => [],
        "private_ADMIN" => [
            "dashboard/accounts" => "manageAccounts", # Gestion des comptes de connexions pour les éditeurs
            
            "ajax/account/create" => "ajaxCreateAccount", # AJAX pour créer un compte utilisateur pour un éditeur
            "ajax/account/delete" => "ajaxDeleteAccount", # AJAX pour supprimer un compte utilisateur pour un éditeur
            "ajax/account/update" => "ajaxUpdateAccount", # AJAX pour modifier un compte utilisateur pour un éditeur
            "ajax/geolocate" => "geolocate", # AJAX pour la localisation
        ],
        "private_ADMINSU" => [
            "dashboard/blockedaccounts" => "blockedAccounts", # Homepage du dashboard
            "dashboard/logs" => "manageLogs",
            //"dashboard/monitoring" => "monitoring",
            "ajax/account/unlock" => "ajaxUnlockAccount", # AJAX pour deverouiller un compte
        ],
        
    ];
    
    /**
     * Correspondance des groupes de routes et rôles requis.
     *
     * @var array<string, string|null>
     */
    private const ROUTE_ROLES = [
        "public"          => null,           // pas de rôle requis
        "private_USER"    => User::USER,
        "private_USERSU"  => User::USERSU,
        "private_ADMIN"   => User::ADMIN,
        "private_ADMINSU" => User::ADMINSU,
    ];
    
    /**
     * Appliquer les filtres de sécurité avant les actions.
     *
     * Gère les prévols CORS, valide le CSRF sur POST et prépare les assets JS/CSS
     * nécessaires aux vues back-office.
     *
     * @throws Exception
     *
     * @return Response|null Réponse immédiate en cas d'échec CSRF ou CORS.
     */
    public function beforeAction(): ?Response
    {
        $response = parent::beforeAction();
        if ($response !== null) {
            return $response; // important : si le parent veut interrompre, on respecte
        }
        
        if ($this->request->isOptions()) {
            // Test CORS à laisser passer, response 200
            return $this->response->json(Controller::SUCCESS, ["message" => "CORS OK"]);
        }
        if ($this->request->isPost() && !Csrf::validateRequest($this->request)) {
            $message = "CSRF token invalide";
            Csrf::token();
            if ($this->request->isAjax()) {
                return $this->response->json(self::ERROR_FORBIDDEN, ["error" => $message]);
            }
            return $this->error(self::ERROR_FORBIDDEN, $message);
        }

        $this->view->addJS([
            ["url" => "/assets/js/ckeditor5/build/ckeditor.js"],
            ["url" => "/assets/node_modules/choices.js/public/assets/scripts/choices.min.js"],
            ["url" => "/assets/node_modules/flatpickr/dist/flatpickr.js"],
            ["url" => "/assets/node_modules/flatpickr/dist/l10n/fr.js"],
            ["url" => "/assets/node_modules/@tabler/core/dist/libs/list.js/dist/list.min.js"],
            ["url" => "/assets/node_modules/dropzone/dist/dropzone-min.js"],
            ["url" => "/assets/node_modules/apexcharts/dist/apexcharts.min.js"],
            ["url" => "/assets/node_modules/@tabler/core/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"],
        ]);

        $this->view->addCSS([
            "/assets/node_modules/choices.js/public/assets/styles/choices.min.css",
            "/assets/node_modules/flatpickr/dist/flatpickr.css",
            "/assets/node_modules/dropzone/dist/dropzone.css",
        ]);
        
        return null;
    }
    
    /**
     * Récupérer les routes accessibles selon le rôle
     *
     * @param string|null $userRole Rôle de l'utilisateur connecté.
     *
     * @return array<string, string> Routes accessibles (url => méthode).
     *
     * @throws Exception
     */
    private function getAccessibleRoutes(?string $userRole): array
    {
        $accessible = [];
        
        foreach (self::ROUTE_ROLES as $group => $requiredRole) {
            
            // Public : toujours accessible
            if ($requiredRole === null) {
                $accessible = array_merge($accessible, self::ROUTES[$group]);
                continue;
            }
            
            // Pas connecté → pas de routes privées
            if (!$userRole) continue;
            
            // Vérification du rôle (modèle User)
            if (User::checkAccess($userRole, $requiredRole)) {
                $accessible = array_merge($accessible, self::ROUTES[$group]);
            }
        }
        
        return $accessible;
    }

    /**
     * Router les requêtes entrantes vers l'action correspondante.
     *
     * Résout la route la plus longue compatible avec le rôle courant, applique
     * les contrôles d'authentification et redirige vers l'action cible avec les
     * paramètres restants.
     *
     * @throws Exception
     */
    public function index(): Response|string
    {
        
        // Récupérer l'URI complète depuis l'objet Request
        $uri = $this->request->uri();
        $urlData = explode("?", $uri);
        $path = ltrim($urlData[0], '/');
        // Si le chemin est vide, afficher la page d'accueil
        if (empty($path)) {
            return $this->home();
        }

        // Séparer le chemin en composants
        $components = explode('/', $path);
        $params = [];
        $route = null;
        /* remove item from array if value = "v1" */
        foreach ($components as $key => $value) {
            if ($value === "v1") {
                unset($components[$key]);
            }
        }
        
        // Rôle actuel
        $userRole = Session::isLoggedIn() ? $this->admin->getLevel() : null;
        
        // Routes accessibles pour ce rôle
        $routes = $this->getAccessibleRoutes($userRole);
        
        // Trouver la route la plus longue correspondante
        for ($i = count($components); $i > 0; $i--) {
            $potentialRoute = implode('/', array_slice($components, 0, $i));
            
            if (isset(self::ROUTES["public"][$potentialRoute])) {
                // Les paramètres sont tous les composants après la route trouvée
                $params = array_slice($components, $i);
                $route = self::ROUTES["public"][$potentialRoute];
                break;
            }
            
            if (isset($routes[$potentialRoute])) {
                // Si on est pas connecté, on ne peut accéder à une route privée
                if (!Session::isLoggedIn()) {
                    // Suivant le type de request on adapte l'erreur
                    // Pour une requête ajax, on retourne une erreur response
                    if ($this->request->isAjax()) {
                        return $this->response->json(self::ERR_UNAUTHORIZED, ["error" => \NGine\Translate::get("nouveauprojet.error.unauthorize")]);
                    }
                    // Pour une requête normale, on retourne une erreur HTTP
                    return $this->error(self::ERR_UNAUTHORIZED, \NGine\Translate::get("nouveauprojet.error.unauthorize"));
                }
                // Si l'url demandé correspond à /api/app il faut être admin pour accéder à cette route
                if (str_contains($this->request->url, "/api/app") && !Session::isAdmin()) {
                    $this->response->setType("text/html");
                    return $this->error(404, "Page not found !");
                }
                // Les paramètres sont tous les composants après la route trouvée
                $params = array_slice($components, $i);
                $route = $routes[$potentialRoute];
                break;
            }
        }

        // Si aucune route n'a été trouvée, retourner une erreur
        if (!$route) {
            if ($this->request->isAjax()) {
                return $this->response->json(self::ERR_NOT_FOUND, ["message" => "Not found"]);
            }

            return $this->error(self::ERR_NOT_FOUND, "Page not found");
        }

        // Définir les paramètres de la requête
        if (!empty($params)) {
            $this->request->query = $params;
        }

        // Appeler la méthode correspondante avec les paramètres
        return $this->{$route}();
    }
    

    /**
     * Afficher la page d'accueil publique.
     *
     * Effets de bord : ajoute des variables de configuration JS.
     *
     * @return string HTML rendu.
     */
    private function home(): string
    {
        Config::addJsConfig('curPage', "accueil");
        return $this->view->renderWithLayouts(
            VIEWS . "/layouts/frontend",
            VIEWS . "/public/home.php",
        );
    }
    
    /**
     * Traiter la demande de connexion via AJAX.
     *
     * Effets de bord : écrit en session, crée/renouvelle le flux 2FA et journalise.
     *
     * @return Response Réponse JSON.
     */
    public function ajaxLogin(): Response
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $login    = $this->request->data("login");
            $password = $this->request->data("password");
            
            if (!$this->validator->validate(
                [
                    "Login"        => [$login, "required|max(150)"],
                    "Mot de passe" => [$password, "required|max(20)"],
                ]
            )) {
                return $this->response->json(
                    Controller::SUCCESS,
                    [
                        "success" => false,
                        "error"   => $this->validator->errors(),
                    ]
                );
            }
            
            $auth = User::login($login, $password);
            
            if ($auth->error) {
                TwoFactorLog::log(
                    null,
                    false,
                    "identifiant ou mot de passe incorrect",
                    null,
                    "LOGIN"
                );
                return $this->response->json(
                    Controller::SUCCESS,
                    [
                        "success" => false,
                        "error"   => \NGine\Translate::get("nouveauprojet.error.login.unknown"),
                    ]
                );
            }
            
            $user = $auth->admin;
            
            /** @var User $user */
            // si le délai est expiré, on reset le blocage
            $user->resetTwoFactorLockIfExpired(Config::get('GOOGLE_AUTHENTIFICATOR.LOCKED_IN_MINUTES'));
            
            // Si l'utilisateur est bloqué 2FA, on le refuse avant même de lancer le flow
            if ($user->isTwoFactorTemporarilyLocked(Config::get('GOOGLE_AUTHENTIFICATOR.LOCKED_IN_MINUTES'))) {
                $remaining = $user->getTwoFactorLockRemainingMinutes(Config::get('GOOGLE_AUTHENTIFICATOR.LOCKED_IN_MINUTES'));
                
                TwoFactorLog::log(
                    $user,
                    false,
                    "Tentative de connexion pendant blocage 2FA (reste {$remaining} minute(s))",
                    $user->getFailed2faAttempts(),
                    'LOGIN'
                );
                
                return $this->response->json(
                    Controller::SUCCESS,
                    [
                        "success" => false,
                        "error"   => "Votre compte est temporairement bloqué après plusieurs codes 2FA incorrects. Réessayez dans environ {$remaining} minute(s)."
                    ]
                );
            }
            
            $google2faManager = new Google2FaManager();
            
            // 1) Si la 2FA n'est pas requise pour ce type d'utilisateur : login direct
            if (!$google2faManager->is2faEnabledForUser($user)) {
                $this->finalizeLogin($user); //TODO: Voir pour gerer selon le type d'utilisateur (Admin etait pour le miniPHP de base)
                
                TwoFactorLog::log(
                    $user,
                    true,
                    "Connexion sans 2FA (2FA non activée pour ce compte)",
                    null,
                    'LOGIN'
                );
                
                return $this->response->json(
                    Controller::SUCCESS,
                    [
                        "success"      => true,
                        "requires_2fa" => false,
                        "message"      => \NGine\Translate::get("nouveauprojet.success.connexion"),
                        "redirect"     => "/dashboard",
                    ]
                );
            }
            
            // 2) 2FA requise : on prépare le flow 2FA
            $hadSecret = !empty($user->getGoogle2faSecret()); // si l'utilisateur a deja un code
            
            // Crée / récupère le secret 2FA
            $secret = $google2faManager->getOrCreateSecretForUser($user);
            
            // Génère l'URL otpauth:// et le QR inline
            $companyName = Config::get("SITE.NAME") ?? "Mon site";
            $otpAuthUrl  = $google2faManager->getOtpAuthUrlForUser($companyName, $user->getEmail(), $secret);
            
            // On ne renvoie le QR que si l'utilisateur n'avait pas encore de secret (premier setup)
            $inlineQrCode = $hadSecret ? null : $google2faManager->generateInlineQrCode($otpAuthUrl);
            
            // Génère un token 2FA temporaire et le mappe à l'utilisateur en session
            $twofaToken = bin2hex(random_bytes(16));
            if (!isset($_SESSION['2fa_pending'])) {
                $_SESSION['2fa_pending'] = [];
            }
            
            // Purge tokens expirés
            foreach ($_SESSION['2fa_pending'] as $t => $data) {
                if (($data['expiresAt'] ?? 0) < time()) {
                    unset($_SESSION['2fa_pending'][$t]);
                }
            }
            
            $expiresAt = time() + Config::get("GOOGLE_AUTHENTIFICATOR.TWOFA_PENDING_TTL");
            $_SESSION['2fa_pending'][$twofaToken] = [
                'userId'    => $user->getId(),
                'expiresAt' => $expiresAt,
            ];
            
            TwoFactorLog::log(
                $user,
                false, // la connexion n’est pas encore finalisée
                "Authentification primaire réussie, 2FA requise",
                $user->getFailed2faAttempts(),
                'LOGIN'
            );
            
            // On NE met PAS encore Session::set("admin", $user) ici : ce sera fait après vérif 2FA
            return $this->response->json(
                Controller::SUCCESS,
                [
                    "success"      => true,
                    "requires_2fa" => true,
                    "twofaToken"   => $twofaToken,
                    "qr_code"      => $inlineQrCode,
                ]
            );
        }
        
        return $this->response->json(
            self::ERR_UNAUTHORIZED,
            [
                "success" => false,
                "error"   => \NGine\Translate::get("nouveauprojet.error.invalid_request"),
            ]
        );
    }

    /**
     * Afficher l'écran de connexion.
     *
     * @return string HTML rendu.
     */
    public function login(): string
    {
        Config::addJsConfig('curPage', "login");
        return $this->view->renderWithLayouts(
            VIEWS . "/layouts/frontend",
            VIEWS . "/public/login.php",
        );
    }

    /**
     * Déconnecter l'utilisateur.
     *
     * Effets de bord : purge la session et les cookies.
     *
     * @return Response Redirection.
     */
    public function logout(): Response
    {
        Session::delete("user");
        Session::delete("admin");
        return Redirector::home();
    }

    /**
     * Géolocaliser une adresse via le helper GeoHelper.
     *
     * @return Response Réponse JSON de géolocalisation.
     */
    public function geolocate(): Response
    {
        if ($this->request->isPost()) {
            $adress1 = $this->request->data("adresse1");
            $adress2 = $this->request->data("adresse2");
            $codePostal = $this->request->data("code_postal");
            $ville = $this->request->data("ville");
            $pays = $this->request->data("pays");

            $address = implode(" ", [
                $adress1 . " " . $adress2,
                $codePostal,
                $ville,
                $pays,
            ]);

            $geoHelper = new \NGine\GeoHelper\GeoHelper();
            $apiKey = Config::get("API.GOOGLE.MAPS.API_KEY");
            $coords = $geoHelper->getCoordinates($address, $apiKey);
            if (!isset($coords->latitude)) {
                return $this->response->json(Controller::ERR_INVALID_REQUEST, ["Adresse [$address] inconnue sur Google Maps."]);
            }
            return $this->response->json(Controller::SUCCESS, ["lat" => ($coords->latitude ?? null), "lgt" => ($coords->longitude ?? null)]);
        }

        return $this->response->json(Controller::ERR_INVALID_REQUEST, ["message" => \NGine\Translate::get("nouveauprojet.error.invalid_request")]);
    }

    /**
     * Afficher le tableau de bord.
     *
     * @return string|Response Vue rendue ou redirection.
     */
    private function dashboard(): string|Response
    {
        if (Session::isLoggedIn()) {
                Config::addJsConfig('curPage', "dashboardAdmin");
                return $this->view->renderWithLayouts(
                    VIEWS . "/layouts/backend",
                    VIEWS . "/admin/dashboard.php",
                    [
                        "noindex" => true,
                    ]
                );

        }
        return Redirector::home();
    }

/*    private function profile()
    {
        $countries = new Country();
        $editor = new Editor($this->admin->getEditorId());
        if ($this->admin->getLevel() === User::ADMINSU) {
            Config::addJsConfig('curPage', "profileAdmin");

            return $this->view->renderWithLayouts(
                VIEWS . "/layouts/backend",
                VIEWS . "/admin/profile.php",
                [
                    "noindex" => true,
                    "editor" => $editor,
                    "countries" => $countries->getCountries(),
                ]
            );
        }

        Config::addJsConfig('curPage', "profileEditor");
        return $this->view->renderWithLayouts(
            VIEWS . "/layouts/backend",
            VIEWS . "/editors/profile.php",
            [
                "noindex" => true,
                "editor" => $editor,
                "countries" => $countries->getCountries(),
            ]
        );
    }*/

    /**
     * Afficher les conditions générales d'utilisation.
     *
     * @return string HTML rendu.
     */
    private function cgu(): string
    {
        Config::addJsConfig('curPage', "cgu");
        return $this->view->renderWithLayouts(
            VIEWS . "/layouts/frontend",
            VIEWS . "/public/cgu.php"
        );
    }

    /**
     * Afficher la page des mentions légales.
     *
     * @return string HTML rendu.
     */
    private function mentionsLegales(): string
    {
        Config::addJsConfig('curPage', "legales");
        return $this->view->renderWithLayouts(
            VIEWS . "/layouts/frontend",
            VIEWS . "/public/mentions-legales.php"
        );
    }

    /**
     * Afficher la page de contact.
     *
     * @return string HTML rendu.
     */
    private function contact(): string
    {
        Config::addJsConfig('curPage', "contact");
        return $this->view->renderWithLayouts(
            VIEWS . "/layouts/frontend",
            VIEWS . "/public/contact.php"
        );
    }

    /**
     * Afficher l'écran de gestion des comptes.
     *
     * @return string|Response Vue rendue ou redirection.
     */
    private function manageAccounts(): string|Response
    {
        // En admin, nous avons des stats globales sur l'app
        if ($this->admin->getLevel() === User::ADMINSU) {
            // On compte le nombre de comptes éditeur
            $accounts = User::getAll();
            // Ajouter le js
            Config::addJsConfig("curPage", "manageAccounts");
            // On render
            return $this->view->renderWithLayouts(
                VIEWS . "/layouts/backend",
                VIEWS . "/admin/accounts-manage.php",
                [
                    "accounts" => $accounts,
                ]
            );
        }
        // Erreur si pas admin
        return $this->error(Controller::ERR_UNAUTHORIZED, "Vous devez être administrateur.");
    }
    
    /**
     * Afficher la liste des comptes bloqués.
     *
     * @return string|Response Vue rendue ou redirection.
     */
    private function blockedAccounts(): string|Response
    {
        // En admin, nous avons des stats globales sur l'app
        if ($this->admin->getLevel() === User::ADMINSU) {
            // On compte le nombre de comptes éditeur
            $accounts = User::getTwoFactorBlockedUsers();
            // Ajouter le js
            Config::addJsConfig("curPage", "manageBlockedAccounts");
            // On render
            return $this->view->renderWithLayouts(
                VIEWS . "/layouts/backend",
                VIEWS . "/admin/blocked-accounts.php",
                [
                    "accounts" => $accounts,
                ]
            );
        }
        // Erreur si pas admin
        return $this->error(Controller::ERR_UNAUTHORIZED, "Vous devez être administrateur.");
    }

    /**
     * Créer un compte utilisateur via AJAX.
     *
     * Effets de bord : écrit en base.
     *
     * @return Response Réponse JSON.
     */
    private function ajaxCreateAccount(): Response
    {
        // Si on est en post
        if ($this->request->isPost()) {
            // Récupère les données envoyées
            $role = $this->request->data("role") ?? User::USER;
            $login = $this->request->data("login");
            $email = $this->request->data("email");
            $password = $this->request->data("password");
            $trad_role = \NGine\Translate::get("nouveauprojet.dashboard.profile.form.role");
            $trad_login = \NGine\Translate::get("nouveauprojet.dashboard.profile.form.login");
            $trad_password = \NGine\Translate::get("nouveauprojet.dashboard.profile.form.password");
            $trad_email_login = \NGine\Translate::get("nouveauprojet.dashboard.profile.form.email_login");
            // Vérifie que les données sont bien au format attendu
            if (!empty($login) && !empty($email) && !empty($password)) {
                // On passe les paramètres dans notre validator
                if (!$this->validator->validate(
                    [
                        $trad_role => [$role, "required|min(4)|max(7)"],
                        $trad_login => [$login, "required|min(3)|max(50)|unique(users,login)"],
                        $trad_password => [$password, "required|min(3)|max(20)|validatePassword"],
                        $trad_email_login => [$email, "required|min(3)|max(200)|email|unique(users,email)"],
                    ]
                )) {
                    return $this->response->json(Controller::SUCCESS, ["error" => $this->validator->errors()]);
                }
                // On hash le mot de passe
                $hashed_password = Encryption::passwordHash2($password);
                // on crée le compte
                $account = new User();
                $account->setLogin($login)
                    ->setEmail($email)
                    ->setPassword($hashed_password)
                    ->setLevel($role)
                    ->save();
                // Retourne un objet bien structuré avec les informations nécessaires
                $accountData = [
                    "id" => $account->getId(),
                    "login" => $account->getLogin(),
                    "role" => $account->getLevel(),
                    "email" => $account->getEmail(),
                    "createdAt" => $account->getCreatedAt()->format("d/m/Y"), // Formater la date pour l'affichage
                ];
                return $this->response->json(Controller::SUCCESS, ["success" => true, "account" => $accountData]);
            }
            return $this->response->json(Controller::ERR_BAD_PARAMS, ["error" => "Bad params"]);
        }
        return $this->response->json(Controller::ERR_INVALID_REQUEST, ["error" => "Invalid request"]);
    }

    /**
     * Supprimer un compte utilisateur via AJAX.
     *
     * Effets de bord : suppression en base.
     *
     * @return Response Réponse JSON.
     */
    private function ajaxDeleteAccount(): Response
    {
        if ($this->request->isPost()) {
            // Récupérer l'ID du compte à supprimer
            $account_id = $this->request->data('account_id');
            if (!empty($account_id)) {
                // Trouver le compte à supprimer
                $account = new User($account_id);
                // Supprimer le compte
                $account->delete();
                return $this->response->json(Controller::SUCCESS, ["success" => true]);
            }
            return $this->response->json(Controller::ERR_BAD_PARAMS, ["error" => "Paramètre manquant"]);
        }
        return $this->response->json(Controller::ERR_INVALID_REQUEST, ["error" => "Requête invalide"]);
    }

    /**
     * Mettre à jour un compte utilisateur via AJAX.
     *
     * Effets de bord : mise à jour en base.
     *
     * @return Response Réponse JSON.
     */
    private function ajaxUpdateAccount(): Response
    {
        if ($this->request->isPost()) {
            // Récupérer les params
            $account_id = $this->request->data('account_id');
            $login = $this->request->data("login");
            $role = $this->request->data("role");
            $email = $this->request->data("email");
            $password = $this->request->data("password");
            $trad_login = \NGine\Translate::get("nouveauprojet.dashboard.profile.form.login");
            $trad_password = \NGine\Translate::get("nouveauprojet.dashboard.profile.form.password");
            $trad_email_login = \NGine\Translate::get("nouveauprojet.dashboard.profile.form.email_login");
            if (!empty($account_id) && !empty($login) && !empty($email)) {
                Logger::debug("account_id: {$account_id}, login: {$login}, email: {$email}");
                // On passe les paramètres dans notre validator
                if (!$this->validator->validate(
                    [
                        $trad_login => [$login, "required|min(3)|max(50)|unique(users,login,{$account_id})"],
                        $trad_email_login => [$email, "required|min(3)|max(200)|email|unique(users,email,{$account_id})"],
                    ]
                )) {
                    return $this->response->json(Controller::SUCCESS, ["error" => $this->validator->errors()]);
                }
                // On instancie notre compte
                $account = new User($account_id);
                $account->setLogin($login)
                        ->setEmail($email);
                
                if (!is_null($role)) {
                    $account->setLevel($role);
                }
                if (!empty($password)) {
                    // On passe les paramètres dans notre validator
                    if (!$this->validator->validate(
                        [
                            $trad_password => [$password, "required|min(3)|max(20)|validatePassword"],
                        ]
                    )) {
                        return $this->response->json(Controller::SUCCESS, ["error" => $this->validator->errors()]);
                    }
                    $hashed_password = Encryption::passwordHash2($password);
                    $account->setPassword($hashed_password);
                }
                
                $account->save();
                
                // Retourne un objet bien structuré avec les informations nécessaires
                $accountData = [
                    "id" => $account->getId(),
                    "login" => $account->getLogin(),
                    "role" => $account->getLevel(),
                    "email" => $account->getEmail(),
                    "createdAt" => $account->getCreatedAt()->format("d/m/Y"), // Formater la date pour l'affichage
                ];
                return $this->response->json(Controller::SUCCESS, ["success" => true, "account" => $accountData]);
            }
            return $this->response->json(Controller::ERR_BAD_PARAMS, ["error" => "Paramètre manquant"]);
        }
        return $this->response->json(Controller::ERR_INVALID_REQUEST, ["error" => "Requête invalide"]);
    }


    /**
     * Afficher l'écran de gestion du compte connecté.
     *
     * @return string HTML rendu.
     */
    private function manageAccount(): string
    {
        // Ajouter le js
        Config::addJsConfig("curPage", "manageAccount");
        // On render
        return $this->view->renderWithLayouts(
            VIEWS . "/layouts/backend",
            VIEWS . "/editors/account-manage.php",
            [
                "account" => $this->admin,
            ]
        );
    }
    
    /**
     * Afficher la page des logs.
     *
     * @return string|Response HTML rendu ou redirection.
     * @throws Exception Si la récupération des logs échoue.
     */
    private function manageLogs(): string|Response
    {
        // Liste des niveaux disponibles
        $levels = ['ALL', 'NOTICE', 'DEBUG', 'WARNING', 'ERROR', 'CRITICAL', 'SECURITY'];
        
        // Niveau par défaut ou sélectionné via le formulaire
        $level = isset($_GET['level']) && in_array($_GET['level'], $levels) ? $_GET['level'] : 'ALL';
        
        // On compte le nombre de logs
        $datas     = Logger::loadBDD(20, $level);
        $countLogs = Logger::countByTypeToday();
        
        // Ajouter le js
        Config::addJsConfig("curPage", "manageLogs");
        // On render
        return $this->view->renderWithLayouts(
            VIEWS . "/layouts/backend",
            VIEWS . "/admin/logs-manage.php",
            [
                "datas"      => $datas,
                "level"      => $level,
                "countToday" => $countLogs
            ]
        );
    }

    /**
     * Mettre à jour le mot de passe via AJAX.
     *
     * Effets de bord : mise à jour en base.
     *
     * @return Response Réponse JSON.
     */
    private function ajaxUpdatePassword(): Response
    {
        if ($this->request->isPost()) {
            // Récupérer les params
            $account_id = $this->request->data('account_id');
            $old_password = $this->request->data("old_password");
            $new_password = $this->request->data("new_password");
            if (!empty($account_id) && !empty($old_password) && !empty($new_password)) {
                // On passe les paramètres dans notre validator
                if (!$this->validator->validate(
                    [
                        "Ancien mot de passe" => [$old_password, "required|min(3)|max(20)|validatePassword"],
                        "Nouveau mot de passe" => [$new_password, "required|min(3)|max(20)|validatePassword"],
                    ]
                )) {
                    return $this->response->json(Controller::SUCCESS, ["error" => $this->validator->errors()]);
                }
                // On instancie notre compte
                $account = new User($account_id);
                // Vérification du mot de passe
                if (Encryption::passwordCheck2($old_password, $account->getPassword())){
                    $hashed_password = Encryption::passwordHash2($new_password);
                    
                    $account->setPassword($hashed_password)
                            ->save();
                    
                    return $this->response->json(Controller::SUCCESS, ["success" => true]);
                }
                return $this->response->json(Controller::SUCCESS, ["error" => "L'ancien mot de passe est incorrect"]);
            }
            return $this->response->json(Controller::ERR_BAD_PARAMS, ["error" => "Paramètre manquant"]);
        }
        return $this->response->json(Controller::ERR_INVALID_REQUEST, ["error" => "Requête invalide"]);
    }

    /**
     * Déclencher l'envoi d'un email de mot de passe oublié via AJAX.
     *
     * Effets de bord : envoi d'email et écriture en base.
     *
     * @return Response Réponse JSON.
     */
    private function ajaxForgotPassword(): Response
    {
        if ($this->request->isPost()) {
            // Récupérer les params
            $email = $this->request->data("email");
            if (!empty($email)) {
                // On passe les paramètres dans notre validator
                if (!$this->validator->validate(
                    [
                        "Email" => [$email, "required|min(3)|email"],
                    ]
                )) {
                    return $this->response->json(Controller::SUCCESS, ["error" => $this->validator->errors()]);
                }
                // On cherche l'utilisateur par son email
                $user = User::getByEmail($email);
                if ($user) {
                    $token = Utility::generateChar('256');
                    $expires = (new DateTime())->add(
                        new DateInterval('PT' . Config::get("SECURITY.RESET_TOKEN.TTL_MINUTES") . 'M')
                    );
                    $user->setResetTokenHash(User::hashResetToken($token))
                        ->setResetExpiresAt($expires)
                        ->save();
                    // On envoie un mail à l'utilisateur avec un lien permettant de modifier son mot de passe
                    $mailManager = new MailManager();
                    //envoyer le mail à l'utilisateur
                    if (!$mailManager->sendForgotPassword($user, $token)) {
                        if ($mailManager->hasErrors()) {
                            return $this->response->json(Controller::ERR_INTERNAL_ERROR, ["error" => $mailManager->errors()]);
                        }
                        return $this->response->json(Controller::ERR_INTERNAL_ERROR, ["error" => "Impossible d'envoyer le mail"]);
                    }
                    return $this->response->json(Controller::SUCCESS, ["success" => true, "message" => "Un email vient d'être envoyé pour vous permettre de modifier votre mot de passe. Merci de consulter votre messagerie."]);
                }
                return $this->response->json(Controller::ERR_NOT_FOUND, ["error" => "Aucun utilisateur ne correspond a cette adresse email"]);
            }
            return $this->response->json(Controller::ERR_BAD_PARAMS, ["error" => "Paramètre manquant"]);
        }
        return $this->response->json(Controller::ERR_INVALID_REQUEST, ["error" => "Requête invalide"]);
    }

    /**
     * Afficher le formulaire de réinitialisation de mot de passe.
     *
     * @return string|Response Vue rendue ou redirection.
     */
    private function loginForgot(): string|Response
    {
        // Récupérer les paramètres de la requête
        $params = $this->request->query;
        $user_id = $params[0];
        $user_reset_token = $params[1];
        // On vérifie que les paramètres existent
        if (!empty($user_id) && !empty($user_reset_token)) {
            // On recupère l'utilisateur via son identifiant
            $user = new User($user_id);
            // Si l'utilisateur n'existe pas
            if (!$user->exists()) {
                return $this->error(404, "Cet utilisateur n'existe pas.");
            }
            // On vérifie que le token est valide
            if (!$user->isResetTokenValid($user_reset_token)) {
                return Redirector::login();
            }
            // Ajouter le js
            Config::addJsConfig("curPage", "resetPassword");
            // On affiche le formulaire de changement de mot de passe
            return $this->view->renderWithLayouts(
                VIEWS . "/layouts/frontend",
                VIEWS . "/public/reset-password.php",
                ['user' => $user, 'token' => $user_reset_token]
            );
        }
        return $this->error(404, "Cet utilisateur n'existe pas.");
    }

    /**
     * Réinitialiser le mot de passe via AJAX.
     *
     * Effets de bord : mise à jour en base.
     *
     * @return Response Réponse JSON.
     */
    private function ajaxResetPassword(): Response
    {
        if ($this->request->isPost()) {
            // Récupérer les params
            $user_id = $this->request->data("user_id");
            $password = $this->request->data("password");
            $resetToken = $this->request->data("reset_token");
            if (!empty($user_id) && !empty($password) && !empty($resetToken)) {
                // On passe les paramètres dans notre validator
                if (!$this->validator->validate(
                    [
                        "ID" => [$user_id, "required|exists(users,id)"],
                        "Mot de passe" => [$password, "required|min(3)|validatePassword"],
                        "Token" => [$resetToken, "required|min(10)"],
                    ]
                )) {
                    return $this->response->json(Controller::SUCCESS, ["error" => $this->validator->errors()]);
                }
                // On cherche l'utilisateur
                $user = new User($user_id);
                if ($user) {
                    if (!$user->isResetTokenValid($resetToken)) {
                        return $this->response->json(Controller::SUCCESS, ["error" => "Le lien de réinitialisation a expiré ou est invalide. Merci de recommencer la procédure."]);
                    }
                    $user->setPassword(password_hash($password, PASSWORD_DEFAULT))
                        ->setResetTokenHash(null)
                        ->setResetExpiresAt(null)
                        ->save();
                    return $this->response->json(Controller::SUCCESS, ["success" => true, "message" => "Mot de passe mis à jour."]);
                }
                return $this->response->json(Controller::ERR_NOT_FOUND, ["error" => "Aucun utilisateur ne correspond a cette adresse email"]);
            }
            return $this->response->json(Controller::ERR_BAD_PARAMS, ["error" => "Paramètre manquant"]);
        }
        return $this->response->json(Controller::ERR_INVALID_REQUEST, ["error" => "Requête invalide"]);
    }
    
    /**
     * Finaliser la connexion après authentification.
     *
     * Effets de bord : écrit en session et met à jour le cookie "remember me".
     *
     * @param User $user Utilisateur authentifié.
     *
     * @return void Aucune valeur de retour.
     */
    protected function finalizeLogin(User $user): void
    {
        Session::regenerate(true);
        Session::set("admin", $user);
    }
    
    /**
     * Vérifier le code 2FA via AJAX.
     *
     * Effets de bord : écrit en session si le code est valide.
     *
     * @return Response Réponse JSON.
     */
    private function ajaxVerify2fa(): Response
    {
        
        if (!$this->request->isPost()) {
            return $this->response->json(Controller::ERR_INVALID_REQUEST, ["error" => "Requête invalide"]);
            
        }
        
        if (!$this->request->isAjax()) {
            return $this->response->json(Controller::ERR_INVALID_REQUEST, ["error" => "Requête invalide"]);
        }

        // Récupérer les params
        $twofaToken  = $this->request->data("twofaToken");
        $otp = $this->request->data("otp");
        
        if (!empty($twofaToken) && !empty($otp)) {
            // On passe les paramètres dans notre validator
            if (!$this->validator->validate(
                [
                    "Token 2Fa" => [$twofaToken, "required"],
                    "otp" => [$otp, "required|min(6)|max(6)"],
                ]
            )) {
                return $this->response->json(Controller::SUCCESS, ["error" => $this->validator->errors()]);
            }
            
            // Vérification de la présence du token en session
            if (empty($_SESSION['2fa_pending'][$twofaToken])) {
                return $this->response->json(
                    Controller::SUCCESS,
                    [
                        "success" => false,
                        "error"   => "Session 2FA invalide ou expirée. Merci de vous reconnecter."
                    ]
                );
            }
            
            // Vérification de l'expiration de la session 2FA
            $pending = $_SESSION['2fa_pending'][$twofaToken] ?? null;
            if (!$pending || time() > ($pending['expiresAt'] ?? 0)) {
                unset($_SESSION['2fa_pending'][$twofaToken]);
                return $this->response->json(
                    Controller::SUCCESS,
                    ["success" => false, "error" => "Session 2FA expirée. Merci de vous reconnecter."]
                );
            }
            
            // Récupération de l'utilisateur associé au token
            $userId = (int)$pending['userId'];
            $user   = new User($userId);
            
            if (!$user->exists()) {
                unset($_SESSION['2fa_pending'][$twofaToken]);
                return $this->response->json(
                    Controller::ERR_NOT_FOUND,
                    ["error" => "Utilisateur introuvable"]
                );
            }
            
            // reset si blocage expiré mais compteur non nettoyé
            $user->resetTwoFactorLockIfExpired(Config::get('GOOGLE_AUTHENTIFICATOR.LOCKED_IN_MINUTES'));
            
            // Vérifier blocage temporel avant de continuer (3 échecs, blocage 60 min)
            if ($user->isTwoFactorTemporarilyLocked(Config::get('GOOGLE_AUTHENTIFICATOR.LOCKED_IN_MINUTES'))) {
                $remaining = $user->getTwoFactorLockRemainingMinutes(Config::get('GOOGLE_AUTHENTIFICATOR.LOCKED_IN_MINUTES'));
                
                TwoFactorLog::log(
                    $user,
                    false,
                    "Tentative de validation 2FA pendant blocage (reste {$remaining} minute(s))",
                    $user->getFailed2faAttempts(),
                    'VERIFY_2FA'
                );
                
                return $this->response->json(
                    Controller::SUCCESS,
                    [
                        "success" => false,
                        "error"   => "Votre compte est temporairement bloqué après plusieurs codes incorrects. Réessayez dans environ {$remaining} minute(s)."
                    ]
                );
            }


            
            // Vérification du code 2FA
            $google2faManager = new Google2FaManager();
            
            // si la 2FA n'est pas requise pour ce type d'utilisateur, on refuse
            if (!$google2faManager->is2faEnabledForUser($user)) {
                
                TwoFactorLog::log(
                    $user,
                    false,
                    "Tentative de validation 2FA alors que la 2FA n'est pas activée pour ce compte",
                    $user->getFailed2faAttempts(),
                    'VERIFY_2FA'
                );
                
                return $this->response->json(
                    Controller::SUCCESS,
                    [
                        "success" => false,
                        "error"   => "Ce compte n'est pas configuré pour la double authentification."
                    ]
                );
            }
            
            $isValid = $google2faManager->verifyCodeForUser($user, $otp);
            
            if (!$isValid) {
                
                // Incrémente le compteur de tentatives échouées
                $attempts = $user->getFailed2faAttempts() + 1;
                $user->setFailed2faAttempts($attempts)
                     ->setLast2faAttempt(new DateTime())
                     ->save();
                
                TwoFactorLog::log(
                    $user,
                    false,
                    "Code 2FA invalide",
                    $attempts,
                    'VERIFY_2FA'
                );
                
                // Si 3 tentatives ou plus -> blocage 1 heure
                if ($attempts >= Config::get("GOOGLE_AUTHENTIFICATOR.ATTEMPTS")) {
                    // On envoi un email
                    Logger::security2FA(" L'utilisateur " . $user->getLogin() . " a été bloqué après plusieurs codes 2FA incorrects.", $user, Config::get('GOOGLE_AUTHENTIFICATOR.LOCKED_IN_MINUTES'));
                    
                    TwoFactorLog::log(
                        $user,
                        false,
                        "Compte bloqué (seuil de tentatives atteint, blocage " . Config::get('GOOGLE_AUTHENTIFICATOR.LOCKED_IN_MINUTES') . " minute(s))",
                        $attempts,
                        'VERIFY_2FA'
                    );
                    
                    return $this->response->json(
                        Controller::SUCCESS,
                        [
                            "success" => false,
                            "error"   => "Vous avez saisi un code incorrect à plusieurs reprises. Votre compte est bloqué pendant 1 heure. Réessayez plus tard ou contactez un administrateur."
                        ]
                    );
                }
                
                $user->save();
                
                
                // Sinon, on indique le nombre de tentatives restantes
                $remaining = Config::get("GOOGLE_AUTHENTIFICATOR.ATTEMPTS") - $attempts;
                
                return $this->response->json(
                    Controller::SUCCESS,
                    [
                        "success" => false,
                        "error"   => "Code invalide. Il vous reste {$remaining} tentative(s) avant blocage.",
                    ]
                );
            }
            
            // Code valide : reset du compteur et finalisation connexion
            $user->setFailed2faAttempts(0)
                 ->setLast2faAttempt(null)
                 ->save();
            
            TwoFactorLog::log(
                $user,
                true,
                "Connexion 2FA réussie",
                0,
                'VERIFY_2FA'
            );
            
            // Code valide : on finalise la connexion
            unset($_SESSION['2fa_pending'][$twofaToken]);
            
            $this->finalizeLogin($user);
            
            return $this->response->json(
                Controller::SUCCESS,
                [
                    "success"  => true,
                    "redirect" => "/dashboard"
                ]
            );
       
            
        }
        
        return $this->response->json(Controller::ERR_BAD_PARAMS, ["error" => "Paramètres manquant"]);
    }
    
    //AjaxUnlockAcount
    /**
     * Déverrouiller un compte 2FA via AJAX.
     *
     * Effets de bord : mise à jour en base.
     *
     * @return Response Réponse JSON.
     */
    private function AjaxUnlockAccount(): Response
    {
        if ($this->request->isPost()) {
            // Récupérer l'ID du compte à supprimer
            $account_id = $this->request->data('account_id');
            if (!empty($account_id)) {
                // Trouver le compte à modifier
                $account = new User($account_id);
                // Décverouiller le compte
                $account->setFailed2faAttempts(0)
                        ->setLast2faAttempt(null)
                        ->save()
                ;
                return $this->response->json(Controller::SUCCESS, ["success" => true]);
            }
            return $this->response->json(Controller::ERR_BAD_PARAMS, ["error" => "Paramètre manquant"]);
        }
        return $this->response->json(Controller::ERR_INVALID_REQUEST, ["error" => "Requête invalide"]);
    }
    
}