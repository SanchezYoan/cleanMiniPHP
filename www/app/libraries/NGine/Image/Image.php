<?php

namespace NGine\Image;

use DateTime;
use \Logger;

/**
 * Fournit des métadonnées sur une image locale.
 */
class Image
{
    /**
     * Largeur de l'image.
     */
    private int $width;
    /**
     * Hauteur de l'image.
     */
    private int $height;
    /**
     * Extension du fichier.
     */
    private string $extension;
    /**
     * Chemin complet du fichier.
     */
    private string $path;
    /**
     * URL publique éventuelle.
     */
    private string $url;
    /**
     * Date de dernière modification.
     */
    private DateTime $dateDerniereModification;
    /**
     * Indique si le fichier est introuvable.
     */
    private bool $missing = false;
    
    /**
     * Chemin par défaut pour image absente.
     */
    public const NO_IMAGE        = '/no_image.png';
    
    /**
     * Initialiser l'image à partir d'un chemin complet.
     *
     * @param string $fullPath Chemin complet vers l'image.
     */
    public function __construct(string $fullPath)
    {
        
        if (file_exists($fullPath)) {
            $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
            [$width, $height] = getimagesize($fullPath);
            
            $this->setPath($fullPath)
                 ->setExtension($extension)
                 ->setHeight($height)
                 ->setWidth($width);
        } else {
            $this->missing = true;
            Logger::notice("File not found : $fullPath");
        }
        
    }

#region setters
    
    /**
     * Récupérer la largeur de l'image.
     *
     * @return int Largeur en pixels.
     */
    public function getWidth(): int
    {
        return $this->width;
    }
    
    /**
     * Définir la largeur de l'image.
     *
     * @param int $width Largeur en pixels.
     *
     * @return Image Instance courante.
     */
    public function setWidth(int $width): Image
    {
        $this->width = $width;
        
        return $this;
    }
    
    /**
     * Récupérer la hauteur de l'image.
     *
     * @return int Hauteur en pixels.
     */
    public function getHeight(): int
    {
        return $this->height;
    }
    
    /**
     * Définir la hauteur de l'image.
     *
     * @param int $height Hauteur en pixels.
     *
     * @return Image Instance courante.
     */
    public function setHeight(int $height): Image
    {
        $this->height = $height;
        
        return $this;
    }
    
    /**
     * Récupérer l'extension du fichier.
     *
     * @return string Extension.
     */
    public function getExtension()
    {
        return $this->extension;
    }
    
    /**
     * Définir l'extension du fichier.
     *
     * @param mixed $extension Extension.
     *
     * @return Image Instance courante.
     */
    public function setExtension($extension): Image
    {
        $this->extension = $extension;
        
        return $this;
    }
    
    /**
     * Récupérer le chemin du fichier.
     *
     * @return string Chemin.
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * Définir le chemin du fichier.
     *
     * @param mixed $path Chemin.
     *
     * @return Image Instance courante.
     */
    public function setPath($path): Image
    {
        $this->path = $path;
        
        return $this;
    }
    
    /**
     * Récupérer l'URL de l'image.
     *
     * @return string URL.
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * Définir l'URL de l'image.
     *
     * @param mixed $url URL.
     *
     * @return Image Instance courante.
     */
    public function setUrl($url): Image
    {
        $this->url = $url;
        
        return $this;
    }
    
    /**
     * Récupérer la date de dernière modification.
     *
     * @return DateTime Date de modification.
     */
    public function getDateDerniereModification()
    {
        return $this->dateDerniereModification;
    }
    
    /**
     * Définir la date de dernière modification.
     *
     * @param mixed $dateDerniereModification Date de modification.
     *
     * @return Image Instance courante.
     */
    public function setDateDerniereModification($dateDerniereModification): Image
    {
        $this->dateDerniereModification = $dateDerniereModification;
        
        return $this;
    }


#endregion
    
    /**
     * Préparer l'objet pour un export JSON.
     *
     * @return array<string, mixed> Données sérialisées.
     */
    public function jsonSerialize(): array
    {
        return [
            "width"                    => $this->getWidth(),
            "height"                   => $this->getHeight(),
            "extension"                => $this->getExtension(),
            "path"                     => $this->getPath(),
            "url"                      => $this->getUrl(),
            "dateDerniereModification" => $this->getDateDerniereModification(),
        ];
    }
    
    /**
     * Indiquer si le fichier est manquant.
     *
     * @return bool true si l'image est manquante, false sinon.
     */
    public function isMissing(): bool
    {
        return $this->missing;
    }
    
    
}
