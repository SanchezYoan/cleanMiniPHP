<?php

namespace NGine\Html;
use \View;

use const ENV;
use const PHP_EOL;

/**
 * Génère des balises meta SEO selon l'environnement et la page.
 */
class Seo
{
    
    /**
     * Générer les balises meta SEO selon la page courante.
     *
     * @param View $view Vue courante contenant la requête.
     *
     * @return string Balises meta SEO.
     */
    public static function referencer(View $view): string
    {
        $metaNoIndexNoFollow = implode(PHP_EOL . "\t",
                                [
                                    '<meta name="robots" content="noindex, nofollow">',
                                    '<meta name="googlebot" content="noindex, nofollow">',
                                ]) . PHP_EOL;

        $metaNoIndex         = implode(PHP_EOL . "\t",
                                [
                                    '<meta name="robots" content="noindex">',
                                    '<meta name="googlebot" content="noindex">',
                                ]) . PHP_EOL;
        if (ENV !== "PROD") {
            return $metaNoIndexNoFollow;
        }

        if (is_array($view->controller->request->params)) {
            
            $controller = $view->controller->request->params['controller'];
            $action     = $view->controller->request->params['action'];
            
            if (strtolower($controller) === 'accueilcontroller' && $action === 'politiqueConfidentialite') {
                return $metaNoIndexNoFollow;
            }
            
            if (strtolower($controller) === 'logincontroller' && $action === 'index') {
                return $metaNoIndexNoFollow;
            }
            
            if (strtolower($controller) === 'contactcontroller' && $action === 'index') {
                return $metaNoIndexNoFollow;
            }

            // Mantis 1685: Amélioration SEO CUA - Mention legales - NoIndex & NoFollow
            /* Finalement, on conserve l'indexation et le suivi de ces pages
            if (strtolower($controller) === 'actuscontroller' && $action === 'index') {
                if(   ($view->referencementSEO === 'source')
                    ||($view->referencementSEO === 'commune')
                    ||($view->referencementSEO === 'departement')
                    ||($view->referencementSEO === 'region')
                    ||($view->referencementSEO === 'global')){
                    return $metaNoIndexNoFollow;
                }
            }
            */

            // Mantis 1685: Amélioration SEO CUA - Mention legales - NoIndex & NoFollow
            if (strtolower($controller) === 'documentcontroller' && (($action === 'mentionslegales')||($action === 'cgu'))) {
                return $metaNoIndexNoFollow;
            }

            // Mantis 1685: Amélioration SEO CUA - Les Parteniares - NoIndex mais follow
            if (strtolower($controller) === 'partenairescontroller' && $action === 'index') {
                return $metaNoIndex;
            }
        }
        
        return '';
    }
}
