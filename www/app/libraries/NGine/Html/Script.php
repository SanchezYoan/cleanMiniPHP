<?php


namespace NGine\Html;

/**
 * Représente une balise <script>.
 */
class Script
{

    /**
     * Nonce CSP.
     *
     * @var mixed
     */
    private $nonce;
    /**
     * Source du script.
     *
     * @var mixed
     */
    private $src;
    /**
     * Type MIME.
     *
     * @var mixed
     */
    private $type;
    /**
     * Exécution asynchrone.
     *
     * @var mixed
     */
    private $async;
    /**
     * Chargement différé.
     *
     * @var mixed
     */
    private $defer;
    /**
     * Attribut crossorigin.
     *
     * @var mixed
     */
    private $crossorigin;
    /**
     * Indique un script noModule.
     *
     * @var mixed
     */
    private $nomodule;

    /**
     * Récupérer le nonce CSP.
     *
     * @return mixed Nonce.
     */
    public function getNonce()
    {
        return $this->nonce;
    }

    /**
     * Définir le nonce CSP.
     *
     * @param mixed $nonce Nonce.
     *
     * @return Script Instance courante.
     */
    public function setNonce($nonce): Script
    {
        $this->nonce = $nonce;

        return $this;
    }

    /**
     * Récupérer la source du script.
     *
     * @return mixed Source.
     */
    public function getSrc()
    {
        return $this->src;
    }

    /**
     * Définir la source du script.
     *
     * @param mixed $src Source.
     *
     * @return Script Instance courante.
     */
    public function setSrc($src): Script
    {
        $this->src = $src;

        return $this;
    }

    /**
     * Récupérer le type MIME.
     *
     * @return mixed Type.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Définir le type MIME.
     *
     * @param mixed $type Type.
     *
     * @return Script Instance courante.
     */
    public function setType($type): Script
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Récupérer l'attribut async.
     *
     * @return mixed Valeur async.
     */
    public function getAsync()
    {
        return $this->async;
    }

    /**
     * Définir l'attribut async.
     *
     * @param mixed $async Valeur async.
     *
     * @return Script Instance courante.
     */
    public function setAsync($async): Script
    {
        $this->async = $async;

        return $this;
    }

    /**
     * Récupérer l'attribut defer.
     *
     * @return mixed Valeur defer.
     */
    public function getDefer()
    {
        return $this->defer;
    }

    /**
     * Définir l'attribut defer.
     *
     * @param mixed $defer Valeur defer.
     *
     * @return Script Instance courante.
     */
    public function setDefer($defer): Script
    {
        $this->defer = $defer;

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
     * @return Script Instance courante.
     */
    public function setCrossorigin($crossorigin): Script
    {
        $this->crossorigin = $crossorigin;

        return $this;
    }

    /**
     * Récupérer l'attribut nomodule.
     *
     * @return mixed Valeur nomodule.
     */
    public function getNomodule()
    {
        return $this->nomodule;
    }

    /**
     * Définir l'attribut nomodule.
     *
     * @param mixed $nomodule Valeur nomodule.
     *
     * @return Script Instance courante.
     */
    public function setNomodule($nomodule): Script
    {
        $this->nomodule = $nomodule;

        return $this;
    }



}
