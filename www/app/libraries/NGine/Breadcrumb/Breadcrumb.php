<?php


namespace NGine\Breadcrumb;

/**
 * Gérer un fil d'Ariane sous forme d'items HTML.
 */
class Breadcrumb
{
    /**
     * Identifiant du fil d'Ariane.
     *
     * @var mixed
     */
    private $id;
    /**
     * Nom logique du fil d'Ariane.
     *
     * @var mixed
     */
    private $name;
    /**
     * @var Link[]
     */
    private $items;
    /**
     * Classe CSS appliquée au fil d'Ariane.
     *
     * @var mixed
     */
    private $class;
    
    /**
     * Initialiser un fil d'Ariane.
     *
     * @param string $name Nom affiché.
     */
    public function __construct(string $name)
    {
        $this->setName($name);
    }
    
    /**
     * Réinitialiser la liste des items.
     *
     * @return $this Instance courante.
     */
    public function reset()
    {
        $this->items = [];
        
        return $this;
    }
    
    /**
     * Ajouter plusieurs items à partir d'un tableau associatif.
     *
     * @param array<int, array<string, mixed>> $items Données des liens.
     *
     * @return Breadcrumb Instance courante.
     */
    public function addItems(array $items)
    {
        
        foreach ($items as $item) {
            $this->addItem((new Link())
                               ->setHref($item["href"] ?? "")
                               ->setIcon($item["icon"] ?? "")
                               ->setText($item["text"] ?? "")
                               ->setTarget($item["target"] ?? "")
                               ->setTitle($item["title"] ?? "")
                               ->setClass($item["class"] ?? "")
            );
        }
        
        return $this;
    }
    
    /**
     * Ajouter un item de fil d'Ariane.
     *
     * @param Link $link Item à ajouter.
     *
     * @return $this Instance courante.
     */
    public function addItem(Link $link)
    {
        $this->items[] = $link;
        
        return $this;
    }
    
    /**
     * Récupérer l'identifiant du fil d'Ariane.
     *
     * @return mixed Identifiant.
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Définir l'identifiant du fil d'Ariane.
     *
     * @param mixed $id Identifiant.
     *
     * @return $this Instance courante.
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * Retirer un item par son index.
     *
     * @param int $pos Index à supprimer.
     *
     * @return $this Instance courante.
     */
    public function removeItem(int $pos)
    {
        unset($this->items[$pos]);
        
        return $this;
    }
    
    /**
     * Générer le HTML du fil d'Ariane.
     *
     * @return string HTML du fil d'Ariane ou chaîne vide.
     */
    public function output()
    {
        $html_items = "";
        
        if (count($this->items) < 2) return "";
        
        
        foreach ($this->items as $item) {
            
            $href   = $item->getHref();
            $text   = $item->getText();
            $icon   = !empty($item->getIcon()) ? "<i class='ti ti-" . $item->getIcon() . " mr-0'></i>" : null;
            $target = $item->getTarget();
            $class  = $item->getClass();
            
            if (!empty($href)) {
                $html_items .= "
                                    <li class=\"breadcrumb-item $class\">
                                        <a class='color-primary' href=\"$href\" target=\"$target\">$icon $text</a>
                                    </li>";
            } else {
                $html_items .= "
                                    <li class=\"breadcrumb-item $class\">
                                        $icon $text
                                    </li>";
            }
            
        }
        
        $output = '<ol class="breadcrumb breadcrumb-arrows" aria-label="breadcrumbs">' . $html_items . '</ol>';
        
        return $output;
        
    }
    
    /**
     * Récupérer le nom du fil d'Ariane.
     *
     * @return mixed Nom logique.
     */
    public function getName()
    {
        return $this->name ?? "default";
    }
    
    /**
     * Définir le nom du fil d'Ariane.
     *
     * @param string $name Nom logique.
     *
     * @return Breadcrumb Instance courante.
     */
    public function setName(string $name)
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * Récupérer la classe CSS du fil d'Ariane.
     *
     * @return mixed Classe CSS.
     */
    public function getClass()
    {
        return $this->class;
    }
    
    /**
     * Définir la classe CSS du fil d'Ariane.
     *
     * @param string $class Classe CSS.
     *
     * @return $this Instance courante.
     */
    public function setClass(string $class)
    {
        $this->class = $class;
        
        return $this;
    }
}
