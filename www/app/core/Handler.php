<?php

use Whoops\Handler\JsonResponseHandler;
use Whoops\Run;
/**
 * Centraliser la gestion des erreurs et exceptions non interceptées.
 *
 * S'appuie sur Whoops pour le rendu et journalise via Logger.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */
class Handler
{
    
    /**
     * Enregistrer les handlers d'erreurs et d'exceptions.
     *
     * Effets de bord : configure Whoops, enregistre des callbacks et écrit la réponse.
     *
     * @return void Aucune valeur de retour.
     */
    public static function register()
    {
        error_reporting(LOG_LEVEL);
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        // Launch Exception handlers
        $whoops = new Run;
        // Handler that can Log info
        $whoops->appendHandler(
            new \Whoops\Handler\CallbackHandler(function ($exception, $inspector, $run) {
                
                $isException = $exception instanceof \Exception;
                if (!$isException) {
                    // Si l'erreur n'est pas une exception, on la transforme en exception pour l'envoyer au Logger.
                    $ex = new ErrorException(
                        $exception->getMessage() . "\r\n" . $exception->getTraceAsString(),
                        $exception->getCode(),
                        E_ERROR,
                        $exception->getFile(),
                        $exception->getLine()
                    );
                } else {
                    $ex = $exception;
                }
                
                try {
                    Logger::error($ex);
                } catch (Exception $e) {
                }
                
                // retour public (PROD)
                if (ENV === "PROD") {
                    self::render($ex);
                }
            })
        );
        
        if ($isAjax || SUBDOMAIN !== "BO") {
            // On renvoie du json aux app, editeur et si l'appel est en ajax
            $jsonHandler = (new JsonResponseHandler)
                ->addTraceToOutput(false)
                ->setJsonApi(false);
            $whoops->appendHandler($jsonHandler);
            $whoops->writeToOutput(true);
            $whoops->allowQuit(true);
            
        } else if (ENV !== "PROD") {
            $whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler());
        }
        $whoops->register();
        
    }
    
    
    /**
     * Afficher une page d'erreur système.
     *
     * @param Exception $e Exception capturée.
     *
     * @return Response Réponse HTTP envoyée.
     */
    private static function render(Exception $e): Response
    {
        if ($e->getCode() === 400) {
            return (new Response())->error(400, "Unknown error occured")->send();
        }
        
        return (new Response())->error(500, "Unknown error occured")->send();
    }
    
}