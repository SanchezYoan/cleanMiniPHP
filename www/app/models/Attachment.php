<?php

/**
 * Représenter une pièce jointe persistée.
 *
 * Gère les métadonnées en base et la manipulation du fichier associé sur disque.
 */
class Attachment extends Model
{
    /**
     * Racine de téléchargement (URL relative).
     */
    public const PATH_DOWNLOAD = "/";

    /**
     * Identifiant de l'attachment.
     */
    private ?int $id = null;

    /**
     * Identifiant du message lié.
     */
    private ?int $fk_messages_id = null;

    /**
     * Nom de fichier complet.
     */
    private ?string $filename = null;

    /**
     * Extension de fichier (sans point).
     */
    private ?string $extension = null;

    /**
     * Taille du fichier en octets.
     */
    private ?int $filesize = null;

    /**
     * Date de création en GMT.
     */
    private ?DateTime $gmt_create = null;
    
    
    /**
     * Initialiser l'attachment et charger l'enregistrement si l'id est fourni.
     *
     * @param int|null $id Identifiant à charger.
     */
    public function __construct(?int $id = null)
    {
        
        parent::__construct();
        $this->table = "attachments";
        if (null !== $id) {
            $this->reload($id);
        }
    }
    
    /**
     * Recharger l'attachment depuis la base.
     *
     * @param int $id Identifiant de l'attachment.
     *
     * @return static Instance courante.
     */
    private function reload(int $id): static
    {
        
        $db = Database::openConnection();
        $db->prepare("SELECT * FROM  attachments WHERE id = :id LIMIT 1")
           ->bindValue(":id", $id)
           ->execute();
        if ($db->countRows() === 1) {
            $data = $db->fetchObject();
            $this->setId($data->id)
                 ->setFkMessagesId($data->fk_messages_id)
                 ->setExtension($data->extension)
                 ->setGmtCreate(new DateTime($data->gmt_create))
                 ->setFilename($data->filename)
                 ->setFilesize($data->filesize);
            
        }
        
        return $this;
    }
    
    /**
     * Exporter les métadonnées utiles pour un usage mobile.
     *
     * @return array{id:int|null, filename:string|null, extension:string|null, filesize:int}
     */
    public function toArrayMobile(): array
    {
        $values = [
            "id"        => $this->id,
            "filename"  => $this->filename,
            "extension" => $this->extension,
            "filesize"  => (int)$this->filesize,
        ];
        return $values;
    }
    
    /**
     * Récupérer la date de création GMT.
     *
     * @return DateTime Date de création.
     */
    public function getGmtCreate(): DateTime
    {
        return $this->gmt_create;
    }
    
    /**
     * Définir la date de création GMT.
     *
     * @param DateTime $gmt_create Date à enregistrer.
     *
     * @return Attachment Instance courante.
     */
    public function setGmtCreate(DateTime $gmt_create): Attachment
    {
        $this->gmt_create = $gmt_create;
        return $this;
    }
    
    /**
     * Vérifier si le fichier local existe.
     *
     * @param string $messagePath Chemin du dossier du message.
     *
     * @return bool true si le fichier existe.
     */
    public function existLocalFile(string $messagePath): bool
    {
        $path_and_filename = $this->getLocalFile($messagePath);
        return file_exists($path_and_filename);
    }
    
    /**
     * Construire le chemin du fichier local de l'attachment.
     *
     * @param string $messagePath Chemin du dossier du message.
     *
     * @return string Chemin complet du fichier.
     */
    public function getLocalFile(string $messagePath): string
    {
        // En local, le fichier se nome <id_attachment>.att
        return $messagePath . '/' . $this->getId() . '.att';
    }
    
    /**
     * Récupérer l'identifiant de l'attachment.
     *
     * @return int|null Identifiant ou null.
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    
    /**
     * Définir l'identifiant de l'attachment.
     *
     * @param int $id Identifiant.
     *
     * @return Attachment Instance courante.
     */
    public function setId(int $id): Attachment
    {
        $this->id = $id;
        return $this;
    }
    
