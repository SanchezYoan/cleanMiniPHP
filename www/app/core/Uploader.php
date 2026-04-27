<?php

use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use ManagerException\UploadException;

/**
 * Gérer les uploads et suppressions de fichiers.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */
class Uploader
{
    /**
     * Mime types autorisés par catégorie.
     *
     * @var array<string, list<string>>
     */
    private static $allowedMIME = [
        "image" => ['image/jpeg', 'image/png', 'image/gif'],
        "csv"   => ['text/csv', 'application/vnd.ms-excel', 'text/plain'],
        "file"  => ['application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/pdf',
                    'application/zip',
                    'application/vnd.ms-powerpoint',
        ],
        
        "any" => ['image/jpeg', 'image/png', 'image/gif',
                  'application/vnd.google-earth.kml+xml',
                  'application/xml',
                  'text/xml',
                  'application/octet-stream',
                  'text/csv', 'application/vnd.ms-excel', 'text/plain',
                  'application/msword',
                  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                  'application/pdf',
                  'application/zip',
                  'application/vnd.ms-powerpoint',
        ],
    ];
    
    /**
     * Taille min/max autorisée (octets).
     * 1 KB = 1024 bytes, 1 MB = 1 048 576 bytes.
     *
     * @var array{0:int,1:int}
     */
    private static $fileSize = [100, 4097152];
    
    /**
     * Dimensions maximales autorisées (px).
     *
     * @var array{0:int,1:int}
     */
    private static $dimensions = [4000, 4000];
    
    /**
     * Erreurs de validation.
     *
     * @var list<string>
     */
    private static $errors = [];
    
    /***
     * Empêcher l'instanciation.
     */
    private function __construct()
    {
    }
    
    /**
     * Supprimer un fichier.
     *
     * Effets de bord : suppression du fichier sur disque.
     *
     * @param string $path Chemin du fichier.
     *
     * @return void
     *
     * @throws RuntimeException Si la suppression échoue.
     */
    public static function deleteFile(string $path): void
    {
        if (file_exists($path)) {
            Logger::notice(__METHOD__ . " unlink : $path");
            
            if (!unlink($path)) {
                throw new \RuntimeException("File " . $path . " couldn't be deleted");
            }
        }
    }
    
    /**
     * Créer un répertoire.
     *
     * Effets de bord : écriture sur le système de fichiers.
     *
     * @param string $dir Chemin du répertoire.
     *
     * @return string Chemin créé.
     *
     * @throws Exception Si la création échoue.
     */
    public static function createDirectory(string $dir)
    {
        
        $newDir = $dir;
        
        // create a directory if not exists
        if (!file_exists($newDir) && !is_dir($newDir)) {
            if (mkdir($newDir, 0755, true) === false) {
                throw new Exception("directory $newDir couldn't be created");
            }
        }
        
        return $newDir;
    }
    
    /**
     * Supprimer un répertoire.
     *
     * Effets de bord : suppression récursive sur disque.
     *
     * @param string $dir Chemin du répertoire.
     *
     * @return void
     *
     * @throws Exception Si la suppression échoue.
     */
    public static function deleteDir(string $dir)
    {
        if (!self::delTree($dir)) {
            throw new Exception("Directory: " . $dir . " couldn't be deleted");
        }
    }
    
