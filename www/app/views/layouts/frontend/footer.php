<footer class="footer footer-transparent d-print-none">
    <div class="container-xl">
        <div class="row text-center align-items-center flex-row">
            <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                <ul class="list-inline list-inline-dots mb-0">
                    <li class="list-inline-item">
                        Copyright © <?= date("Y"); ?>
                        <a href="https://digitalfit.tech" class="link-secondary">DigitalFit</a>.
                        All rights reserved.
                    </li>
                    <li class="list-inline-item">
                        <a href="/mentions-legales" class="link-secondary"><?= \NGine\Translate::get("nouveauprojet.footer.terms", "fr"); ?></a>.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>

</div>


<div class="modal modal-blur fade" id="modal-login" tabindex="-1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="form-login">
                <div class="modal-header">
                    <h5 class="modal-title"><?= \NGine\Translate::get("nouveauprojet.login.title"); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label"><?= \NGine\Translate::get("nouveauprojet.login.login"); ?></label>
                        <input type="text" required class="form-control" id="connexion-login" name="login" placeholder="<?= \NGine\Translate::get("nouveauprojet.login.login.placeholder"); ?>">
                    </div>
                    <input type="hidden" name="csrf_token" value="<?= Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label"><?= \NGine\Translate::get("nouveauprojet.login.password"); ?></label>
                        <input type="password" required class="form-control" id="connexion-password" name="password" placeholder="<?= \NGine\Translate::get("nouveauprojet.login.password.placeholder"); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-link link-secondary" data-bs-dismiss="modal">
                        <?= \NGine\Translate::get("nouveauprojet.login.cancel"); ?>
                    </a>
                    <button type="submit" class="btn btn-primary ms-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M12 5l0 14"></path>
                            <path d="M5 12l14 0"></path>
                        </svg>
                        <?= \NGine\Translate::get("nouveauprojet.login.validate"); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tabler Core -->
<script src="/assets/node_modules/@tabler/core/dist/js/tabler.min.js" defer></script>

<?= $this->renderJS(); ?>
<script src="/assets/js/main.js?<?= Config::get("VERSION.ASSETS"); ?>"></script>
<!-- Assign all configuration variables -->
<script>
    <?php Config::addJsConfig('csrfToken', Csrf::token()); ?>
    config = <?= json_encode(Config::getJsConfig()); ?>;
    document.addEventListener("DOMContentLoaded", function () {
        App.init();
    });
</script>
<?php Database::closeConnection(); ?>
</body>
</html>