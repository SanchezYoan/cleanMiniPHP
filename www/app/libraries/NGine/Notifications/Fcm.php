<?php

namespace NGine\Notifications;

use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MessageData;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\MulticastSendReport;

/**
 * Envoie des notifications push via Firebase Cloud Messaging.
 */
class Fcm
{
    /**
     * Tokens des appareils à notifier.
     *
     * @var array<int, string>
     */
    private array   $deviceNotificationTokens = [];
    /**
     * Titre de la notification.
     */
    private ?string $title                    = null;
    /**
     * Corps de la notification.
     */
    private ?string $body                     = null;
    /**
     * Son associé à la notification.
     */
    private ?string $sound                    = null;
    /**
     * Données supplémentaires.
     *
     * @var array<string, mixed>
     */
    private array   $data                     = [];
    /**
     * Résultat brut d'envoi.
     */
    private object  $result;
    /**
     * Chemin vers la clé JSON Firebase.
     */
    private string  $firebaseJsonKeyPath      = BASE_DIR . "/app/config/nouveauprojet-9bf4b-98e0e8f96037.json";
    /**
     * Client de messagerie Firebase.
     */
    private \Kreait\Firebase\Contract\Messaging     $messaging;
    /**
     * Message FCM courant.
     */
    private \Kreait\Firebase\Messaging\CloudMessage $message;
    /**
     * Usine Firebase.
     */
    private \Kreait\Firebase\Factory $firebase;
    
    /**
     * Rapport de diffusion multicast.
     */
    private MulticastSendReport $report;
    
    /**
     * Journal d'exécution du dernier envoi.
     */
    private string $log = "";
    
    /**
     * Initialiser le client Firebase et la messagerie.
     *
     * @throws \Exception Si l'initialisation Firebase échoue.
     */
    public function __construct()
    {
        // Path to your Firebase service account key
        $this->firebase = (new Factory)
            ->withServiceAccount($this->firebaseJsonKeyPath);
        
        $this->messaging = $this->firebase->createMessaging();
    }
    
    
    /**
     * Envoyer une notification simple à un appareil.
     *
     * @param string $deviceToken Token optionnel si non fourni via setDeviceNotificationTokens.
     *
     * @throws MessagingException
     * @throws FirebaseException
     *
     * @return array Résultat renvoyé par l'API Firebase.
     */
    public function sendNotification(string $deviceToken = ""): array
    {
        if (empty($this->deviceNotificationTokens) && !empty($deviceToken)) {
            $this->deviceNotificationTokens[] = $deviceToken;
        }
        if (count($this->deviceNotificationTokens) === 0) {
            throw  new \RuntimeException("FCM Multicast Notifications : No device tokens provided.");
        }
        $notification                   = Notification::create($this->title, $this->body);
        $this->message                  = CloudMessage::withTarget('token', $this->deviceNotificationTokens[0])
                                                      ->withNotification($notification)->withData($this->data);
        $report                         = $this->messaging->send($this->message);
        $this->deviceNotificationTokens = [];
        return $report;
    }
    
    /**
     * Récupérer le log du dernier envoi.
     *
     * @return string Journal d'exécution.
     */
    public function getLog(): string
    {
        return $this->log;
    }
    
    /**
     * Envoyer une notification à plusieurs appareils.
     *
     * @param array<int, string> $deviceTokens Tokens optionnels si non fournis via setDeviceNotificationTokens.
     *
     * @throws MessagingException En cas d'échec Firebase.
     * @throws FirebaseException  En cas d'échec Firebase.
     * @throws \RuntimeException  Si aucun token n'est fourni.
     *
     * @return \Kreait\Firebase\Messaging\MulticastSendReport Rapport multicast.
     */
    public function multiCast(array $deviceTokens = []): \Kreait\Firebase\Messaging\MulticastSendReport
    {
        if(empty($this->deviceNotificationTokens) && !empty($deviceTokens)) {
            $this->deviceNotificationTokens = $deviceTokens;
        }
        if (count($this->deviceNotificationTokens) === 0) {
            throw  new \RuntimeException("FCM Multicast Notifications : No device tokens provided.");
        }
        $notification                   = Notification::create($this->title, $this->body);
        $this->message                  = CloudMessage::new()->withNotification($notification)->withData($this->data);
        $this->report = $this->messaging->sendMulticast($this->message, $this->deviceNotificationTokens);
        $this->log = "Success: " . $this->report->successes()->count() . "<br>" . "Fails: " . $this->report->failures()->count() . "<br>";
        
        if ($this->report->hasFailures()) {
            foreach ($this->report->failures()->getItems() as $failure) {
                $this->log .= "Invalid Device Token : " . $failure->target()->value() . "<br>";
            }
        }
        
        $this->deviceNotificationTokens = [];
        return $this->report;
    }
    
    /**
     * Définir la liste des tokens à notifier.
     *
     * @param array<int, string> $deviceNotificationTokens Tokens des appareils.
     *
     * @return Fcm Instance courante.
     */
    public function setDeviceNotificationTokens(array $deviceNotificationTokens): Fcm
    {
        $this->deviceNotificationTokens = $deviceNotificationTokens;
        return $this;
    }
    
    
    /**
     * Définir le titre de notification.
     *
     * @param string|null $title Titre à envoyer.
     *
     * @return Fcm Instance courante.
     */
    public function setTitle(?string $title): Fcm
    {
        $this->title = $title;
        return $this;
    }
    
    
    /**
     * Définir le corps de notification.
     *
     * @param string|null $body Corps à envoyer.
     *
     * @return Fcm Instance courante.
     */
    public function setBody(?string $body): Fcm
    {
        $this->body = $body;
        return $this;
    }
    
    
    /**
     * Définir le son de notification.
     *
     * @param string|null $sound Son à envoyer.
     *
     * @return Fcm Instance courante.
     */
    public function setSound(?string $sound): Fcm
    {
        $this->sound = $sound;
        return $this;
    }
    
    
    /**
     * Définir les données additionnelles.
     *
     * @param array<string, mixed> $data Payload data.
     *
     * @return Fcm Instance courante.
     */
    public function setData(array $data): Fcm
    {
        $this->data = $data;
        return $this;
    }
    
}
