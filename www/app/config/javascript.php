<?php

/**
 * This file contains javascript configuration for the application.
 * It will be used by app/core/Config.php
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

return [

    /* public root used in ajax calls and redirection from client-side */
    "root" => "/",
    /* max file size, this is important to avoid overflow in files with big size */
    "fileSizeOverflow" => 3072000, // 3Mo
    "dateFormat" => 'DD/MM/YYYY HH:mm',
    "env" => ENV,
    "host" => "https://" . DOMAIN,
    "apiHost" => "https://" . DOMAIN_API,
    "domain" => DOMAIN,
    "cookieconsent" => [
        "title1" => '<span style="font-size:4em;">🍪</span>&nbsp;Nous utilisons des cookies !',
        "title2" => '<h2>🍪&nbsp;nouveauprojet</h2>',
        "description1" => '<p>Nous utilisons des cookies à des fins de mesures d\'audience (Google Analytics) ainsi que pour mémoriser vos préférences.</p>
                            <p>Aucune donnée n\'est utilisée pour tracer votre activité ou partager des informations avec d\'autres sites.</p>',
        "description2" => 'Nous utilisons des cookies pour vous assurer une expérience agréable et simple sur Push my news. Vous avez le choix d\'accepter ou pas certaines catégories de cookies non indispensables. Pour plus de détails sur les cookies et la gestion de la vie privée, consulter nos  <a href="/documents/mentions-legales" class="cc-link">mentions légales</a>.',

    ],
    "ckEditorItems" => ["undo", "|", "heading", "|", "fontColor", "bold", "italic", "link", "alignment", "|", "bulletedList", "insertTable", "imageInsert", "sourceEditing"],
    // "xdebug"           => "?XDEBUG_SESSION_START=14875",
    "fileTypes" => [
        "image" => "image/jpg,image/jpeg,image/png",
        "document" => ".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,.docx,.xls,.xlsx,.pdf,application/pdf",
        "archive" => "zip,rar,gzip,7z",
        "video" => "video/mp4,video/avi,video/mkv,video/mpeg,video/wmv",
        "accept" => ".zip,.rar,.gzip,.7z,.doc,.docx,.pptx,.ppt,.xls,.xlsx,.pdf,.png,.jpg,.jpeg,.gif,.mp4,.wmv,.flv,.avi,.mpeg",
        "mimes" => "application/vnd.ms-excel,application/vnd.ms-powerpoint,application/x-zip-compressed,application/vnd.openxmlformats-officedocument.presentationml.presentation,.pptx,.ppt,application/zip,image/jpg,image/*,image/jpeg,image/png,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/pdf,video/mp4,video/avi,video/mkv,video/mpeg,video/wmv,video/*",
    ],
    "fileSizeOverflow" => 209715200, // o, bytes = 200MB
    "diskSpaceAvailable" => 0,
    "lang" => [
        "close" => \NGine\Translate::get("nouveauprojet.form.close"),
        "admin" => [
            "new_message" => \NGine\Translate::get("nouveauprojet.dashboard.form.message.new"),
        ],
        "profil" => [
            "error" => [
                "geoloc" => [
                    "pays" => \NGine\Translate::get("nouveauprojet.error.profile.geo.pays"),
                    "ville" => \NGine\Translate::get("nouveauprojet.error.profile.geo.ville"),
                    "fallback" => \NGine\Translate::get("nouveauprojet.error.profile.geo.fallback"),
                    "maps" => \NGine\Translate::get("nouveauprojet.error.profile.geo.maps"),
                ],
                "horaires" => [
                    "add" => \NGine\Translate::get("nouveauprojet.error.profile.geo.maps"),
                ],
            ],
        ],
        "attachments" => [
            "error" => [
                "default" => \NGine\Translate::get("nouveauprojet.dashboard.form.message.attachment.default"),
                "fallback" => \NGine\Translate::get("nouveauprojet.dashboard.form.message.attachment.fallback"),
                "tooBig" => \NGine\Translate::get("nouveauprojet.dashboard.form.message.attachment.tooBig"),
                "invalid" => \NGine\Translate::get("nouveauprojet.dashboard.form.message.attachment.invalid"),
                "cancel" => \NGine\Translate::get("nouveauprojet.dashboard.form.message.attachment.cancel"),
                "cancelConfirmation" => \NGine\Translate::get("nouveauprojet.dashboard.form.message.attachment.cancelConfirmation"),
                "removeFileButton" => \NGine\Translate::get("nouveauprojet.dashboard.form.message.attachment.removeFile"),
                "maxFilesExceeded" => \NGine\Translate::get("nouveauprojet.dashboard.form.message.attachment.maxFilesExceeded"),
                "responseError" => \NGine\Translate::get("nouveauprojet.dashboard.form.message.attachment.responseError"),
            ],
        ],
    ],
];