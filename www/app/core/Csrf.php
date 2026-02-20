<?php

/**
 * Gérer les jetons CSRF pour la session courante.
 *
 * Les opérations manipulent la session PHP et peuvent envoyer un en-tête
 * `X-CSRF-Token` afin d'exposer le jeton au client.
 */
class Csrf
{
    /**
     * Clé de stockage du jeton CSRF en session.
     */
    private const SESSION_KEY = 'csrf_token';

    /**
     * Récupérer le jeton CSRF en le générant si nécessaire.
     *
     * Initialise la session si besoin et expose le jeton via un en-tête HTTP
     * lorsque celui-ci est créé.
     */
    public static function token(): string
    {
        Session::init();
        $token = Session::get(self::SESSION_KEY);
        if (empty($token)) {
            $token = self::generate();
        }

        return $token;
    }

    /**
     * Valider un jeton CSRF fourni par le client.
     *
     * @param string|null $token Jeton fourni (formulaire ou en-tête).
     *
     * @return bool true si le jeton correspond à celui en session, false sinon.
     */
    public static function validate(?string $token): bool
    {
        Session::init();
        $current = Session::get(self::SESSION_KEY);

        if (empty($token) || empty($current) || !hash_equals($current, $token)) {
            return false;
        }

        self::rotate();
        return true;
    }

    /**
     * Valider un jeton CSRF présent dans la requête.
     *
     * Le jeton est recherché en priorité dans les données de formulaire puis
     * dans les en-têtes (`X-CSRF-Token`). La session est initialisée si besoin.
     *
     * @param Request $request Requête contenant les données à inspecter.
     *
     * @return bool true si le jeton est valide, false sinon.
     */
    public static function validateRequest(Request $request): bool
    {
        $candidate = $request->data('csrf_token');

        if (empty($candidate)) {
            $candidate = self::extractFromHeaders($request->getHeaders());
        }

        return self::validate(is_string($candidate) ? $candidate : null);
    }

    /**
     * Supprimer le jeton CSRF de la session courante.
     */
    public static function clear(): void
    {
        Session::delete(self::SESSION_KEY);
    }

    /**
     * Régénérer un jeton CSRF et le stocker en session.
     */
    private static function rotate(): string
    {
        return self::generate();
    }

    /**
     * Générer un jeton CSRF sécurisé et l'exposer côté client.
     *
     * @throws Exception Si la source d'aléa cryptographique est indisponible.
     */
    private static function generate(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::set(self::SESSION_KEY, $token);
        self::expose($token);

        return $token;
    }

    /**
     * Extraire le jeton CSRF depuis les en-têtes HTTP fournis.
     *
     * @param array<string, mixed>|string $headers En-têtes bruts ou valeur unique.
     *
     * @return string|null Jeton détecté ou null si absent.
     */
    private static function extractFromHeaders(array|string $headers): ?string
    {
        if (!is_array($headers) || empty($headers)) {
            return null;
        }

        foreach ($headers as $name => $value) {
            if (strtolower($name) === 'x-csrf-token') {
                return is_array($value) ? end($value) : $value;
            }
        }

        return null;
    }

    /**
     * Exposer le jeton au client via l'en-tête HTTP `X-CSRF-Token`.
     *
     * @param string $token Jeton à envoyer.
     */
    private static function expose(string $token): void
    {
        if (!headers_sent()) {
            header('X-CSRF-Token: ' . $token);
        }
    }
}
