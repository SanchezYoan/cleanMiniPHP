<?php


namespace NGine\Html;

/**
 * Représente une balise <link>.
 */
class Link
{

    /**
     * Relation du lien (rel).
     *
     * @var mixed
     */
    private $rel;
    /**
     * Type MIME.
     *
     * @var mixed
     */
    private $type;
    /**
     * URL cible.
     *
     * @var mixed
     */
    private $href;
    /**
     * Tailles associées.
     *
     * @var mixed
     */
    private $sizes;
    /**
     * Media query associée.
     *
     * @var mixed
     */
    private $media;
    /**
     * Attribut crossorigin.
     *
     * @var mixed
     */
    private $crossorigin;
    /**
     * Attribut as pour preloading.
     *
     * @var mixed
     */
    private $as;
    /**
     * Attribut disabled.
     *
     * @var mixed
     */
    private $disabled;
    /**
     * Langue alternative (hreflang).
     *
     * @var mixed
     */
    private $hreflang;
    /**
     * Importance du lien.
     *
     * @var mixed
     */
    private $importance;
    /**
     * Intégrité SRI.
     *
     * @var mixed
     */
    private $integrity;
    /**
     * Politique de référent.
     *
     * @var mixed
     */
    private $referrerpolicy;
    /**
     * Titre du lien.
     *
     * @var mixed
     */
    private $title;

    /**
     * Récupérer la relation du lien (rel).
     *
     * @return mixed Valeur rel.
     */
    public function getRel()
    {
        return $this->rel;
    }

    /**
     * Définir la relation du lien (rel).
     *
     * @param mixed $rel Valeur rel.
     *
     * @return Link Instance courante.
     */
    public function setRel($rel): Link
    {
        $this->rel = $rel;

        return $this;
    }

    /**
     * Récupérer le type MIME.
     *
     * @return mixed Type MIME.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Définir le type MIME.
     *
     * @param mixed $type Type MIME.
     *
     * @return Link Instance courante.
     */
    public function setType($type): Link
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Récupérer l'URL cible.
     *
     * @return mixed URL.
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * Définir l'URL cible.
     *
     * @param mixed $href URL.
     *
     * @return Link Instance courante.
     */
    public function setHref($href): Link
    {
        $this->href = $href;

        return $this;
    }

    /**
     * Récupérer les tailles associées.
     *
     * @return mixed Tailles.
     */
    public function getSizes()
    {
        return $this->sizes;
    }

    /**
     * Définir les tailles associées.
     *
     * @param mixed $sizes Tailles.
     *
     * @return Link Instance courante.
     */
    public function setSizes($sizes): Link
    {
        $this->sizes = $sizes;

        return $this;
    }

    /**
     * Récupérer la media query.
     *
     * @return mixed Media query.
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Définir la media query.
     *
     * @param mixed $media Media query.
     *
     * @return Link Instance courante.
     */
    public function setMedia($media): Link
    {
        $this->media = $media;

        return $this;
    }

    /**
     * Récupérer l'attribut crossorigin.
     *
     * @return mixed Valeur crossorigin.
     */
    public function getCrossorigin()
    {
        return $this->crossorigin;
    }

    /**
     * Définir l'attribut crossorigin.
     *
     * @param mixed $crossorigin Valeur crossorigin.
     *
     * @return Link Instance courante.
     */
    public function setCrossorigin($crossorigin): Link
    {
        $this->crossorigin = $crossorigin;

        return $this;
    }

    /**
     * Récupérer l'attribut as.
     *
     * @return mixed Valeur as.
     */
    public function getAs()
    {
        return $this->as;
    }

    /**
     * Définir l'attribut as.
     *
     * @param mixed $as Valeur as.
     *
     * @return Link Instance courante.
     */
    public function setAs($as): Link
    {
        $this->as = $as;

        return $this;
    }

    /**
     * Récupérer l'état disabled.
     *
     * @return mixed Valeur disabled.
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * Définir l'état disabled.
     *
     * @param mixed $disabled Valeur disabled.
     *
     * @return Link Instance courante.
     */
    public function setDisabled($disabled): Link
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Récupérer le hreflang.
     *
     * @return mixed Valeur hreflang.
     */
    public function getHreflang()
    {
        return $this->hreflang;
    }

    /**
     * Définir le hreflang.
     *
     * @param mixed $hreflang Valeur hreflang.
     *
     * @return Link Instance courante.
     */
    public function setHreflang($hreflang): Link
    {
        $this->hreflang = $hreflang;

        return $this;
    }

    /**
     * Récupérer l'importance.
     *
     * @return mixed Valeur importance.
     */
    public function getImportance()
    {
        return $this->importance;
    }

    /**
     * Définir l'importance.
     *
     * @param mixed $importance Valeur importance.
     *
     * @return Link Instance courante.
     */
    public function setImportance($importance): Link
    {
        $this->importance = $importance;

        return $this;
    }

    /**
     * Récupérer la valeur d'intégrité (SRI).
     *
     * @return mixed Valeur integrity.
     */
    public function getIntegrity()
    {
        return $this->integrity;
    }

    /**
     * Définir la valeur d'intégrité (SRI).
     *
     * @param mixed $integrity Valeur integrity.
     *
     * @return Link Instance courante.
     */
    public function setIntegrity($integrity): Link
    {
        $this->integrity = $integrity;

        return $this;
    }

    /**
     * Récupérer la politique de référent.
     *
     * @return mixed Valeur referrerpolicy.
     */
    public function getReferrerpolicy()
    {
        return $this->referrerpolicy;
    }

    /**
     * Définir la politique de référent.
     *
     * @param mixed $referrerpolicy Valeur referrerpolicy.
     *
     * @return Link Instance courante.
     */
    public function setReferrerpolicy($referrerpolicy): Link
    {
        $this->referrerpolicy = $referrerpolicy;

        return $this;
    }

    /**
     * Récupérer le titre du lien.
     *
     * @return mixed Titre.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Définir le titre du lien.
     *
     * @param mixed $title Titre.
     *
     * @return Link Instance courante.
     */
    public function setTitle($title): Link
    {
        $this->title = $title;

        return $this;
    }



}
