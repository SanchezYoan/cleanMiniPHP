<?php
/**
 * @var $countToday        array
 */
$lang = $this->controller->lang;
?>
<h2>Aujourd'hui dans les logs</h2>
<div class="row">
    <div class="col-md-6"><b>NOTICE</b></div>
    <div class="col-md-6 text-right"><?= $countToday["NOTICE"] ?? 0; ?></div>
    <div class="col-md-6"><b>DEBUG</b></div>
    <div class="col-md-6 text-right"><?= $countToday["DEBUG"] ?? 0; ?></div>
    <div class="col-md-6"><b>WARNING</b></div>
    <div class="col-md-6 text-right"><?= $countToday["WARNING"] ?? 0; ?></div>
    <div class="col-md-6"><b>ERROR</b></div>
    <div class="col-md-6 text-right"><?= $countToday["ERROR"] ?? 0; ?></div>
    <div class="col-md-6"><b>CRITICAL</b></div>
    <div class="col-md-6 text-right"><?= $countToday["CRITICAL"] ?? 0; ?></div>
    <div class="col-md-6"><b>SECURITY</b></div>
    <div class="col-md-6 text-right"><?= $countToday["SECURITY"] ?? 0; ?></div>
</div>