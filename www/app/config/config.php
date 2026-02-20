<?php

return [
    "VERSION" => [
        "APP" => "1.0",
        "ASSETS" => "1.0.1",
    ],
    "ALLOWED_IP" => [
        "DIGITAL" => [
            "2.4.116.254",
            "193.176.65.148",
            "91.165.16.207",
            "localhost",
            "127.0.0.1",
            "92.167.85.15",
        ],
        "CLIENTS" => [
            "86.220.22.30",
        ],
    ],
    "DEBUG" => [
        "SQL" => true,
    ],
    "FEATURES" => [
        // Activer/désactiver des fonctionnnalités
        "LOGGER" => [
            "DEBUG" => ["WRITE_TXT" => true, "WRITE_DB" => true, "SEND_MAIL" => false],
            "NOTICE" => ["WRITE_TXT" => true, "WRITE_DB" => true, "SEND_MAIL" => false],
            "WARNING" => ["WRITE_TXT" => true, "WRITE_DB" => true, "SEND_MAIL" => false],
            "ERROR" => ["WRITE_TXT" => true, "WRITE_DB" => true, "SEND_MAIL" => ENV !== "LOCAL"],
            "SECURITY" => ["WRITE_TXT" => true, "WRITE_DB" => true, "SEND_MAIL" => ENV !== "LOCAL"],
            "CRITICAL" => ["WRITE_TXT" => true, "WRITE_DB" => true, "SEND_MAIL" => ENV !== "LOCAL"],
        ],
        "REDIRECTION" => [
            "EMAIL" => ENV === "PROD",
        ],
        "NOTIFICATIONS" => in_array(ENV, ["PROD", "DEV"]),
    ],
    "DEFAULT_ACCOUNT" => [
        "EMAIL" => "technique@ngine-innovation.com",
    ],
    "APP" => [
        "NAME" => "NouveauProjet",
    ],
    "LANG" => "FR",
    "DB" => [

        "PROD" => [
            "HOST" => "127.0.0.1",
            "NAME" => "",
            "USER" => "",
            "PASS" => "",
            'CHARSET' => 'utf8mb4',
        ],
        "DEV" => [
            "HOST" => "127.0.0.1",
            "NAME" => "",
            "USER" => "",
            "PASS" => "",
            'CHARSET' => 'utf8mb4',
        ],
        "LOCAL" => [
            "HOST" => "127.0.0.1",
            "NAME" => "lifeforge",
            "USER" => "root",
            "PASS" => "root",
            "PORT" => "3306",
            'CHARSET' => 'utf8mb4',
        ],

    ],
    "API" => [
        "GOOGLE" => [
            "MAPS" => [
                "API_KEY" => "AIzaSyDz2eDmjSuImpITrAb-qbOO5NL6Y0BnKYk",
            ],
        ],
    ],
    "csrfToken" => Csrf::token(),
    "COOKIE_LIFETIME" => (60 * 60 * 24), // 24h cookie auth (remember me)
    "SESSION_TIMEOUT" => (60 * 60 * 24), // 24h cookie de session php
    "REMEMBER_ME_COOKIE_LIFETIME" => (60 * 60 * 24 * 2), // 48h cookie de session php
    "COOKIE_DOMAIN" => DOMAIN,
    "COOKIE_PATH" => '/',
    "COOKIE_SECURE" => true,
    "COOKIE_HTTP" => true,
    "COOKIE_SECRET_KEY" => "a9&11-UO^!a{to+r5@g##l]#kQ56+QS%",
    "ENCRYPTION" => [
        "KEY" => "S¥‹a0Ç&@!$222Êìpmn473%&",
        "HMAC_SALT" => "hHà#n7^Zp8%0Qfd9K4ftd$11#ab",
        "HASH_KEY" => "f0JPOp666cR",
        'HASH_PEPPER' => '&22-D_O^7Y!$556ÊìhIM_p007cW',
        "HASH_COST_FACTOR" => "10",
        "DB_ENCRYPT_KEY" => "def000005a0989ae05f41fecd6c78775524402ec3b11922498d2f5f8fa4f01e939b34fff63cd542c55c7816935dd04eb9e4953bb984f87aa17c48dc249579efba6f6fcd0",
    ],
    "SECURITY" => [
        "RESET_TOKEN" => [
            "TTL_MINUTES" => 60,
            "HMAC_SECRET" => 'iipFz3ef%K!pKh8"{DR^&@V9*m(0d_M',
        ],
    ],
    "EMAILS" => [
        "SETTINGS" => [
            "NO_REPLY" => "no-reply@" . DOMAIN,
        ],
        "ERRORS" => [
            "DEVS" => "dev@digitalfit.tech",
        ],
    ],
    "HTML" => [
        "HEADERS" => [
            "TITLE" => ENV === "PROD" ? "NouveauProjet" : "NouveauProjet : " . ENV,
            "DESCRIPTION" => "NouveauProjet : MON Nouveau projet !",
            "TAGS" => [],
            "AUTHOR" => "DigitalFit",
        ],
    ],
    "GOOGLE_AUTHENTIFICATOR" => [
        "LOCKED_IN_MINUTES" => 60, // Durée pendant laquelle on bloque le compte si x erreurs en moins d'1h
        "TWOFA_PENDING_TTL" => 300, // Durée pendant laquelle on stocke le token de 2FA (si on depasse on supprime, l'utilisateur devra refaire la premiere étape d'authentification)
        "ATTEMPTS" => 3, // tentatives autorisées
        "SECURITY" => [
            "WRITE_DB" => true,
            "SEND_EMAIL" => [
                "securite@ngine-innovation.com",
            ],
        ],
        "IS_ACTIVE" =>
        [
            "USER" => true,
            "USERSU"=> true,
            "ADMIN" => true,
            "ADMINSU" => true,
        ]
    ],

];