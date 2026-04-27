<?php
$head_title = $head_title ?? Config::get("HTTP.HEADERS.TITLE");
$head_description = $head_description ?? Config::get("HTTP.HEADERS.DESCRIPTION");
$color = [
        "LIGHT" => [
                "LOCAL" => "#129f14",
                "PROD"  => "#007fc2",
        ],
        "DARK"  => [
                "LOCAL" => "#094f09",
                "PROD"  => "#0e232f",
        ],
        "MENU"  => [
                "LOCAL" => "#186e18",
                "PROD"  => "#033b5b",
        ],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $head_title; ?></title>
    <meta name="description" content="<?= $head_description; ?>">
    <meta name="robots" content="noindex">
    <?= $this->renderCSS(); ?>
     <style>
        :root {
            --color-cua-blue: <?=$color["LIGHT"][ENV];?>;
            --color-cua-blue-dark: <?=$color["DARK"][ENV];?>;
            --color-cua-blue-hover: <?=$color["MENU"][ENV];?>;
        }
    </style>

    <link href="/assets/css/main.min.css?<?= Config::get("VERSION.ASSETS"); ?>" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/manual.css" type="text/css">
</head>
<body class="<?= Config::getJsConfig("curPage"); ?> <?= Utility::isMobile() ? "mobile" : null; ?>">
    <div class="wrapper">
        <nav class="navbar navbar-expand-md navbar-dark fixed-top ">
            <div class="container">
                <a class="navbar-brand" href="/"><img loading="lazy" src="/assets/img/logo_200.png" alt="logo nouveauprojet"> <span class="text hidden-sm">nouveauprojet</span></a>
                <button class="navbar-toggler" type="button"
                        data-toggle="collapse" data-target="#navbarCollapse"
                        aria-controls="navbarCollapse" aria-expanded="false"
                        aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <?= $this->render(VIEWS . "/layouts/default/menu.php"); ?>
            </div>
        </nav>
        <?php if (isset($h1)) { ?>
            <h1 style="display:none !important;"><?= $h1 ?></h1>
        <?php } ?>
        <main role="main">

            <?php if (isset($breadcrumb)) { ?>
            <div class="container-fluid breadcrumb-container">
                <div class="row">
                    <div class="container">
                        <?= $breadcrumb->output() ?>
                    </div>
                </div>
            </div>
<?php } ?>