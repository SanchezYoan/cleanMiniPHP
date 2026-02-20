<?php

use NGine\Breadcrumb\Breadcrumb;
/**
 * Fournir la base des contrôleurs applicatifs.
 *
 * Centralise la requête, la réponse, la validation et les helpers communs.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */
class Controller
{
    /**
     * Code HTTP OK.
     */
    public const SUCCESS = 200;
    /**
     * Requête invalide.
     */
    public const ERR_INVALID_REQUEST = 400;
    /**
     * Non autorisé.
     */
    public const ERR_UNAUTHORIZED = 401;
    /**
     * Accès interdit.
     */
    public const ERROR_FORBIDDEN = 403;
    /**
     * Non trouvé.
     */
    public const ERR_NOT_FOUND = 404;
    /**
     * Erreur de validation.
     */
    public const ERR_VALIDATION = 422;
    /**
     * Paramètres invalides.
     */
    public const ERR_BAD_PARAMS = 452;
    /**
     * Session expirée.
     */
    public const ERR_EXPIRED = 453;
    /**
     * Appareil inconnu.
     */
    public const ERR_UNKNOWN_DEVICE = 454; // bad url, bad method called
    /**
     * Utilisateur inconnu.
     */
    public const ERR_UNKNOWN_USER = 455; // not authenticated, not logged in.
    /**
     * Erreur interne.
     */
    public const ERR_INTERNAL_ERROR = 500; // not allowed
    /**
     * Erreur serveur personnalisée.
     */
    public const ERR_CUSTOM = 503; // Server error
    /**
     * Fil d'Ariane du contrôleur.
     */
    public ?Breadcrumb $breadcrumb = null;
    /**
     * Utilisateur courant (API/app).
     */
    public ?User $user = null;
    /**
     * Administrateur courant (BO).
     */
    public ?User $admin = null;
    /**
     * Appareil courant (API/app).
     */
    public ?Device $device = null;
    /**
     * Validateur de champs.
     */
    public Validation $validator;
    /**
     * Langue courante (ISO 639-1).
     */
    public string $lang = "fr";
    /**
     * Requête HTTP en cours.
     *
     * @var Request
     */
    public Request $request;
    /**
     * Réponse HTTP en cours.
     *
     * @var Response
     */
    public Response $response;
    /**
     * Composants chargés.
     *
     * @var array
     */
    public array $components = [];
    /**
     * Vue associée au contrôleur.
     *
     * @var View
     */
    protected View $view;

    /**
     * Construire un contrôleur et hydrater les dépendances.
     *
     * @param Request|null  $request  Requête injectée (optionnelle).
     * @param Response|null $response Réponse injectée (optionnelle).
     *
     * @throws Exception Si l'initialisation échoue.
     */
    public function __construct(?Request $request = null, ?Response $response = null)
    {
        $this->request = $request ?? new Request();
        $this->response = $response ?? new Response();
        $headers = apache_request_headers();
        $languages = $headers['Accept-Language'] ?? "en,en-Us";
        $requestLang = explode(",", $languages)[0];
        $this->lang = substr($requestLang, 0, 2);
        $this->validator = new Validation($this->lang);
        if (Session::isLoggedIn()) {
            $this->admin = Session::get("admin");
        }
        $this->view = new View($this);


    }

    /**
     * Exécuter la phase de démarrage du contrôleur.
     *
     * @return Response|null Réponse immédiate ou null pour continuer.
     * @throws Exception Si l'initialisation échoue.
     */
    public function startupProcess(): ?Response
    {
        $result = $this->beforeAction();

        if ($result instanceof Response) {
            return $result;
        }
        return null;
    }


    /**
     * Exécuter la logique avant l'action du contrôleur.
     *
     * @return Response|null Réponse immédiate ou null pour poursuivre.
     */
    public function beforeAction(): ?Response
    {
        
        return null;
    }

    /**
     * Rendre une page d'erreur HTML.
     *
     * @param int    $code    Code HTTP.
     * @param string $message Message à afficher.
     *
     * @return Response Réponse HTML.
     */
    public function error(int $code, string $message): Response
    {

        $html = $this->view->renderWithLayouts(
            VIEWS . "/layouts/frontend",
            VIEWS . "/errors/error.php",
            ["code" => $code, "message" => $message]
        );

        return $this->response->setContent($html);
    }

    /**
     * Charger dynamiquement un modèle par son nom.
     *
     * @param string $name Nom de propriété demandé.
     *
     * @return object Instance créée.
     */
    public function __get($name): object
    {
        $uc_model = ucwords($name);

        if (isset($this->{$name})) {
            return $this->{$name};
        }

        $this->{$name} = new $uc_model();
        return $this->{$name};
    }

    /**
     * Affecter dynamiquement une propriété.
     *
     * @param string $name  Nom de propriété.
     * @param object $value Valeur à stocker.
     *
     * @return void Aucune valeur de retour.
     */
    public function __set($name, object $value): void
    {

        $this->{$name} = $value;
    }

    /**
     * Vérifier si une propriété dynamique existe.
     *
     * @param string $name Nom de propriété.
     *
     * @return bool true si définie.
     */
    public function __isset($name): bool
    {
        return isset($this->{$name});
    }


    /**
     * Forcer une requête HTTPS.
     *
     * @return void Aucune valeur de retour.
     */
    public function forceSSL(): void
    {
        $secured = "https://" . $this->request->currentUrl();
        Redirector::to($secured);
    }

}