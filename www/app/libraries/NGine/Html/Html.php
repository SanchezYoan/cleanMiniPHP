<?php


namespace NGine\Html;

use NGine\Html\Link;
use NGine\Html\Meta;
use NGine\Html\Script;

/**
 * Agrège des balises HTML de type meta, link et script.
 */
class Html
{
    /**
     * @var Meta[]
     */
    private $metas = [];
    /**
     * @var Link[]
     */
    private $links = [];
    /**
     * @var Script[]
     */
    private $scripts = [];

    /**
     * Réinitialiser les balises accumulées.
     *
     * @return $this Instance courante.
     */
    public function reset(): Html
    {
        $this->metas = [];
        $this->links = [];
        $this->css = [];
        $this->js = [];

        return $this;
    }

    /**
     * Ajouter une balise meta.
     *
     * @param Meta $meta Meta à ajouter.
     *
     * @return $this Instance courante.
     */
    public function addMeta(Meta $meta): Html
    {

        $this->metas[] = $meta;

        return $this;
    }

    /**
     * Ajouter une balise link.
     *
     * @param \NGine\Html\Link $link Lien à ajouter.
     *
     * @return $this Instance courante.
     */
    public function addLink(Link $link): Html
    {

        $this->links[] = $link;

        return $this;
    }

    /**
     * Ajouter une balise script.
     *
     * @param \NGine\Html\Script $script Script à ajouter.
     *
     * @return $this Instance courante.
     */
    public function addScript(Script $script): Html
    {

        $this->scripts[] = $script;

        return $this;
    }

    /**
     * Générer le HTML des balises enregistrées.
     *
     * @return string HTML concaténé.
     */
    public function output(): string
    {
        $metas = [];
        $links = [];
        $scripts = [];
        foreach ($this->metas as $meta) {

            $name = $meta->getName();
            $property = $meta->getProperty();
            $content = $meta->getContent();
            $charset = $meta->getCharset();
            $http_equiv = $meta->getHttpEquiv();

            $name = empty($name) ? null : " name=\"$name\"";
            $property = empty($property) ? null : " property=\"$property\"";
            $content = empty($content) ? null : " content=\"$content\"";
            $http_equiv = empty($http_equiv) ? null : " http_equiv=\"$http_equiv\"";
            $charset = empty($charset) ? null : " charset=\"$charset\"";

            $metas[$name.$property] = "<meta $name $property $content $http_equiv $charset >";

        }

        foreach ($this->links as $link) {

            $href = $link->getHref() ?? false;
            $rel = $link->getRel() ?? false;
            $importance = $link->getImportance() ?? false;
            $type = $link->getType() ?? false;
            $hrefLang = $link->getHreflang() ?? false;
            $disabled = $link->getDisabled() ?? false;
            $as = $link->getAs() ?? false;
            $crossOrigin = $link->getCrossorigin() ?? false;
            $referrerPolicy = $link->getReferrerpolicy() ?? false;
            $integrity = $link->getIntegrity() ?? false;
            $sizes = $link->getSizes() ?? false;
            $title = $link->getTitle() ?? false;
            $media = $link->getMedia() ?? false;

            $rel = $rel ? "rel='$rel'" : null;
            $href = $href ? "href='$href'" : null;
            $importance = $importance ? "importance='$importance'" : null;
            $sizes = $sizes ? "sizes='$sizes'" : null;
            $crossOrigin = $crossOrigin ? "crossorigin='$crossOrigin'" : null;
            $referrerPolicy = $referrerPolicy ? "referrerpolicy='$referrerPolicy'" : null;
            $type = $type ? "type='$type'" : null;
            $hrefLang = $hrefLang ? "hreflang='$hrefLang'" : null;
            $disabled = $disabled ? "disabled='$disabled'" : null;
            $as = $as ? "as='$as'" : null;
            $media = $media ? "media='$media'" : null;
            $title = $title ? "title='$title'" : null;
            $integrity = $integrity ? "integrity='$integrity'" : null;

            $links[$rel.$href] = "<link $rel $href $importance $sizes $crossOrigin $referrerPolicy $type $hrefLang $disabled $as $media $title $integrity >";
        }

        foreach ($this->scripts as $script) {

            $src = $script->getSrc() ?? false;
            $type = $script->getType() ?? false;
            $async = $script->getAsync() ?? false;
            $crossOrigin = $script->getCrossorigin() ?? false;

            $src = $src ? "src='$src'" : null;
            $type = $type ? "type='$type'" : null;
            $async = $async ? "async='$async'" : null;
            $crossOrigin = $crossOrigin ? "crossorigin='$crossOrigin'" : null;

            $scripts[] = "<script $src $type $async $crossOrigin ></script>";

        }

        $result = implode("\n", $metas)
            . "\n" . implode("\n", $links)
            . "\n" . implode("\n", $scripts);

        return $result;
    }
    
    /**
     * Récupérer le contenu d'une meta par nom ou propriété.
     *
     * @param string $var Nom ou propriété recherchée.
     *
     * @return string|null Contenu trouvé ou null.
     */
    public function get($var): ?string
    {
        foreach ($this->metas as $meta) {
    
            $name     = $meta->getName();
            $property = $meta->getProperty();
    
            if ($name === $var) {
                return $meta->getContent();
            } else if ($property === $var) {
                return $meta->getContent();
            }
        }
        
        return null;
    }
}
