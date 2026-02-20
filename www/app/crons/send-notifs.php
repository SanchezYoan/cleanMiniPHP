<?php

require_once dirname(__FILE__, 3) . "/public/index.php";
if (!Config::get("FEATURES.NOTIFICATIONS")) {
    Logger::debug("CRON : Notifications désactivé sur ENV " . ENV);
    return;
}
try {
    $messages = Message::toNotify();
} catch (Exception $e) {
    Logger::error($e);
    return;
}
if (empty($messages)) {
    //Logger::debug("CRON : Aucun message a envoyer");
    return;
}

foreach ($messages as $message) {
    // On appel la méthode d'envoi de notification
    $message->sendNotification();
}