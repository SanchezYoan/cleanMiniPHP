<?php


namespace NGine\Breadcrumb;

/**
 * Décrit un lien d'un fil d'Ariane
 */
class Link
{

    /**
     * URL de destination
     *
     * @var mixed
     */
    private $href;
    /**
     * Titre du lien
     *
     * @var mixed
     */
    private $title;
    /**
     * Texte affiché.
     *
     * @var mixed
     */
    private $text;
    /**
     * Cible HTML (ex: _blank)
     *
     * @var mixed
     */
    private $target;
    /**
     * Classe CSS du lien
     *
     * @var mixed
     */
    private $class;
    /**
     * Métadonnée personnalisée
     *
     * @var mixed
     */
    private $extend;
    /**
     * Nom d'icône associé.
     *
     * @var mixed
     */
    private $icon;

    /**
     * Récupérer l'icône associée
     *
     * @return mixed Icône.
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Définir l'icône associée
     *
     * @param string $icon Icône (nom)
     *
     * @return Link Instance courante
     */
    public function setIcon(string $icon)
    {
        $this->icon = $icon;

        return $this;
    }



    /**
     * Récupérer l'URL du lien
     *
     * @return mixed URL
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * Définir l'URL du lien
     *
     * @param mixed $href URL
     *
     * @return Link Instance courante
     */
    public function setHref($href)
    {
        $this->href = $href;

        return $this;
    }

    /**
     * Récupérer le titre du lien
     *
     * @return mixed Titre
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Définir le titre du lien
     *
     * @param mixed $title Titre
     *
     * @return Link Instance courante
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Récupérer le texte affiché
     *
     * @return mixed Texte
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Définir le texte affiché
     *
     * @param mixed $text Texte
     *
     * @return Link Instance courante
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Récupérer la cible du lien.
     *
     * @return mixed Cible (ex: _blank).
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Définir la cible du lien
     *
     * @param mixed $target Cible (ex: _blank)
     *
     * @return Link Instance courante
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Récupérer la classe CSS
     *
     * @return mixed Classe CSS
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Définir la classe CSS
     *
     * @param mixed $class Classe CSS
     *
     * @return Link Instance courante
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Récupérer la métadonnée extend
     *
     * @return mixed Métadonnée
     */
    public function getExtend()
    {
        return $this->extend;
    }

    /**
     * Définir la métadonnée extend
     *
     * @param mixed $extend Métadonnée
     *
     * @return Link Instance courante
     */
    public function setExtend($extend)
    {
        $this->extend = $extend;

        return $this;
    }

}