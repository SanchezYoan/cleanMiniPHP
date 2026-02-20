</main>
<footer class="footer">
    <div class="container">
        <div style="width: 100%;">
            <p class="legal">
                &copy; <?= date('Y'); ?> <a href="https://www.digitalfit.tech" target="_blank">DigitalFit</a>
            </p>
            <p class="menu">
                
                <a href="/contact">Nous contacter</a>
                
                <span class="separator">&nbsp;•&nbsp;</span>
                <a href="/mentions-legales">Mentions légales</a>
                
                <span class="separator">&nbsp;•&nbsp;</span>
                <a href="/cgu">CGU</a>
                
                <span class="separator">&nbsp;•&nbsp;</span>
               
                <a href="javascript:void(0);" aria-label="View cookie settings" data-cc="c-settings" title="Mes préférences de cookies">Préférences 🍪</a>
            </p>
        </div>
    </div>
</footer>
</div>

<script src="/assets/node_modules/jquery/dist/jquery.min.js"></script>
<script src="/assets/node_modules/@popperjs/core/dist/umd/popper.min.js"></script>
<script src="/assets/node_modules/vanilla-cookieconsent/dist/cookieconsent.js"></script>
<script src="/assets/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>

<?= $this->renderJS(); ?>
<script src="/assets/js/main.js?<?= Config::get(".VERSION.ASSETS"); ?>"></script>

<!-- Assign CSRF Token to JS variable -->
<?php Config::addJsConfig('csrfToken', Csrf::token()); ?>
<!-- Assign all configration variables -->
<script>config = <?= json_encode(Config::getJsConfig()); ?>;</script>
<script>$(document).ready(App.init());</script>
</body>
</html>

<?php Database::closeConnection(); ?>