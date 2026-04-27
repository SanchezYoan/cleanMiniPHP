<?php

/**
 * Rendre les vues HTML/JSON.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class View
{
    /**
     * Administrateur courant.
     */
    public ?User $admin = null;
    /**
     * Contrôleur associé.
     */
    public Controller $controller;
    /**
     * Helper HTML.
     */
    public ?\NGine\Html\Html $html = null;
    /**
     * Indique si le client est mobile.
     */
    public bool $isMobile;
    /**
     * Contenu SEO additionnel.
     */
    public string $referencementSEO = '';
    /**
     * Feuilles CSS optionnelles.
     *
     * @var list<string>
     */
    private array $optionalCSS = [];
    /**
     * Scripts JS optionnels.
     *
     * @var list<array<string, string>>
     */
    private array $optionalJS = [];

    // Mantis 1685: Amélioration SEO CUA - Ajout d'une information pour le SEO
    /**
     * En-têtes additionnels pour la vue.
     *
     * @var array<string, mixed>
     */
    private array $headers = [];

    /**
     * Construire une vue.
     *
     * @param Controller $controller Contrôleur associé.
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
        $this->html = new \NGine\Html\Html();
        $this->referencementSEO = '';
        $description = \NGine\Translate::get("nouveauprojet.head.description");
        $title = \NGine\Translate::get("nouveauprojet.head.title");
        $keywords = \NGine\Translate::get("nouveauprojet.head.tags");
        $this->admin = $controller->admin;


        $this->html
            ->addMeta((new \NGine\Html\Meta())->setName("keywords")->setContent($keywords))
            ->addMeta((new \NGine\Html\Meta())->setName("description")->setContent($description))
            ->addMeta((new \NGine\Html\Meta())->setName("application-name")->setContent(Config::get("APP.NAME")))
            ->addMeta((new \NGine\Html\Meta())->setProperty("og:site_name")->setContent(DOMAIN))
            ->addMeta((new \NGine\Html\Meta())->setProperty("og:title")->setContent(Config::get("APP.NAME")))
            ->addMeta((new \NGine\Html\Meta())->setProperty("og:description")->setContent($description))
            ->addMeta((new \NGine\Html\Meta())->setProperty("og:url")->setContent(DOMAIN))
            ->addMeta((new \NGine\Html\Meta())->setProperty("og:type")->setContent("website"))
            ->addMeta((new \NGine\Html\Meta)->setProperty("og:locale")->setContent("fr_FR"))
            ->addMeta((new \NGine\Html\Meta)->setName("author")->setContent(Config::get("HTML.HEADERS.AUTHOR")))
            ->addMeta((new \NGine\Html\Meta)->setProperty('og:image')->setContent(PUBLIC_ROOT . "/assets/img/nouveauprojet-og-image.jpg"))
            ->addMeta((new \NGine\Html\Meta)->setProperty('og:image:width')->setContent(474))
            ->addMeta((new \NGine\Html\Meta)->setProperty('og:image:height')->setContent(494));
        $this->isMobile = Utility::isMobile();
    }

    /**
     * Rendre un fichier PHP en HTML.
     *
     * @param string               $filePath Chemin du fichier.
     * @param array<string, mixed>|null $data Données extraites dans la vue.
     *
     * @return string HTML rendu.
     */
    public static function renderFile(string $filePath, ?array $data = null): string
    {

        if (!empty($data)) {
            extract($data, EXTR_OVERWRITE);
        }

        // Using include Vs require is better,
        // because we may want to include a file more than once.
        ob_start();
        include $filePath . "";
        return ob_get_clean();
    }

    /**
     * Définir les en-têtes de rendu.
     *
     * @param array<string, mixed> $headers En-têtes à injecter.
     *
     * @return View Instance courante.
     */
    public function setHeaders(array $headers): View
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Ajouter des scripts JS optionnels.
     *
     * @param list<array<string, string>> $links Scripts à ajouter.
     *
     * @return View Instance courante.
     */
    public function addJS(array $links): View
    {
        $this->optionalJS = array_merge($links, $this->optionalJS);

        return $this;
    }

    /**
     * Ajouter des feuilles CSS optionnelles.
     *
     * @param list<string> $links Liens CSS.
     *
     * @return View Instance courante.
     */
    public function addCSS(array $links): View
    {
        $this->optionalCSS = array_merge($links, $this->optionalCSS);

        return $this;

    }

    /**
     * Générer les balises CSS optionnelles.
     *
     * @return string HTML des balises.
     */
    public function renderCSS(): string
    {
        $html = "";
        $assetVersion = Config::get("VERSION.ASSETS");
        
        try {
            foreach ($this->optionalCSS as $link) {
                $hasQuery = str_contains($link, "?");
                $p = $hasQuery ? "&" : "?";
                $html .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$link}{$p}ver=" . $assetVersion . "\" media=\"all\" />" . "\n";
            }
        } catch (Exception $ex) {
            Logger::error($ex);
        }

        return $html;

    }

    /**
     * Générer les balises JS optionnelles.
     *
     * @return string HTML des scripts.
     */
    public function renderJS(): string
    {
        $html = "";
        $assetVersion = Config::get("VERSION.ASSETS");
        
        try {
            foreach ($this->optionalJS as $script) {
                $link = $script['url'];
                $type = $script['type'] ?? "text/javascript";
                $hasQuery = str_contains($link, "?");
                $p = $hasQuery ? "&" : "?";
                $html .= "<script type=\"{$type}\" src=\"{$link}{$p}ver=" . $assetVersion . "\"></script>" . "\n";
            }
        } catch (Exception $ex) {
            Logger::error($ex);
        }

        return $html;

    }

    /**
     * Rendre une vue avec layout (header/footer).
     *
     * Effets de bord : écrit le contenu dans Response.
     *
     * @param string                   $layoutDir Répertoire de layout.
     * @param string                   $filePath  Vue à rendre.
     * @param array<string, mixed>|null $data      Données injectées.
     *
     * @return string HTML rendu.
     */
    public function renderWithLayouts(string $layoutDir, string $filePath, $data = null): string
    {
        if (!empty($this->headers)) {
            extract($this->headers, EXTR_OVERWRITE);
        }
        if (!empty($data)) {
            extract($data, EXTR_OVERWRITE);
        }

        // you can use require_once() immediately without ob_start() & ob_get_clean()
        // or use ob_start() & ob_get_clean() then return $renderedFile then echo,
        // but, using ob_start() & ob_get_clean() is a handy way, especially for ajax response.
        ob_start();
        require_once $layoutDir . "/header.php";
        require_once $filePath . "";
        require_once $layoutDir . "/footer.php";
        $renderedFile = ob_get_clean();

        $this->controller->response->setContent($renderedFile);

        return $renderedFile;
    }

    /**
     * Renvoyer un JSON de redirection.
     *
     * @param string $url URL cible.
     *
     * @return string JSON rendu.
     */
    public function renderRedirect(string $url): string
    {
        return $this->renderJson(['redirect' => $url]);
    }

    /**
     * Rendre un JSON et l'injecter dans Response.
     *
     * @param array<string, mixed>|null $data Données.
     *
     * @return string JSON rendu.
     */
    public function renderJson(?array $data = null): string
    {
        $jsonData = $this->jsonEncode($data);

        $this->controller->response->setType('application/json')->setContent($jsonData);

        return $jsonData;
    }

    /**
     * Sérialiser un tableau en JSON.
     *
     * @param array<string, mixed> $data Données.
     *
     * @return string JSON rendu.
     */
    public function jsonEncode(array $data): string
    {
        return json_encode($data);
    }

    /**
     * Rendre une erreur (HTML ou JSON selon contexte).
     *
     * Effets de bord : modifie le statut de Response.
     *
     * @param string|array<int, string> $errors  Erreurs à afficher.
     * @param int                      $status  Code HTTP.
     *
     * @return string HTML ou JSON rendu.
     */
    public function renderErrors($errors, int $status = 200): string
    {

        $this->controller->response->setStatusCode($status);

        if ($this->controller->request->isAjax()) {
            if (is_array($errors)) {
                $errors = implode(";<br><i class='fa fa-exclamation-circle'></i>&nbsp;", $errors);
            }
            return $this->renderJson(["error" => $errors]);
        }

        $html = $this->render(VIEWS . '/alerts/errors.php', ["errors" => $errors]);
        $this->controller->response->setContent($html);
        return $html;

    }

    /**
     * Rendre un fichier PHP en HTML.
     *
     * Effets de bord : écrit le contenu dans Response.
     *
     * @param string                   $filePath Chemin du fichier.
     * @param array<string, mixed>|null $data     Données injectées.
     *
     * @return string HTML rendu.
     */
    public function render(string $filePath, ?array $data = null): string
    {

        if (!empty($data)) {
            extract($data, EXTR_OVERWRITE);
        }

        // Using include Vs require is better,
        // because we may want to include a file more than once.
        ob_start();
        include $filePath . "";
        $renderedFile = ob_get_clean();
        $renderedFile = str_replace(["\r\n", "\n", "\n\r"], "", $renderedFile);
        $renderedFile = preg_replace('/(?<=>)\s+(?=<)/', '', $renderedFile);
        $this->controller->response->setContent($renderedFile);

        return $renderedFile;
    }

    /**
     * Rendre un message de succès.
     *
     * @param string|array<int, string> $message Message à afficher.
     *
     * @return string HTML ou JSON rendu.
     */
    public function renderSuccess($message): string
    {

        if ($this->controller->request->isAjax()) {
            return $this->renderJson(["success" => $message]);
        }

        $html = $this->render(VIEWS . '/alerts/success.php', ["success" => $message]);
        $this->controller->response->setContent($html);

        return $html;
    }

} 