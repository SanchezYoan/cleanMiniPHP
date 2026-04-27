<?php
$head_title       = $head_title ?? \NGine\Translate::get("nouveauprojet.head.title");
$head_description = $head_description ?? \NGine\Translate::get("nouveauprojet.head.description");
$color            = [
    "LOCAL" => "#B0D7A1FF",
    "DEV"   => "#D7C6A1FF",
    "PROD"  => "#fff",
];
?>
<!doctype html>

<html lang="<?= \NGine\Translate::currentLang(); ?>">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <?= isset($noindex) ? '<meta name="robots" content="noindex, nofollow">' : null; ?>
    <title><?= $head_title; ?></title>
    <?php if (!empty($this->html)) {
        echo $this->html->output();
    } ?>
    <meta name="description" content="<?= $head_description; ?>">
    <!-- CSS files -->
    <link href="/assets/node_modules/@tabler/core/dist/css/tabler.min.css" rel="stylesheet"/>
    <link href="/assets/node_modules/@tabler/core/dist/css/tabler-flags.min.css" rel="stylesheet"/>
    <link href="/assets/node_modules/@tabler/core/dist/css/tabler-payments.min.css" rel="stylesheet"/>
    <link href="/assets/node_modules/@tabler/core/dist/css/tabler-vendors.min.css" rel="stylesheet"/>
    
    <?= $this->renderCSS(); ?>
    <link href="/assets/css/main.min.css?<?= Config::get("VERSION.ASSETS"); ?>" rel="stylesheet">
    <style>
        :root {
            --color-env-navbar: <?=$color[ENV];?>;
        }
    </style>
    
    <!-- Tabler Core -->
    <script src="/assets/node_modules/@tabler/core/dist/js/tabler.min.js" defer></script>
</head>
<body class="<?= Config::getJsConfig("curPage"); ?> <?= Utility::isMobile() ? "mobile" : null; ?>">
<div class="page">
    <!-- Navbar -->
    <div class="sticky-top">
        <header class="navbar navbar-expand-md sticky-top d-print-none">
            <div class="container-xl">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu"
                        aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3 m-md-0 m-sm-auto m-xs-auto">
                    <a href="/" aria-description="nouveauprojet">
                        <img src="/assets/img/logo-site-generique.svg" width="110" height="32" alt="<?= $head_title; ?>" class="navbar-brand-image">
                    </a>
                </h1>
                <h2 style="font-size: 1.5rem;" class="page-title fst-italic justify-content-center color-primary">
                    <?= \NGine\Translate::get("nouveauprojet.home.slogan"); ?>
                </h2>
                <div class="navbar-nav flex-row order-md-last">
                    <div class="nav-item d-none d-md-flex me-3">
                        <div class="btn-list">
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <header class="navbar-expand-md">
            <div class="collapse navbar-collapse" id="navbar-menu">
                <div class="navbar">
                    <div class="container-xl">
                        <ul class="navbar-nav">
                            <li class="nav-item <?= Config::getJsConfig("curPage") === "accueil" ? "active" : null; ?>">
                                <a class="nav-link" href="/" role="button">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                             viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                             stroke-linecap="round" stroke-linejoin="round"><path stroke="none"
                                                                                                  d="M0 0h24v24H0z"
                                                                                                  fill="none"/><path
                                                    d="M5 12l-2 0l9 -9l9 9l-2 0"/><path
                                                    d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path
                                                    d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/></svg>
                                    </span>
                                    <span class="nav-link-title"><?= \NGine\Translate::get("nouveauprojet.menu.home"); ?></span>
                                </a>
                            </li>
                            <?php if (Session::isLoggedIn()): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="/dashboard" role="button">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M12 13m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path><path d="M13.45 11.55l2.05 -2.05"></path><path
                                                    d="M6.4 20a9 9 0 1 1 11.2 0z"></path></svg>
                                    </span>
                                        <span class="nav-link-title"><?= \NGine\Translate::get("nouveauprojet.menu.dashboard"); ?></span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="nav-item <?= Config::getJsConfig("curPage") === "login" ? "active" : null; ?>">
                                    <a class="nav-link" href="/login" role="button">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"></path><path
                                                    d="M20 12h-13l3 -3m0 6l-3 -3"></path></svg>
                                    </span>
                                        <span class="nav-link-title"><?= \NGine\Translate::get("nouveauprojet.menu.login"); ?></span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </header>
    </div>
    <div class="page-wrapper">