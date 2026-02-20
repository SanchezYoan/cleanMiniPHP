<?php


namespace NGine\Html;

/**
 * Représente une balise meta HTML.
 */
class Meta
{

    /**
     * Nom de la meta.
     *
     * @var string
     */
    private $name = "";
    /**
     * Propriété OpenGraph.
     *
     * @var string
     */
    private $property = "";
    /**
     * Contenu de la meta.
     *
     * @var string
     */
    private $content = "";
    /**
     * Charset défini.
     *
     * @var mixed
     */
    private $charset;
    /**
     * Valeur http-equiv.
     *
     * @var mixed
     */
    private $http_equiv;

    /**
     * Récupérer le nom de la meta.
     *
     * @return string Nom.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Définir le nom de la meta.
     *
     * @param string $name Nom.
     *
     * @return Meta Instance courante.
     */
    public function setName(string $name): Meta
    {
        $this->name = $name;

        return $this;
    }
    
    /**
     * Récupérer la propriété OpenGraph.
     *
     * @return string Propriété.
     */
    public function getProperty(){
        return $this->property;
    }
    
    /**
     * Définir la propriété OpenGraph.
     *
     * @param string $property Propriété.
     *
     * @return $this Instance courante.
     */
    public function setProperty(string $property): Meta
    {
        $this->property = $property;

        return $this;
    }

    /**
     * Récupérer le contenu de la meta.
     *
     * @return string Contenu.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Définir le contenu de la meta.
     *
     * @param mixed $content Contenu.
     *
     * @return Meta Instance courante.
     */
    public function setContent($content): Meta
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Récupérer le charset.
     *
     * @return mixed Charset.
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Définir le charset.
     *
     * @param mixed $charset Charset.
     *
     * @return Meta Instance courante.
     */
    public function setCharset($charset): Meta
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Récupérer la valeur http-equiv.
     *
     * @return mixed Valeur http-equiv.
     */
    public function getHttpEquiv()
    {
        return $this->http_equiv;
    }

    /**
     * Définir la valeur http-equiv.
     *
     * @param mixed $http_equiv Valeur http-equiv.
     *
     * @return Meta Instance courante.
     */
    public function setHttpEquiv($http_equiv): Meta
    {
        $this->http_equiv = $http_equiv;

        return $this;
    }



}