    /**
     * Supprimer un répertoire récursivement.
     *
     * @param string $dir Chemin du répertoire.
     *
     * @return bool true si suppression OK.
     */
    private static function delTree(string $dir): bool
    {
        
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                
                if (is_dir("$dir/$file")) {
                    
                    self::delTree("$dir/$file");
                    
                } else {
                    Logger::notice(__METHOD__ . " unlink : $dir/$file");
                    
                    unlink("$dir/$file");
                }
            }
        }
        
        return rmdir($dir);
    }
    
    /**
     * Vérifier le type MIME autorisé.
     *
     * @param string $formTempFile Chemin du fichier temporaire.
     *
     * @return bool true si autorisé.
     */
    public static function authorisedFileType(string $formTempFile): bool
    {
        $mime = strtolower(mime_content_type($formTempFile));
        if (!in_array($mime, [
            "image/png",
            "image/jpg",
            "image/jpeg",
        ])) {
            Logger::notice("Mime type not supported : $mime");
            
            return false;
        }
        
        return true;
    }
    
    
    /**
     * Uploader le logo d'une entité.
     *
     * Effets de bord : écrit sur disque et redimensionne l'image.
     *
     * @param Entite $entite Entité cible.
     * @param array  $uFile  Fichier uploadé (structure $_FILES).
     *
     * @return string|false Chemin final ou false si échec.
     *
     * @throws UploadException      Si le fichier est invalide.
     * @throws ImageResizeException Si le redimensionnement échoue.
     */
    public static function uploadEntiteLogo(Entite $entite, array $uFile)
    {
        if (!self::authorisedFileType($uFile["tmp_name"])) {
            throw new UploadException("Type de fichier non reconnue. {$uFile['name']} n'est pas pris en charge. Image type jpg, jpeg ou png uniquement.", E_WARNING);
        }
        $basename = pathinfo($uFile['name'], PATHINFO_BASENAME);
        $tempPath = Entite::logoBasePath($entite->getToken());
        $tempFilePath  = $tempPath . DIRECTORY_SEPARATOR . "$basename";
    
        if (!is_dir($tempPath)) {
            if (!mkdir($tempPath, 0777, true)) {
                Logger::error("Impossible de créer le dossier $tempPath (mkdir failed)");
                return false;
            }
        }
        
        
        if (!move_uploaded_file($uFile['tmp_name'], $tempFilePath)) {
            Logger::critical("UPLOAD ERROR, File : " . var_export($uFile, true) . PHP_EOL . "Destination : $tempPath", __FILE__, __LINE__);
            return false;
        }
        
        $image     = new ImageResize($tempFilePath);
        
        $finalPath = Entite::logoBasePath($entite->getToken()) . "/logo.jpg";
        
        $image->resizeToBestFit(200, 200, true);
        $image->save($finalPath, IMAGETYPE_JPEG, null, null, [200, 200]);
        
        return $finalPath;
        
        
    }
    
    /**
     * Convertir un tableau $_FILES en liste d'images.
     *
     * @param array<int|string, mixed> $visuels_uploads Données $_FILES.
     * @param array<int, object>|null  $sortedFileNames Liste de fichiers ordonnés (optionnel).
     *
     * @return array<int, array<string, mixed>> Liste des fichiers normalisés.
     */
    public static function convertImagesUploadsToArrayImages($visuels_uploads, $sortedFileNames = null): array
    {
        $visuels_data = [];
        // Est-ce que nous avons des images dans les uploads
        if (!empty($visuels_uploads["tmp_name"])) {
            // On va créer un tableau d'images
            $nb_images = count($visuels_uploads["tmp_name"]);
            for ($i = 0; $i < $nb_images; $i++) {
                $image_data                                 = ['name'     => $visuels_uploads["name"][$i],
                                                               'type'     => $visuels_uploads["type"][$i],
                                                               'tmp_name' => $visuels_uploads["tmp_name"][$i],
                                                               'error'    => $visuels_uploads["error"][$i],
                                                               'size'     => $visuels_uploads["size"][$i],
                ];
                $visuels_data[$visuels_uploads["size"][$i]] = $image_data;
            }
        }
        $visuels = [];
        // Est-ce que nous avons des images ordonnées (peut-être aussi des suppressions)
        if ($sortedFileNames == null) {
            // Sinon on prend tout le monde dans l'ordre de l'upload
            foreach ($visuels_data as $data) {
                $visuels[] = $data;
            }
        } else if (count($sortedFileNames) > 0) {
            foreach ($sortedFileNames as $data) {
                $visuels[] = $visuels_data[$data->size];
            }
        }
        // On retourne la liste des images
        return $visuels;
    }
    
    /**
     * Valider un lot d'images uploadées.
     *
     * @param array<int, array<string, mixed>> $array_uploads Liste d'images.
     *
     * @return list<string> Messages d'erreur.
     */
    public static function validateArrayImagesUploads($array_uploads): array
    {
        $error_visuels_taille = [];
        foreach ($array_uploads as $visuel) {
            $validator = new Validation();
            if (!$validator->validate([
                                          "visuel" => [$visuel, "fileSize(200," . Config::getJsConfig("fileSizeOverflow") . ")|imageMinSize(200,200)|imageMaxSize(7200,7200)"],
                                      ])) {
                $visuel_name            = $visuel['name'];
                $error_visuels_taille[] = "Upload images : Image $visuel_name trop petite.";
            }
        }
        return $error_visuels_taille;
    }
    
    /**
     * Ajouter un lot d'images à une actualité.
     *
     * Effets de bord : suppression/écriture sur disque et en base.
     *
     * @param mixed                          $actu          Objet d'actualité.
     * @param array<int, array<string, mixed>> $array_uploads Liste d'images.
     *
     * @return bool true si toutes les images sont ajoutées.
     */
    public static function addArrayImagesUploads($actu, $array_uploads): bool
    {
        $ordre = 0;
        $actu->removeFiles();
        foreach ($array_uploads as $visuel) {
            // Pour chaque visuel, nous allons ajouter les images dans la base de données
            // l'actu concerné : $actu->getId();
            try {
                $finalPath = self::uploadActuImage($actu, $ordre, $visuel);
                if (!$finalPath) {
                    return false;
                }
                ++$ordre; // Image suivante
            } catch (Exception $ex) {
                Logger::error($ex);
            }
        }
        return true;
    }
    
}