    /**
     * Sauvegarder l'attachment en base et sur disque.
     *
     * Écrit en base (transaction) puis copie le fichier temporaire vers le dossier
     * du message. En cas d'échec sur le disque, la transaction est annulée.
     *
     * @param string $messagePath Chemin du dossier du message.
     * @param string $tmpPath     Chemin du fichier temporaire.
     *
     * @return $this Instance rechargée après insertion.
     */
    public function save(string $messagePath, string $tmpPath): static
    {
        // Open database connection
        $db = Database::openConnection();
        $db->beginTransaction();
        
        $db->prepare("INSERT INTO attachments (fk_messages_id, filename, extension, filesize) VALUES (:fk_messages_id, :filename, :extension, :filesize)")
           ->bindValue(":fk_messages_id", $this->getFkMessagesId())
           ->bindValue(":filename", $this->getFilename())
           ->bindValue(":extension", $this->getExtension())
           ->bindValue(":filesize", $this->getFilesize())
           ->execute();
        // Récupérer l'id
        $this->setId($db->lastInsertedId());
        
        try {
            // Enregistrement sur le disque de l'attachment au path du message
            $inputFile  = fopen($tmpPath, 'rb');
            $outputFile = fopen($messagePath . '/' . $this->getId() . '.att', 'wb');
            
            if ($inputFile && $outputFile) {
                while (!feof($inputFile)) {
                    fwrite($outputFile, fread($inputFile, 8192)); // Read/write 8KB chunks
                }
                fclose($inputFile);
                fclose($outputFile);
            } else {
                throw new Exception("Failed to open file streams.");
            }
        } catch (Exception $e) {
            $db->rollBack();
            Logger::error($e);
            $this->addError("Error while trying to save the file on disk");
            return $this;
        }
        
        $db->commit();
        
        return $this->reload($this->getId());
    }
    
    /**
     * Récupérer l'identifiant du message lié.
     *
     * @return int Identifiant du message.
     */
    public function getFkMessagesId(): int
    {
        return $this->fk_messages_id;
    }
    
    /**
     * Définir l'identifiant du message lié.
     *
     * @param int $fk_messages_id Identifiant du message.
     *
     * @return Attachment Instance courante.
     */
    public function setFkMessagesId(int $fk_messages_id): Attachment
    {
        $this->fk_messages_id = $fk_messages_id;
        return $this;
    }
    
    /**
     * Récupérer le nom du fichier.
     *
     * @return string Nom du fichier.
     */
    public function getFilename(): string
    {
        return $this->filename;
    }
    
    /**
     * Définir le nom du fichier.
     *
     * @param string $filename Nom du fichier.
     *
     * @return Attachment Instance courante.
     */
    public function setFilename(string $filename): Attachment
    {
        $this->filename = $filename;
        return $this;
    }
    
    /**
     * Récupérer l'extension du fichier.
     *
     * @return string Extension.
     */
    public function getExtension(): string
    {
        return $this->extension;
    }
    
    /**
     * Définir l'extension du fichier.
     *
     * @param string $extension Extension.
     *
     * @return Attachment Instance courante.
     */
    public function setExtension(string $extension): Attachment
    {
        $this->extension = $extension;
        return $this;
    }
    
    /**
     * Récupérer la taille du fichier.
     *
     * @return int Taille en octets.
     */
    public function getFilesize(): int
    {
        return $this->filesize;
    }
    
    /**
     * Définir la taille du fichier.
     *
     * @param int $filesize Taille en octets.
     *
     * @return Attachment Instance courante.
     */
    public function setFilesize(int $filesize): Attachment
    {
        $this->filesize = $filesize;
        return $this;
    }
    
    /**
     * Récupérer le nom de fichier sans extension.
     *
     * @return string Nom de base.
     */
    public function getFilenameWithoutExtension(): string
    {
        // Le nom du fichier contient l'extension, on sépare juste à partir de '.' et on garde que le début
        return explode('.', $this->getFilename())[0];
    }
    
    /**
     * Supprimer l'attachment en base et sur disque.
     *
     * Effets de bord : suppression en base et suppression du fichier local.
     *
     * @param string $messagePath Chemin du dossier du message.
     *
     * @return static Instance courante.
     */
    public function delete(string $messagePath): static
    {
        // Open database connection
        $db = Database::openConnection();
        // Suppression de l'attachment
        $db->prepare("DELETE FROM messages WHERE id = :id")
           ->bindValue(":id", $this->getId())
           ->execute();
        // On récupère le path de l'attachment
        // - on récupère le path du dossier du message via $message->getPath() car depuis l'objet attachment on ne peut pas le connaitre du au chemin pour les messages privés ou non
        // - Puis on construit le path de l'attachment depuis le path du message + id de l'attachment + ".att"
        $path = $messagePath . '/' . $this->getId() . '.att';
        // Logger
        Logger::debug("Message->delete : Suppression du message privé {$this->getId()}. Removing folder {$path}");
        // Suppression du dossier via l'utilitaire
        Utility::removeFile($path);
        // Retour
        return $this;
    }
    
    /**
     * Construire l'URL de téléchargement de l'attachment.
     *
     * @return string URL relative de téléchargement.
     */
    public function getUrlDownload(): string
    {
        // On récupère le message lié à l'attachment via le fk_message_id
        $message = new Message($this->getFkMessagesId());
        // On récupère le channel via le fk_channel_id de l'objet message
        $channel = new Channel($message->getFkChannelsId());
        // On récupère l'éditeur via le fk_editor_id de l'objet message
        $editor = new Editor($message->getFkEditorsId());
        // On construit l'url
        return '/data/attachment/' . $editor->getShortLink() . '/' . $channel->getShortLink() . '/' . $message->getId() . '-' . $this->getId();
    }
    
}
