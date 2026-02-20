<?php
$head_title       = $head_title ?? Config::get("HTML.HEADERS.TITLE");
$head_description = $head_description ?? Config::get("HTML.HEADERS.DESCRIPTION");
$color            = [
    "LOCAL" => "#B0D7A1FF",
    "DEV"   => "#D7C6A1FF",
    "PROD"  => "#fff",
];
?>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-md-12">
                <div class="card card-md">
                    <div class="card-body">
                        <h2 class="text-center" style="font-size:4rem;">Error <?= $code ?? null; ?></h2>
                        <p class="text-center"><?= $message ?? null; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>