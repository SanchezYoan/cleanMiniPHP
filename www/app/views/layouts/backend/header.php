<?php
$head_title = $head_title ?? \NGine\Translate::get("nouveauprojet.head.title");
$head_description = $head_description ?? \NGine\Translate::get("nouveauprojet.head.description");
$color = [
    "LOCAL" => "#B0D7A1FF",
    "DEV" => "#D7C6A1FF",
    "PROD" => "#fff",
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
    
    <!-- Tabler Core -->
    <script src="/assets/node_modules/@tabler/core/dist/js/tabler.min.js" defer></script>
    <link href="/assets/css/main.min.css?<?= Config::get("VERSION.ASSETS"); ?>" rel="stylesheet">
    <style>
        :root {
            --color-env-navbar: <?=$color["PROD"];?>;
        }
    </style>
</head>
<body class="<?= Config::getJsConfig("curPage"); ?><?= Utility::isMobile() ? " mobile" : null; ?>">

<div class="page">
    <!-- Sidebar -->
    <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="light">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
                    aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3 m-md-0 m-sm-auto m-xs-auto">
                <a href="/" aria-description="NouveauProjet">
                    <img src="/assets/img/logo-site-generique.svg" width="110" height="32" alt="<?= $head_title; ?>"
                         class="navbar-brand-image">
                </a>
            </h1>
            <script>
                const themeStorageKey = "tablerTheme";
                const defaultTheme = "light";
                
                const params = new URLSearchParams(window.location.search);
                const urlTheme = params.get("theme");
                
                const selectedTheme =
                    (urlTheme === "dark" || urlTheme === "light")
                        ? urlTheme
                        : (localStorage.getItem(themeStorageKey) || defaultTheme);
                
                localStorage.setItem(themeStorageKey, selectedTheme);
                
                document.documentElement.setAttribute("data-bs-theme", selectedTheme);
                
                document.body.setAttribute("data-bs-theme", selectedTheme);
                
                const aside = document.querySelector("aside");
                if (aside) aside.setAttribute("data-bs-theme", selectedTheme);
                
                const brand = document.querySelector(".navbar-brand-image");
                if (brand) {
                    brand.setAttribute(
                        "src",
                        selectedTheme === "dark"
                            ? "/assets/img/logo-site-generique-white.svg"
                            : "/assets/img/logo-site-generique.svg"
                    );
                }
                /*Pour éviter que l’URL reste en ?theme=light / ?theme=dark après clic*/
                if (urlTheme) {
                    params.delete("theme");
                    history.replaceState({}, "", location.pathname + (params.toString() ? "?" + params : "") + location.hash);
                }
            </script>
            <div class="navbar-nav flex-row d-lg-none">
                <div class="d-flex">
                    <a href="?theme=dark" class="nav-link px-0 hide-theme-dark" data-bs-toggle="tooltip"
                       data-bs-placement="bottom"
                       aria-label="<?= \NGine\Translate::get("nouveauprojet.theme.title.enableDark"); ?>"
                       data-bs-original-title="<?= \NGine\Translate::get("nouveauprojet.theme.title.enableDark"); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"
                             stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                             stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z"></path>
                        </svg>
                    </a>
                    <a href="?theme=light" class="nav-link px-0 hide-theme-light" data-bs-toggle="tooltip"
                       data-bs-placement="bottom"
                       aria-label="<?= \NGine\Translate::get("nouveauprojet.theme.title.enableLight"); ?>"
                       data-bs-original-title="<?= \NGine\Translate::get("nouveauprojet.theme.title.enableLight"); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"
                             stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                             stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                            <path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7"></path>
                        </svg>
                    </a>
                </div>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown"
                       aria-label="Open user menu">
                        <span class="avatar avatar-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24"
                                     viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                     stroke-linecap="round" stroke-linejoin="round"><path stroke="none"
                                                                                          d="M0 0h24v24H0z"
                                                                                          fill="none"></path><path
                                            d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"></path><path
                                            d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path></svg>
                            </span>
                        <div class="d-block ps-2">
                            <div><?= $this->admin->getLogin(); ?></div>
                            <div class="mt-1 small text-secondary"><?= $this->admin->getLevel(); ?></div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <?php if (false): ?>
                            <a href="#" class="dropdown-item">Status</a>
                            <a href="./profile.html" class="dropdown-item">Profile</a>
                            <a href="#" class="dropdown-item">Feedback</a>
                            <div class="dropdown-divider"></div>
                            <a href="./settings.html" class="dropdown-item">Settings</a>
                        <?php endif; ?>
                        <a href="/dashboard/account"
                           class="dropdown-item"><?= \NGine\Translate::get("nouveauprojet.menu.account"); ?></a>
                        <a href="/logout"
                           class="dropdown-item"><?= \NGine\Translate::get("nouveauprojet.menu.logout"); ?></a>
                    </div>
                </div>
            </div>
            <div class="navbar-collapse collapse" id="sidebar-menu" style="">
                <ul class="navbar-nav pt-lg-3 pb-lg-3">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard">
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
                            <span class="nav-link-title"><?= \NGine\Translate::get("nouveauprojet.menu.dashboard"); ?></span>
                        </a>
                    </li>

                    <!-- Titre pour les admins SU -->
                    <?php if ($this->admin->getLevel() === User::ADMINSU): ?>
                        <li class="nav-item mt-4 ps-2 border-top">
                            <span class="fw-bold"><?= \NGine\Translate::get("nouveauprojet.menu.category.admin"); ?></span>
                        </li>
                    <?php endif; ?>
                    
                    
                    
                    <!-- contenu pour les admins SU -->
                    <?php if ($this->admin->getLevel() === User::ADMINSU): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard/accounts">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                       <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-user-cog"><path
                                                   stroke="none" d="M0 0h24v24H0z" fill="none"/><path
                                                   d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path
                                                   d="M6 21v-2a4 4 0 0 1 4 -4h2.5"/><path
                                                   d="M19.001 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path
                                                   d="M19.001 15.5v1.5"/><path d="M19.001 21v1.5"/><path
                                                   d="M22.032 17.25l-1.299 .75"/><path d="M17.27 20l-1.3 .75"/><path
                                                   d="M15.97 17.25l1.3 .75"/><path d="M20.733 20l1.3 .75"/></svg>
                                    </span>
                                <span class="nav-link-title"><?= \NGine\Translate::get("nouveauprojet.menu.admin.accounts"); ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard/blockedaccounts">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                       <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-lock"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path
                                                   d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z"/><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0"/><path d="M8 11v-4a4 4 0 1 1 8 0v4"/></svg>
                                    </span>
                                <span class="nav-link-title"><?= \NGine\Translate::get("nouveauprojet.menu.admin.blockedaccounts"); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($this->admin->getLevel() === User::ADMINSU): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard/logs">
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                 stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-brand-tripadvisor">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M6.5 13.5m-1.5 0a1.5 1.5 0 1 0 3 0a1.5 1.5 0 1 0 -3 0"/>
                                                <path d="M17.5 13.5m-1.5 0a1.5 1.5 0 1 0 3 0a1.5 1.5 0 1 0 -3 0"/>
                                                <path d="M17.5 9a4.5 4.5 0 1 0 3.5 1.671l1 -1.671h-4.5z"/>
                                                <path d="M6.5 9a4.5 4.5 0 1 1 -3.5 1.671l-1 -1.671h4.5z"/><path d="M10.5 15.5l1.5 2l1.5 -2"/>
                                                <path d="M9 6.75c2 -.667 4 -.667 6 0"/>
                                            </svg>
                                        </span>
                                <span class="nav-link-title"><?= \NGine\Translate::get("nouveauprojet.menu.admin.menu.logs"); ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard/monitoring">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round"
                                             class="icon icon-tabler icons-tabler-outline icon-tabler-heart-rate-monitor"><path
                                                    stroke="none" d="M0 0h24v24H0z" fill="none"/><path
                                                    d="M3 4m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z"/><path
                                                    d="M7 20h10"/><path d="M9 16v4"/><path d="M15 16v4"/><path
                                                    d="M7 10h2l2 3l2 -6l1 3h3"/></svg>
                                    </span>
                                <span class="nav-link-title">Monitoring</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    

                    <li class="nav-item mt-auto">
                        <span class="nav-link text-muted fst-italic"><?= \NGine\Translate::get("nouveauprojet.version") . 'v' . Config::get("VERSION.APP"); ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </aside>
    <!-- Navbar -->
    <header class="navbar navbar-expand-md d-none d-lg-flex d-print-none">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu"
                    aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="navbar-nav flex-row order-md-last">
                <div class="d-flex">
                    <a href="?theme=dark" class="nav-link px-0 hide-theme-dark" data-bs-toggle="tooltip"
                       data-bs-placement="bottom"
                       aria-label="<?= \NGine\Translate::get("nouveauprojet.theme.title.enableDark"); ?>"
                       data-bs-original-title="<?= \NGine\Translate::get("nouveauprojet.theme.title.enableDark"); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"
                             stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                             stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z"></path>
                        </svg>
                    </a>
                    <a href="?theme=light" class="nav-link px-0 hide-theme-light" data-bs-toggle="tooltip"
                       data-bs-placement="bottom"
                       aria-label="<?= \NGine\Translate::get("nouveauprojet.theme.title.enableLight"); ?>"
                       data-bs-original-title="<?= \NGine\Translate::get("nouveauprojet.theme.title.enableLight"); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"
                             stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                             stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                            <path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7"></path>
                        </svg>
                    </a>
                </div>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown"
                       aria-label="Open user menu">
                        <span class="avatar avatar-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24"
                                     viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                     stroke-linecap="round" stroke-linejoin="round"><path stroke="none"
                                                                                          d="M0 0h24v24H0z"
                                                                                          fill="none"></path><path
                                            d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"></path><path
                                            d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path></svg>
                            </span>
                        <div class="d-none d-xl-block ps-2">
                            <div><?= $this->admin->getLogin(); ?></div>
                            <div class="mt-1 small text-secondary"><?= $this->admin->getLevel(); ?></div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <?php if (false): ?>
                            <a href="#" class="dropdown-item">Status</a>
                            <a href="./profile.html" class="dropdown-item">Profile</a>
                            <a href="#" class="dropdown-item">Feedback</a>
                            <div class="dropdown-divider"></div>
                            <a href="./settings.html" class="dropdown-item">Settings</a>
                        <?php endif; ?>
                        <a href="/dashboard/account"
                           class="dropdown-item"><?= \NGine\Translate::get("nouveauprojet.menu.account"); ?></a>
                        <a href="/logout"
                           class="dropdown-item"><?= \NGine\Translate::get("nouveauprojet.menu.logout"); ?></a>
                    </div>
                </div>
            </div>
            <div class="collapse navbar-collapse" id="navbar-menu">
                <h2 class="m-0"><?= \NGine\Translate::get("nouveauprojet.home.slogan"); ?></h2>
            </div>
        </div>
    </header>
    <div class="page-wrapper">