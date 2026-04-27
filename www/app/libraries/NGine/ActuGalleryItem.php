<?php

namespace NGine;
/**
 * Représente un item d'image dans une galerie d'actualités.
 */
class ActuGalleryItem implements \JsonSerializable
{
    /**
     * Identifiant de l'item.
     *
     * @var mixed
     */
    private $id;
    /**
     * Date de mise à jour formatée.
     *
     * @var mixed
     */
    private $maj;
    /**
     * Largeur de l'image.
     *
     * @var mixed
     */
    private $w;
    /**
     * Hauteur de l'image.
     *
     * @var mixed
     */
    private $h;
    
    /**
     * Construire un item de galerie.
     */
    public function __construct()
    {
    }
    
    /**
     * Exporter l'item au format JSON.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        
        return [
            "id"  => $this->getId(),
            "maj" => $this->getMaj(),
            "w"   => $this->getW(),
            "h"   => $this->getH(),
        ];
        
    }
    
    /**
     * Récupérer l'identifiant de l'item.
     *
     * @return mixed Identifiant.
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Définir l'identifiant de l'item.
     *
     * @param mixed $id Identifiant.
     *
     * @return ActuGalleryItem Instance courante.
     */
    public function setId($id): ActuGalleryItem
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * Récupérer la date de mise à jour.
     *
     * @return mixed Date formatée.
     */
    public function getMaj()
    {
        return $this->maj;
    }
    
    /**
     * Définir la date de mise à jour.
     *
     * @param mixed $maj Date brute.
     *
     * @return ActuGalleryItem Instance courante.
     */
    public function setMaj($maj): ActuGalleryItem
    {
        $this->maj = date("Y_m_d_H_i_s", strtotime($maj));
        
        return $this;
    }
    
    /**
     * Récupérer la largeur.
     *
     * @return mixed Largeur.
     */
    public function getW()
    {
        return $this->w;
    }
    
    /**
     * Définir la largeur.
     *
     * @param mixed $w Largeur.
     *
     * @return ActuGalleryItem Instance courante.
     */
    public function setW($w): ActuGalleryItem
    {
        $this->w = $w;
        
        return $this;
    }
    
    /**
     * Récupérer la hauteur.
     *
     * @return mixed Hauteur.
     */
    public function getH()
    {
        return $this->h;
    }
    
    /**
     * Définir la hauteur.
     *
     * @param mixed $h Hauteur.
     *
     * @return ActuGalleryItem Instance courante.
     */
    public function setH($h): ActuGalleryItem
    {
        $this->h = $h;
        
        return $this;
    }
    
    
}
