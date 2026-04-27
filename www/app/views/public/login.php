<?php
/**
 * @var $this View
 */
?>
<div class="page page-center flex-grow-1">
    <div class="container container-tight py-4">
        <div class="card card-md">
            <div class="card-body">
                <div class="text-center">
                    <a href="/" class="navbar-brand navbar-brand-autodark">
                        <img src="/assets/img/LifeForge_logo.png" alt="Logo" class="">
                    </a>
                </div>
        
                <form id="login-form" autocomplete="off" novalidate="">
                    <input type="hidden" name="csrf_token" value="<?= Csrf::token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">
                            <?= \NGine\Translate::get("nouveauprojet.login.login"); ?>
                        </label>
                        <input id="username" type="text" class="form-control" autocomplete="off">
                        <div data-lastpass-icon-root=""
                             style="position: relative !important; height: 0px !important; width: 0px !important; float: left !important;"></div>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label">
                            <?= \NGine\Translate::get("nouveauprojet.login.password"); ?>
                            <span class="form-label-description">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#modal-forget-password">
                                    <?= \NGine\Translate::get("nouveauprojet.login.password.forget"); ?>
                                </a>
                            </span>
                        </label>
                        <div class="input-group input-group-flat">
                            <input id="password"
                                   type="password"
                                   class="form-control"
                                   placeholder="<?= \NGine\Translate::get("nouveauprojet.login.password.placeholder"); ?>"
                                   autocomplete="off">
                            <span class="input-group-text">
                                <a href="#"
                                   id="togglePassword"
                                   class="link-secondary"
                                   data-bs-toggle="tooltip"
                                   aria-label="Show password"
                                   data-bs-original-title="Show password">
                                    <svg id="eyeIcon"
                                         xmlns="http://www.w3.org/2000/svg"
                                         class="icon"
                                         width="24"
                                         height="24"
                                         viewBox="0 0 24 24"
                                         stroke-width="2"
                                         stroke="currentColor"
                                         fill="none"
                                         stroke-linecap="round"
                                         stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
                                        <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"></path>
                                    </svg>
                                </a>
                            </span>
                        </div>
                        <small id="firstLoginMessage" class="form-hint d-none">
                            S'il s'agit de la première connexion de cet utilisateur, ce mot de passe sera enregistré pour vos prochaines connexions.
                        </small>
                    </div>
                    
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <?= \NGine\Translate::get("nouveauprojet.menu.login"); ?>
                        </button>
                        <button id="btn-open-create-account" class="btn btn-primary w-100">
                            <?= \NGine\Translate::get("nouveauprojet.menu.signin"); ?>
                        </button>
                    </div>
                </form>
                
                <!-- Bloc 2FA (affiché uniquement si le backend renvoie requires_2fa = true) -->
                <div id="twofa-block" class="mt-3 d-none">
                    <hr>
                    <h3 class="h4 mb-2 text-center">
                        <?= \NGine\Translate::get("nouveauprojet.login.2fa.title") ?: "Vérification en deux étapes"; ?>
                    </h3>
                    <p class="text-muted mb-2 text-center">
                        <?= \NGine\Translate::get("nouveauprojet.login.2fa.help")
                                ?: "Saisissez le code à 6 chiffres de votre application Google Authenticator."; ?>
                    </p>
                    
                    <!-- QR code affiché lors du premier enrôlement 2FA (optionnel) -->
                    <div id="twofa-qrcode" class="mb-3 d-none text-center">
                        <img id="twofa-qrcode-img" src="" alt="QR Code 2FA" style="max-width: 180px;">
                        <p class="small text-muted mt-2">
                            <?= \NGine\Translate::get("nouveauprojet.login.2fa.qrinfo")
                                    ?: "Scannez ce code si c'est votre première activation."; ?>
                        </p>
                    </div>
                    
                    <div class="m-5 text-center">
                        <label class="form-label d-block mb-2">
                            <?= \NGine\Translate::get("nouveauprojet.login.2fa.code") ?: "Code 2FA"; ?>
                        </label>
                        
                        <div class="d-flex justify-content-center gap-2">
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <input
                                        type="text"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        maxlength="1"
                                        class="form-control text-center twofa-digit"
                                        style="max-width: 3rem;"
                                        data-index="<?= $i ?>"
                                >
                            <?php endfor; ?>
                        </div>
                        
                        <!-- champ caché qui contiendra le code complet "123456" -->
                        <input type="hidden" id="twofa-code" name="twofa-code">
                    </div>
                    
                    <div class="d-grid text-center">
                        <button id="twofa-submit" type="button" class="btn btn-primary">
                            <?= \NGine\Translate::get("nouveauprojet.login.2fa.submit") ?: "Valider le code"; ?>
                        </button>
                    </div>
                    
                    <div id="twofa-message" class="alert alert-danger mt-2 d-none" role="alert" style="background-color: color-mix(in srgb,var(--tblr-alert-bg),var(--tblr-bg-surface));">
                        <div class="d-flex">
                            <div class="me-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24"
                                     height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                     fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <circle cx="12" cy="12" r="9"/>
                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                            </div>
                                <div id="twofa-message-text" class="text-secondary"></div>
                        </div>
                        
                    </div>
                </div>
            
            </div>
        </div>
    </div>
</div>

<!-- Modal d'Inscription -->
<div class="modal modal-blur fade" id="modal-create-account" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <form id="createAccountForm">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Créer un compte
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <!-- CSRF -->
                    <!-- <input id="csrf_token"type="hidden" name="csrf_token" value="<?= Csrf::token(); ?>"> -->

                    <!-- Nom d'utilisateur -->
                    <div class="mb-3">
                        <label class="form-label">Nom d'utilisateur</label>
                        <input type="text" class="form-control" id="login" name="login" required>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <!-- Mot de passe -->
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="form-hint">
                            Utilisez au moins 8 caractères.
                        </small>
                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Créer le compte
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-forget-password" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="form-forget-password">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?= \NGine\Translate::get("nouveauprojet.login.modal.title"); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Csrf::token(); ?>">
                    <label class="form-label">
                        <?= \NGine\Translate::get("nouveauprojet.login.modal.label"); ?>
                    </label>
                    <input id="forget-email" type="email" class="form-control" placeholder="Email">
                    <small class="form-hint">
                        <?= \NGine\Translate::get("nouveauprojet.login.modal.tips"); ?>
                    </small>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        <?= \NGine\Translate::get("nouveauprojet.form.close"); ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?= \NGine\Translate::get("nouveauprojet.login.modal.submit"); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
