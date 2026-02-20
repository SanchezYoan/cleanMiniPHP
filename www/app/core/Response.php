<?php

/**
 * Construire et envoyer une réponse HTTP.
 *
 * La classe gère le contenu, les en-têtes, les statuts et les téléchargements
 * de fichiers ou flux binaires pour les contrôleurs.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class Response
{
    
    /**
     * En-têtes HTTP à envoyer au client.
     *
     * @var array<string, string|int>
     */
    public array    $headers;

    /**
     * Corps de la réponse.
     */
    private string  $content;

    /**
     * Version HTTP utilisée pour la ligne de statut.
     */
    private ?string $version;

    /**
     * Code de statut HTTP.
     */
    private int     $statusCode;

    /**
     * Libellé du statut HTTP.
     */
    private ?string $statusText;

    /**
     * Charset appliqué sur la réponse textuelle.
     */
    private string  $charset;

    /**
     * Chemin de fichier à streamer au client (image ou téléchargement).
     */
    private ?string $file = null;

    /**
     * Données CSV à diffuser (non utilisé actuellement).
     *
     * @var array<int, array<int, string>>|null
     */
    private ?array  $csv  = null;

    /**
     * Correspondances code statut -> libellé.
     *
     * @var array<int, string>
     */
    private array $statusTexts = [
        200 => 'OK',
        302 => 'Found',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error',
    ];
    /**
     * Correspondance extensions connues -> mime types.
     *
     * @var array<string, string|array<int, string>>
     */
    private array $mimeTypes = [
        'csv'  => ['text/csv', 'application/vnd.ms-excel'],
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'pdf'  => 'application/pdf',
        'zip'  => 'application/zip',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'gif'  => 'image/gif',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
    ];
    
    /**
     * Construire une réponse HTTP initialisée.
     *
     * @param string $content Contenu initial.
     * @param int    $status  Code HTTP initial.
     * @param array  $headers En-têtes supplémentaires à appliquer.
     *
     */
    public function __construct(string $content = '', int $status = 200, array $headers = [])
    {
        
        $this->content    = $content;
        $this->statusCode = $status;
        $this->headers    = $headers;
        $this->statusText = $this->statusTexts[$status];
        $this->version    = Config::get("VERSION.APP");
        $this->charset    = 'UTF-8';
        
    }
    
    /**
     * Envoyer les en-têtes et le contenu vers le client.
     *
     * Déclenche la lecture de fichier si nécessaire puis flush la sortie pour
     * clore la requête (fastcgi_finish_request si disponible).
     */
    public function send(): Response
    {
        $this->sendHeaders();
        
        if ($this->file) {
            $this->readFile();
        } else {
            $this->sendContent();
        }
        
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else if ('cli' !== PHP_SAPI) {
            $this->flushBuffer();
        }
        
        return $this;
    }
    
    /**
     * Émettre les en-têtes HTTP configurés.
     *
     * @return void
     */
    private function sendHeaders(): void
    {
        
        // check headers have already been sent
        if (!headers_sent()) {
            
            // status
            header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText), true, $this->statusCode);
            
            // Content-Type
            // if Content-Type is already exists in headers, then don't send it
            if (!array_key_exists('Content-Type', $this->headers)) {
                header('Content-Type: ' . 'text/html; charset=' . $this->charset);
            }
            
            // headers
            foreach ($this->headers as $name => $value) {
                header($name . ': ' . $value, true, $this->statusCode);
            }
        }
        
    }
    
    /**
     * Envoyer un fichier en lecture directe.
     *
     * @return Response
     */
    private function readFile()
    {
        readfile($this->file);
        return $this;
    }
    
    /**
     * Envoyer le contenu texte/binaire courant.
     *
     * @return void
     */
    private function sendContent(): void
    {
        echo $this->content;
    }
    
    /**
     * Vider les buffers de sortie.
     *
     */
    private function flushBuffer(): void
    {
        // ob_flush();
        flush();
    }
    
    /**
     * Configurer une réponse JSON avec statut HTTP.
     *
     * Journalise les erreurs pour les codes non 200, puis sérialise le corps.
     *
     * @param int                   $code Code HTTP retourné.
     * @param array<string, mixed>  $body Corps de réponse à encoder en JSON.
     *
     * @return Response
     */
    public function json(int $code, array $body): Response
    {
        
        if (200 !== $code && !empty($body)) {
            Logger::debug("ERROR {$code} : " . var_export($body, true));
        }
        return $this->setType('application/json')
                    ->setStatusCode($code)
                    ->setContent(json_encode($body));
        
    }
    
    /**
     * Définit le contenu textuel de la réponse.
     *
     * @param string $content The response content
     * @return Response
     */
    public function setContent(string $content = ""): Response
    {
        $this->content = $content;
        return $this;
    }
    
    /**
     * Définir le code HTTP et son libellé associé.
     *
     * @param int $code HTTP status code
     * @return Response
     */
    public function setStatusCode(int $code): Response
    {
        
        $this->statusCode = (int)$code;
        $this->statusText = $this->statusTexts[$code] ?? '';
        
        return $this;
    }
    
    /**
     * Définir le Content-Type de la réponse.
     *
     * @param null|string $contentType Mime type (null pour supprimer).
     * @return Response
     */
    public function setType(?string $contentType = null): Response
    {
        
        if ($contentType === null) {
            unset($this->headers['Content-Type']);
        } else {
            $this->headers['Content-Type'] = $contentType;
        }
        
        return $this;
    }
    
    /**
     * Construire une réponse d'erreur standardisée.
     *
     * @param int|string $code    Code HTTP/clé d'erreur attendue.
     * @param string     $message Message complémentaire optionnel.
     *
     * @return Response|string Objet Response configuré (pas encore envoyé).
     */
    public function error(int|string $code, string $message = ""): Response|string
    {
        
        $errors = [
            400 => "badrequest",
            401 => "unauthenticated",
            403 => "unauthorized",
            404 => "notfound",
            409 => "conflit",
            500 => "system",
        ];
        
        if (!isset($errors[$code])) {
            $code = 400;
        }
        
        $this->setStatusCode($code);
        $this->clearBuffer();
        if (empty($this->headers['Content-Type'])) {
            $this->setType('application/json')->setContent(json_encode(["error" => $errors[$code], "code" => $code, "message" => $message]));
        } else {
            $html = View::renderFile(VIEWS . "/errors/error.php", ["error" => $errors[$code], "code" => $code, "message" => $message]);
            $this->setType('text/html')->setContent($html);
        }
        
        return $this;
    }
    
    /**
     * Effacer le buffer de sortie courant.
     *
     * Utile avant l'envoi d'un fichier ou d'une réponse JSON pour éviter tout
     * contenu parasite.
     */
    public function clearBuffer(): void
    {
        
        // check if output_buffering is active
        if (ob_get_level() > 0) {
            ob_clean();
        }
    }
    
    /**
     * Préparer l'envoi d'une image avec détection du mime type.
     *
     * @param string               $path    Chemin absolu du fichier à diffuser.
     * @param array<string, mixed> $headers En-têtes personnalisés (facultatif).
     *
     * @return Response
     */
    public function image($path, array $headers = []): Response
    {
        $this->file     = $path;
        $file_extension = pathinfo($this->file, PATHINFO_EXTENSION);
        $this->setStatusCode(200);
        
        if (empty($headers)) {
            
            $mime = $this->getMimeType($file_extension);
            if (!$mime) {
                $mime = "application/octet-stream";
            }
            
            $headers = [
                'Content-Type' => $mime,
            ];
        }
        
        $this->headers = $headers;
        $this->clearBuffer();
        
        return $this;
    }
    
    /**
     * Obtenir le mime type correspondant à une extension.
     *
     * @param string $key Extension (sans point).
     *
     * @return string|false Mime type détecté ou false si inconnu.
     */
    private function getMimeType(string $key): string|false
    {
        
        if (isset($this->mimeTypes[$key])) {
            $mime = $this->mimeTypes[$key];
            return is_array($mime) ? current($mime) : $mime;
        }
        return false;
    }
    
    /**
     * Servir un fichier YAML (ou équivalent) en téléchargement simple.
     *
     * @param string $file Chemin du fichier à retourner.
     *
     * @return Response
     */
    public function yaml(string $file): Response
    {
        $this->setStatusCode(200);
        $this->file    = $file;
        $this->headers = [
            'Content-Type' => 'text/html',
        ];
        $this->clearBuffer();
        return $this;
    }
    
    
    /**
     * Préparer un téléchargement de fichier binaire.
     *
     * @param string               $path    Chemin absolu du fichier.
     * @param array<string, mixed> $file    Métadonnées du fichier (basename, extension).
     * @param array<string, mixed> $headers En-têtes personnalisés (facultatif).
     *
     * @return Response
     */
    public function download(string $path, array $file, array $headers = []): Response
    {
        
        $this->file = $path;
        $this->setStatusCode(200);
        if (empty($headers)) {
            $mime = $this->getMimeType($file["extension"]);
            if (!$mime) {
                $mime = "application/octet-stream";
            }
            
            $headers = [
                'Content-Description'       => 'File Transfer',
                'Content-Type'              => $mime,
                'Content-Disposition'       => 'attachment; filename="' . $file["basename"] . '.' . $file["extension"] . '"',
                'Expires'                   => '0',
                'Cache-Control'             => 'must-revalidate',
                'Pragma'                    => 'public',
                'Content-Transfer-Encoding' => 'binary',
                'Content-Length'            => filesize($path),
            ];
        }
        $this->headers = $headers;
        $this->clearBuffer();
        return $this;
    }

    /**
     * Préparer l'affichage inline d'un fichier (image/PDF/etc.).
     *
     * @param string               $path    Chemin absolu du fichier.
     * @param array<string, mixed> $file    Métadonnées du fichier (basename, extension).
     * @param array<string, mixed> $headers En-têtes personnalisés (facultatif).
     *
     * @return Response
     */
    public function display(string $path, array $file, array $headers = []): Response
    {

        $this->file = $path;
        $this->setStatusCode(200);

        if (empty($headers)) {

            $mime = $this->getMimeType($file["extension"]);
            if (!$mime) {
                $mime = "application/octet-stream";
            }

            $headers = [
                'Content-Type'              => $mime,
                'Content-Disposition'       => 'inline; filename="' . $file["basename"] . '"',
                'Content-Length'            => filesize($path),
            ];
        }

        $this->headers = $headers;
        $this->clearBuffer();
        return $this;
    }
} 
