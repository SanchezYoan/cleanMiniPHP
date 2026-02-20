<?php

/**
 * Centraliser les redirections HTTP.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class Redirector
{

    /**
     * Empêcher l'instanciation.
     */
    private function __construct()
    {
    }
    
    /**
     * Rediriger vers une URL absolue ou relative.
     *
     * Effets de bord : envoie un header Location.
     *
     * @param string $location URL de destination.
     * @param string $query    Query string à append.
     *
     * @return Response Réponse HTTP 302 envoyée.
     */
    public static function to(string $location, string $query = ""): Response
    {

        if (!empty($query)) {
            $query = '?' . http_build_query((array)$query, null, '&');
        }
    
        return (new Response('', 302, ["Location" => $location . $query]))->send();
    }
    
    /**
     * Rediriger depuis la racine publique.
     *
     * Effets de bord : envoie un header Location.
     *
     * @param string $location Chemin relatif.
     * @param string $query    Query string à append.
     *
     * @return Response Réponse HTTP 302 envoyée.
     */
    public static function root(string $location = "", string $query = ""): Response
    {

        if (!empty($query)) {
            $query = '?' . http_build_query((array)$query, null, '&');
        }

        $response = new Response('', 302, ["Location" => PUBLIC_ROOT . $location . $query]);

        return $response->send();
    }

    /**
     * Rediriger vers la page d'accueil.
     *
     * @return Response Réponse HTTP 302 envoyée.
     */
    public static function home(): Response
    {
        return self::to(PUBLIC_ROOT . "/");
    }
    
    /**
     * Rediriger vers la page de connexion.
     *
     * @return Response Réponse HTTP 302 envoyée.
     */
    public static function connexion(): Response
    {
        return self::to(PUBLIC_ROOT . "/login");
    }

    /**
     * Rediriger vers le référent ou la racine.
     *
     * @return Response Réponse HTTP 302 envoyée.
     */
    public static function back(): Response
    {
        if(isset($_SERVER["HTTP_REFERER"])){
            $referer = $_SERVER["HTTP_REFERER"];
            
            $request = new Request();
            
            if ($request->validateUrl($referer)) { // Ajout Securité Cédric : valider le host avant la redirection
                return self::to($referer);
            }

        }
    
        return self::to(PUBLIC_ROOT . "/" );
    }

    /**
     * Rediriger vers la déconnexion.
     *
     * @return Response Réponse HTTP 302 envoyée.
     */
    public static function logout(): Response
    {
        return self::to(PUBLIC_ROOT . "/login/logout");
    }
    
    /**
     * Rediriger vers le dashboard.
     *
     * @return Response Réponse HTTP 302 envoyée.
     */
    public static function dashboard(): Response
    {
        return self::to(PUBLIC_ROOT . "/dashboard");
    }


    /**
     * Rediriger vers la page de login.
     *
     * @param string|null $redirect_url URL de redirection post-login.
     *
     * @return Response Réponse HTTP 302 envoyée.
     */
    public static function login(?string $redirect_url = null): Response
    {
        if (!empty($redirect_url)) {
            return self::to("/login?redirect=" . urlencode($redirect_url));
        }
    
        return self::to("/login");
    }
    

} 